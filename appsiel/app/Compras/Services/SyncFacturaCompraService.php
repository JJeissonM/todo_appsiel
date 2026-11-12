<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasPivotItemXml;
use App\Compras\Proveedor;
use App\Compras\SyncFacturaCompraLog;
use App\Core\Tercero;
use App\Core\TipoDocApp;
use App\Sistema\TipoTransaccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncFacturaCompraService
{
    // ─────────────────────────────────────────────────────────────
    // MÉTODO PRINCIPAL
    // ─────────────────────────────────────────────────────────────

    /**
     * Procesa el payload completo del BOT.
     * Devuelve un resumen de lo ocurrido con cada factura.
     */
    public function sincronizar(array $payload, int $empresa_id, string $creado_por): array
    {
        $resultado = [
            'procesadas' => 0,
            'exitosas'   => 0,
            'duplicadas' => 0,
            'fallidas'   => 0,
            'detalle'    => [],
        ];

        foreach ($this->facturas_antes_que_notas($payload['data']) as $item_payload) {
            $cufe    = $item_payload['cufe'];
            $invoice = $item_payload['invoice_data']['invoice'];
            $resultado['procesadas']++;

            // ── Idempotencia ──────────────────────────────────────
            if (SyncFacturaCompraLog::ya_procesado($cufe, $empresa_id)) {
                $resultado['duplicadas']++;

                // También se informa el documento que ya existe: sin él, las filas
                // duplicadas se veían en blanco en la columna DOCUMENTO del BOT.
                $encabezado_existente = $this->encabezado_ya_sincronizado($cufe, $empresa_id);

                $resultado['detalle'][] = [
                    'cufe'        => $cufe,
                    'estado'      => 'duplicado',
                    'mensaje'     => 'El documento con este CUFE ya fue sincronizado.',
                    'documento'   => $encabezado_existente ? $this->etiqueta_documento($encabezado_existente) : null,
                    'consecutivo' => $encabezado_existente ? $encabezado_existente->consecutivo : null,
                ];
                continue;
            }

            // ── Procesar dentro de transacción DB ─────────────────
            try {
                $detalle = DB::transaction(function () use (
                    $cufe, $invoice, $empresa_id, $creado_por
                ) {
                    $tipo_documento = $this->tipo_documento($invoice);

                    if ($tipo_documento === 'DebitNote') {
                        throw new \Exception('Las notas débito de compras todavía no se sincronizan desde la DIAN. Regístrela manualmente.');
                    }

                    $proveedor  = $this->resolver_proveedor(
                        $invoice['supplier'],
                        $empresa_id
                    );

                    $factura_relacionada = null;
                    if ($tipo_documento === 'CreditNote') {
                        // Antes toda nota crédito recibida se creaba como factura de compra.
                        $factura_relacionada = $this->buscar_factura_referenciada($invoice, $proveedor, $empresa_id);
                        $encabezado = $this->crear_encabezado_nota_credito(
                            $cufe, $invoice, $proveedor, $factura_relacionada, $empresa_id, $creado_por
                        );
                    } else {
                        $encabezado = $this->crear_encabezado(
                            $cufe, $invoice, $proveedor, $empresa_id, $creado_por
                        );
                    }
                    $this->crear_registros_items(
                        $invoice['items'], $encabezado, $creado_por, $proveedor
                    );
                    $log = $this->registrar_log(
                        $cufe, $empresa_id, $encabezado->id, 'procesado', null, $creado_por
                    );
                    $this->registrar_pivot_xml(
                        $invoice['items'],
                        $proveedor ? $proveedor->id : 0,
                        $log->id
                    );

                    if ($tipo_documento !== 'CreditNote') {
                        // Notas que llegaron antes que la factura que afectan.
                        $this->relacionar_notas_pendientes($encabezado);
                    }

                    $detalle = [
                        'cufe'                 => $cufe,
                        'estado'               => 'procesado',
                        'tipo'                 => $tipo_documento === 'CreditNote' ? 'nota_credito' : 'factura',
                        // El BOT muestra el documento tal como se ve en el ERP
                        // ("FC 67"), no el id de compras_doc_encabezados.
                        'documento'            => $this->etiqueta_documento($encabezado),
                        'consecutivo'          => $encabezado->consecutivo,
                        'proveedor_encontrado' => !is_null($proveedor),
                    ];

                    if ($tipo_documento === 'CreditNote') {
                        $detalle['factura_relacionada'] = $factura_relacionada
                            ? $this->etiqueta_documento($factura_relacionada)
                            : null;
                    }

                    return $detalle;
                });

                $resultado['exitosas']++;
                $resultado['detalle'][] = $detalle;

            } catch (\Exception $e) {
                $resultado['fallidas']++;
                $resultado['detalle'][] = [
                    'cufe'    => $cufe,
                    'estado'  => 'fallido',
                    'mensaje' => $e->getMessage(),
                ];
                $this->registrar_log(
                    $cufe, $empresa_id, null, 'fallido', $e->getMessage(), $creado_por
                );
            }
        }

        return $resultado;
    }

    // ─────────────────────────────────────────────────────────────
    // RESOLVER PROVEEDOR
    // ─────────────────────────────────────────────────────────────

    /**
     * Busca el proveedor a partir del NIT que viene en supplier.identification_number.
     *
     * Flujo según E-R:
     *   core_terceros.numero_identificacion = $nit
     *   → compras_proveedores.core_tercero_id = core_terceros.id
     *
     * Si no se encuentra devuelve null; la factura se guarda con proveedor_id = 0
     * y se indica en la descripción para revisión manual.
     */
    private function resolver_proveedor(array $supplier, int $empresa_id): ?Proveedor
    {
        $nit = $supplier['identification_number'];
        $tercero = Tercero::where('numero_identificacion', $nit)->first();
        if (!$tercero) {
            $tipo = isset($supplier['type']) && strpos(strtolower($supplier['type']), 'natural') !== false ? 'Persona natural' : 'Persona jurídica';
            
            $empresa = \App\Core\Empresa::find($empresa_id);
            $ciudad_default = $empresa ? $empresa->codigo_ciudad : 169; // 169 fallback universal (Bogota en la mayoría de Appsiel)

            $tercero = Tercero::create([
                'core_empresa_id' => $empresa_id,
                'tipo' => $tipo,
                'razon_social' => $supplier['legal_name'] ?? '',
                'descripcion' => $supplier['trade_name'] ?: ($supplier['legal_name'] ?? 'Proveedor Desconocido'),
                'numero_identificacion' => $nit,
                'digito_verificacion' => $supplier['verification_digit'] ?? '',
                'direccion1' => $supplier['address_line'] ?? '',
                'telefono1' => $supplier['phone'] ?? '',
                'email' => $supplier['email'] ?? '',
                'id_tipo_documento_id' => $supplier['identification_type'] ?? 31,
                'codigo_ciudad' => $ciudad_default,
                'estado' => 'Activo',
                'creado_por' => 'BOT_OSEI'
            ]);
        }

        $proveedor = Proveedor::where('core_tercero_id', $tercero->id)->first();
        if (!$proveedor) {
            $proveedor = Proveedor::create([
                'core_tercero_id'    => $tercero->id,
                'clase_proveedor_id' => 1,
                'inv_bodega_id'      => Proveedor::getDefaultInvBodegaId(),
                'condicion_pago_id'  => 1,
                'estado'             => 'Activo'
            ]);
        }

        return $proveedor;
    }

    // ─────────────────────────────────────────────────────────────
    // CREAR ENCABEZADO
    // ─────────────────────────────────────────────────────────────

    /**
     * Inserta en compras_doc_encabezados.
     *
     * Campos clave del diagrama E-R usados:
     *   core_tipo_transaccion_id → sys_tipos_transacciones
     *   core_tipo_doc_app_id     → core_tipos_docs_apps
     *   core_tercero_id          → core_terceros
     *   proveedor_id             → compras_proveedores
     *   entrada_almacen_id       → inv_doc_encabezados (0: sin bodega en esta fase)
     *
     * IMPORTANTE: antes de ejecutar verificar que config/compras.php
     * contenga las claves fc_tipo_transaccion_id y fc_tipo_doc_app_id
     * apuntando a "Factura de compras" (distintas de ea_tipo_transaccion_id).
     */
    private function crear_encabezado(
        string     $cufe,
        array      $invoice,
        ?Proveedor $proveedor,
        int        $empresa_id,
        string     $creado_por
    ): ComprasDocEncabezado {

        $documentConfig = $this->getFacturaCompraDocumentConfig();

        $descripcion = $invoice['anotation'] ?? '';
        if (!$proveedor) {
            $descripcion = '[SIN PROVEEDOR - REVISAR NIT: '
                . $invoice['supplier']['identification_number'] . '] '
                . $descripcion;
        }

        // Consecutivo: último + 1 para este tipo_doc_app
        $ultimo_consecutivo = ComprasDocEncabezado::where(
            'core_tipo_doc_app_id', $documentConfig['core_tipo_doc_app_id']
        )->max('consecutivo') ?? 0;

        return ComprasDocEncabezado::create([
            'core_empresa_id'             => $empresa_id,
            'core_tipo_transaccion_id'    => $documentConfig['core_tipo_transaccion_id'],
            'core_tipo_doc_app_id'        => $documentConfig['core_tipo_doc_app_id'],
            'core_tercero_id'             => $proveedor ? $proveedor->core_tercero_id : 0,
            'proveedor_id'                => $proveedor ? $proveedor->id : 0,
            'entrada_almacen_id'          => 0,
            'compras_doc_relacionado_id'  => 0,
            'consecutivo'                 => $ultimo_consecutivo + 1,
            'fecha'                       => $this->parsear_fecha($invoice['issue_date']),
            'fecha_vencimiento'           => $this->parsear_fecha(
                                                $invoice['payment_date'] ?: $invoice['issue_date']
                                            ),
            'doc_proveedor_prefijo'       => $invoice['resolution']['prefix'] ?? '',
            'doc_proveedor_consecutivo'   => (string) $invoice['number'],
            'cufe'                        => $cufe,
            'forma_pago'                  => $this->parsear_forma_pago($invoice['payment_means_type']),
            'descripcion'                 => $descripcion,
            'estado'                      => 'Activo',
            'sincronizado_bot'            => true,
            'valor_total'                 => $this->calcular_total_factura($invoice['items']),
            'creado_por'                  => $creado_por,
        ]);
    }

    /**
     * Etiqueta del documento como la muestra el ERP: prefijo + consecutivo ("FC 67").
     *
     * Se apoya en get_label_documento() del modelo para no tener dos formas
     * distintas de nombrar el mismo documento. Si faltara el tipo de documento,
     * se devuelve al menos el consecutivo en lugar de romper la sincronización.
     */
    /**
     * Encabezado creado en una sincronización anterior para este CUFE, si existe.
     */
    private function encabezado_ya_sincronizado(string $cufe, int $empresa_id): ?ComprasDocEncabezado
    {
        $log = SyncFacturaCompraLog::where('cufe', $cufe)
            ->where('core_empresa_id', $empresa_id)
            ->where('estado', 'procesado')
            ->whereNotNull('compras_doc_encabezado_id')
            // orderByDesc() no existe en Laravel 5.2.
            ->orderBy('id', 'desc')
            ->first();

        return $log ? $log->encabezado : null;
    }

    private function etiqueta_documento(ComprasDocEncabezado $encabezado): string
    {
        if (!$encabezado->tipo_documento_app) {
            return (string) $encabezado->consecutivo;
        }

        return $encabezado->get_label_documento();
    }

    private function getFacturaCompraDocumentConfig(): array
    {
        $core_tipo_transaccion_id = (int) config('compras.fc_tipo_transaccion_id');
        $core_tipo_doc_app_id = (int) config('compras.fc_tipo_doc_app_id');

        if ($core_tipo_transaccion_id <= 0) {
            $fc_modelo_id = (int) config('compras.fc_modelo_id');

            if ($fc_modelo_id > 0) {
                $core_tipo_transaccion_id = (int) TipoTransaccion::where('core_modelo_id', $fc_modelo_id)
                    ->where('estado', 'Activo')
                    ->value('id');
            }
        }

        if ($core_tipo_transaccion_id <= 0) {
            $core_tipo_transaccion_id = 25;
        }

        if ($core_tipo_doc_app_id <= 0) {
            $core_tipo_doc_app_id = (int) DB::table('core_transaccion_tiene_documento')
                ->where('core_tipo_transaccion_id', $core_tipo_transaccion_id)
                ->orderBy('orden')
                ->value('core_tipo_doc_id');
        }

        if ($core_tipo_doc_app_id <= 0) {
            $core_tipo_doc_app_id = (int) ComprasDocEncabezado::where('core_tipo_transaccion_id', $core_tipo_transaccion_id)
                ->orderBy('id', 'desc')
                ->value('core_tipo_doc_app_id');
        }

        if ($core_tipo_transaccion_id <= 0 || $core_tipo_doc_app_id <= 0) {
            throw new \Exception('No fue posible resolver la configuración de factura de compra. Verifique compras.fc_tipo_transaccion_id y compras.fc_tipo_doc_app_id.');
        }

        return [
            'core_tipo_transaccion_id' => $core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $core_tipo_doc_app_id,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // NOTAS CRÉDITO
    // ─────────────────────────────────────────────────────────────

    /**
     * Tipo de documento según OSEI: nombre del nodo raíz del XML (Invoice,
     * CreditNote, DebitNote). Si no viene, se asume factura como antes.
     */
    private function tipo_documento(array $invoice): string
    {
        $tipo = $invoice['document_type'] ?? '';

        return in_array($tipo, ['CreditNote', 'DebitNote'], true) ? $tipo : 'Invoice';
    }

    /**
     * Las facturas se procesan primero: así una nota que llega en el mismo lote
     * que su factura queda relacionada con ella.
     */
    private function facturas_antes_que_notas(array $items): array
    {
        $facturas = [];
        $notas = [];

        foreach ($items as $item) {
            $invoice = $item['invoice_data']['invoice'] ?? [];
            if ($this->tipo_documento($invoice) === 'Invoice') {
                $facturas[] = $item;
            } else {
                $notas[] = $item;
            }
        }

        return array_merge($facturas, $notas);
    }

    /**
     * Factura de compra que afecta la nota (cac:BillingReference del XML).
     * Primero por CUFE, que es inequívoco; si no, por proveedor + prefijo + número.
     */
    private function buscar_factura_referenciada(array $invoice, ?Proveedor $proveedor, int $empresa_id): ?ComprasDocEncabezado
    {
        $referencia = $invoice['billing_reference'] ?? [];
        $tipo_transaccion_fc = $this->getFacturaCompraDocumentConfig()['core_tipo_transaccion_id'];

        $consulta = function () use ($empresa_id, $tipo_transaccion_fc) {
            return ComprasDocEncabezado::where('core_empresa_id', $empresa_id)
                ->where('core_tipo_transaccion_id', $tipo_transaccion_fc)
                ->where('estado', '<>', 'Anulado');
        };

        $cufe_factura = trim((string) ($referencia['uuid'] ?? ''));
        if ($cufe_factura !== '') {
            $factura = $consulta()->where('cufe', $cufe_factura)->orderBy('id', 'desc')->first();
            if ($factura) {
                return $factura;
            }
        }

        $numero_factura = trim((string) ($referencia['id'] ?? ''));
        if ($numero_factura === '' || !$proveedor) {
            return null;
        }

        list($prefijo, $consecutivo) = $this->separar_prefijo_numero($numero_factura);

        return $consulta()
            ->where('proveedor_id', $proveedor->id)
            ->where('doc_proveedor_prefijo', $prefijo)
            ->where('doc_proveedor_consecutivo', $consecutivo)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Crea la nota crédito de compras (transacción 36) pendiente, igual que las
     * facturas del BOT: sin devolución de inventario, sin CxP y sin contabilidad.
     *
     * Como en las notas hechas a mano, doc_proveedor_* guarda la factura del
     * proveedor que se afecta. El número de la propia nota queda en la descripción
     * y su CUFE en el campo cufe.
     */
    private function crear_encabezado_nota_credito(
        string                $cufe,
        array                 $invoice,
        ?Proveedor            $proveedor,
        ?ComprasDocEncabezado $factura,
        int                   $empresa_id,
        string                $creado_por
    ): ComprasDocEncabezado {

        $documentConfig = $this->getNotaCreditoDocumentConfig();

        $numero_nota = $this->numero_documento_proveedor($invoice);
        $numero_factura = trim((string) ($invoice['billing_reference']['id'] ?? ''));

        if ($factura) {
            $prefijo_factura = $factura->doc_proveedor_prefijo;
            $consecutivo_factura = $factura->doc_proveedor_consecutivo;
        } else {
            list($prefijo_factura, $consecutivo_factura) = $this->separar_prefijo_numero($numero_factura);
        }

        $descripcion = trim('Nota crédito del proveedor ' . $numero_nota . '. ' . ($invoice['anotation'] ?? ''));
        if (!$proveedor) {
            $descripcion = '[SIN PROVEEDOR - REVISAR NIT: '
                . $invoice['supplier']['identification_number'] . '] '
                . $descripcion;
        }

        

        return ComprasDocEncabezado::create([
            'core_empresa_id'             => $empresa_id,
            'core_tipo_transaccion_id'    => $documentConfig['core_tipo_transaccion_id'],
            'core_tipo_doc_app_id'        => $documentConfig['core_tipo_doc_app_id'],
            'core_tercero_id'             => $proveedor ? $proveedor->core_tercero_id : 0,
            'proveedor_id'                => $proveedor ? $proveedor->id : 0,
            'entrada_almacen_id'          => 0,
            'compras_doc_relacionado_id'  => $factura ? $factura->id : 0,
            'consecutivo'                 => TipoDocApp::reservar_consecutivo($empresa_id, $documentConfig['core_tipo_doc_app_id']),
            'fecha'                       => $this->parsear_fecha($invoice['issue_date']),
            'fecha_vencimiento'           => $this->parsear_fecha($invoice['issue_date']),
            'doc_proveedor_prefijo'       => $prefijo_factura,
            'doc_proveedor_consecutivo'   => $consecutivo_factura,
            'cufe'                        => $cufe,
            'forma_pago'                  => $factura
                                                ? $factura->forma_pago
                                                : $this->parsear_forma_pago($invoice['payment_means_type'] ?? ''),
            'descripcion'                 => $descripcion,
            'estado'                      => 'Activo',
            'sincronizado_bot'            => true,
            'valor_total'                 => $this->calcular_total_factura($invoice['items']),
            'creado_por'                  => $creado_por,
        ]);
    }

    /**
     * Relaciona con la factura recién sincronizada las notas del BOT que llegaron
     * antes que ella (quedaron con compras_doc_relacionado_id = 0).
     */
    private function relacionar_notas_pendientes(ComprasDocEncabezado $factura): void
    {
        if ((string) $factura->doc_proveedor_consecutivo === '' || !$factura->proveedor_id) {
            return;
        }

        ComprasDocEncabezado::where('core_empresa_id', $factura->core_empresa_id)
            ->where('core_tipo_transaccion_id', $this->getNotaCreditoDocumentConfig()['core_tipo_transaccion_id'])
            ->where('sincronizado_bot', true)
            ->where('compras_doc_relacionado_id', 0)
            ->where('estado', '<>', 'Anulado')
            ->where('proveedor_id', $factura->proveedor_id)
            ->where('doc_proveedor_prefijo', $factura->doc_proveedor_prefijo)
            ->where('doc_proveedor_consecutivo', $factura->doc_proveedor_consecutivo)
            ->update([
                'compras_doc_relacionado_id' => $factura->id,
                'forma_pago'                 => $factura->forma_pago,
            ]);
    }

    private function getNotaCreditoDocumentConfig(): array
    {
        $core_tipo_transaccion_id = (int) config('compras.nc_tipo_transaccion_id') ?: 36;
        $core_tipo_doc_app_id = (int) config('compras.nc_tipo_doc_app_id');

        if ($core_tipo_doc_app_id <= 0) {
            $core_tipo_doc_app_id = (int) DB::table('core_transaccion_tiene_documento')
                ->where('core_tipo_transaccion_id', $core_tipo_transaccion_id)
                ->orderBy('orden')
                ->value('core_tipo_doc_id');
        }

        if ($core_tipo_doc_app_id <= 0) {
            throw new \Exception('No fue posible resolver el tipo de documento de la nota crédito de compras (transacción ' . $core_tipo_transaccion_id . ').');
        }

        return [
            'core_tipo_transaccion_id' => $core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $core_tipo_doc_app_id,
        ];
    }

    /**
     * Número del documento tal como lo emitió el proveedor ("ENC19137").
     * OSEI lo envía en document_id; las versiones anteriores solo enviaban el
     * número sin letras.
     */
    private function numero_documento_proveedor(array $invoice): string
    {
        if (!empty($invoice['document_id'])) {
            return (string) $invoice['document_id'];
        }

        return ($invoice['resolution']['prefix'] ?? '') . $invoice['number'];
    }

    /**
     * "A3309" → ['A', '3309']; "SETP-0012" → ['SETP', '12']. El número se guarda
     * sin ceros a la izquierda, igual que el de las facturas que llegan de OSEI.
     */
    private function separar_prefijo_numero(string $numero): array
    {
        $numero = trim($numero);

        if (preg_match('/^(.*?)(\d+)$/', $numero, $partes)) {
            return [trim($partes[1], " -_"), (string) (int) $partes[2]];
        }

        return [$numero, ''];
    }

    // ─────────────────────────────────────────────────────────────
    // CREAR REGISTROS DE ÍTEMS
    // ─────────────────────────────────────────────────────────────

    /**
     * Inserta una fila en compras_doc_registros por cada ítem del XML.
     *
     * inv_producto_id = 0 hasta que el usuario realice el mapeo en la vista show.
     * inv_motivo_id   = 0 (sin bodega; se asignará con el mapeo si se desea).
     * inv_bodega_id   = 0 (misma razón).
     */
    private function crear_registros_items(
        array                $items,
        ComprasDocEncabezado $encabezado,
        string               $creado_por,
        ?Proveedor           $proveedor
    ): void {
        foreach ($items as $item) {
            $tax         = $item['taxes'][0] ?? [];
            $tax_rate    = (float) ($tax['tax_rate']       ?? 0);
            $tax_amount  = (float) ($tax['tax_amount']     ?? 0);
            $taxable_amt = (float) ($tax['taxable_amount'] ?? $item['line_extension_amount']);
            $discount    = (float) ($tax['total_discount'] ?? 0);
            $precio_total = round((float) $item['line_extension_amount'] + $tax_amount, 2);

            // Auto-Mapeo y aplicación del factor de conversión de U.M.:
            $inv_producto_id      = 0;
            $factor_conversion    = 1.0;
            $tipo_factor          = 'division';
            $cantidad_xml         = (float) $item['quantity'];
            $cantidad_local       = $cantidad_xml;

            if ($proveedor) {
                $codigo = (isset($item['sku']) && $item['sku'] !== '' && $item['sku'] !== null)
                    ? $item['sku']
                    : $item['description'];

                $pivot = ComprasPivotItemXml::where('proveedor_id', $proveedor->id)
                    ->where('codigo_item_xml', $codigo)
                    ->first();

                if ($pivot && $pivot->inv_producto_id) {
                    $inv_producto_id = $pivot->inv_producto_id;
                }

                if ($pivot) {
                    if ((float)$pivot->factor_conversion > 0) {
                        $factor_conversion = (float) $pivot->factor_conversion;
                    }
                    if (!empty($pivot->tipo_factor) && in_array($pivot->tipo_factor, ['multiplicacion', 'division'])) {
                        $tipo_factor = $pivot->tipo_factor;
                    }
                }

                if ($factor_conversion <= 0) {
                    $factor_conversion = 1.0;
                }

                // Regla nueva:
                // - el total XML se respeta
                // - la cantidad se convierte según operación + factor
                // - el precio unitario se deriva como total_xml / cantidad_convertida
                if ($tipo_factor === 'multiplicacion') {
                    $cantidad_local = $cantidad_xml * $factor_conversion;
                } else {
                    $cantidad_local = $cantidad_xml / $factor_conversion;
                }
            }

            if ($cantidad_local <= 0) {
                $cantidad_local = $cantidad_xml;
            }

            $precio_unitario = $cantidad_local > 0
                ? round($precio_total / $cantidad_local, 6)
                : 0;

            $data_registro = [
                'compras_doc_encabezado_id' => $encabezado->id,
                'inv_producto_id'           => $inv_producto_id,
                'inv_motivo_id'             => 0,
                'contab_retencion_id'       => 0,
                'precio_unitario'           => $precio_unitario,
                'cantidad'                  => $cantidad_local,
                'precio_total'              => $precio_total,
                'base_impuesto'             => round($taxable_amt, 2),
                'tasa_impuesto'             => $tax_rate,
                'valor_impuesto'            => round($tax_amount, 2),
                'tasa_descuento'            => 0,
                'valor_total_descuento'     => $discount,
                'tasa_retencion'            => 0,
                'valor_retencion'           => 0,
                'estado'                    => 'Activo',
                'creado_por'                => $creado_por,
                'xml_producto'              => $item['description'] ?? '',
                'xml_codigo'                => $codigo ?? '',
            ];

            // Guardar valores fieles al XML si la tabla los tiene
            if (Schema::hasColumn('compras_doc_registros', 'xml_cantidad')) {
                $data_registro['xml_cantidad'] = $cantidad_xml;
            }
            if (Schema::hasColumn('compras_doc_registros', 'xml_precio_unitario')) {
                $data_registro['xml_precio_unitario'] = round((float) $item['price'], 6);
            }

            ComprasDocRegistro::create($data_registro);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // REGISTRAR PIVOT XML
    // ─────────────────────────────────────────────────────────────

    /**
     * Inserta o actualiza en compras_pivot_items_xml.
     *
     * Clave de búsqueda: proveedor_id + codigo_item_xml.
     * Si sku está vacío se usa description como codigo_item_xml.
     *
     * inv_producto_id NO se toca aquí; solo se escribe cuando
     * el usuario guarda el mapeo desde la vista show.
     */
    private function registrar_pivot_xml(
        array $items,
        int   $proveedor_id,
        int   $sync_log_id
    ): void {
        foreach ($items as $item) {
            $codigo = (isset($item['sku']) && $item['sku'] !== '' && $item['sku'] !== null)
                ? $item['sku']
                : $item['description'];

            $pivot = ComprasPivotItemXml::where('proveedor_id', $proveedor_id)
                ->where('codigo_item_xml', $codigo)
                ->first();

            if ($pivot) {
                $pivot->update([
                    'nombre_item_xml'     => $item['description'],
                    'unidad_medida_xml'   => $item['u.m'] ?? '',
                    'compras_sync_log_id' => $sync_log_id,
                ]);
            } else {
                ComprasPivotItemXml::create([
                    'proveedor_id'        => $proveedor_id,
                    'codigo_item_xml'     => $codigo,
                    'nombre_item_xml'     => $item['description'],
                    'unidad_medida_xml'   => $item['u.m'] ?? '',
                    'compras_sync_log_id' => $sync_log_id,
                    'inv_producto_id'     => null,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    private function registrar_log(
        string  $cufe,
        int     $empresa_id,
        ?int    $encabezado_id,
        string  $estado,
        ?string $mensaje_error,
        string  $creado_por
    ): SyncFacturaCompraLog {
        return SyncFacturaCompraLog::updateOrCreate(
            [
                'cufe'            => $cufe,
                'core_empresa_id' => $empresa_id,
            ],
            [
                'compras_doc_encabezado_id' => $encabezado_id,
                'estado'                    => $estado,
                'mensaje_error'             => $mensaje_error,
                'creado_por'                => $creado_por,
            ]
        );
    }

    /** Convierte "15/01/2026" → "2026-01-15". Si ya viene en formato MySQL lo devuelve tal cual. */
    private function parsear_fecha(string $fecha): string
    {
        if (empty($fecha)) return date('Y-m-d');
        $partes = explode('/', $fecha);
        if (count($partes) === 3) {
            return "{$partes[2]}-{$partes[1]}-{$partes[0]}";
        }
        return $fecha;
    }

    /** "CONTADO" → "contado" | cualquier otro valor → "credito" */
    private function parsear_forma_pago(string $tipo): string
    {
        return strtolower($tipo) === 'contado' ? 'contado' : 'credito';
    }

    /** Suma line_extension_amount + tax_amount de todos los ítems */
    private function calcular_total_factura(array $items): float
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) ($item['line_extension_amount'] ?? 0);
            $total += (float) ($item['taxes'][0]['tax_amount'] ?? 0);
        }
        return round($total, 2);
    }

}
