<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasPivotItemXml;
use App\Compras\Proveedor;
use App\Compras\SyncFacturaCompraLog;
use App\Core\Tercero;
use Illuminate\Support\Facades\DB;

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

        foreach ($payload['data'] as $item_payload) {
            $cufe    = $item_payload['cufe'];
            $invoice = $item_payload['invoice_data']['invoice'];
            $resultado['procesadas']++;

            // ── Idempotencia ──────────────────────────────────────
            if (SyncFacturaCompraLog::ya_procesado($cufe, $empresa_id)) {
                $resultado['duplicadas']++;
                $resultado['detalle'][] = [
                    'cufe'    => $cufe,
                    'estado'  => 'duplicado',
                    'mensaje' => 'La factura con este CUFE ya fue sincronizada.',
                ];
                continue;
            }

            // ── Procesar dentro de transacción DB ─────────────────
            try {
                $detalle = DB::transaction(function () use (
                    $cufe, $invoice, $empresa_id, $creado_por
                ) {
                    $proveedor  = $this->resolver_proveedor(
                        $invoice['supplier'],
                        $empresa_id
                    );
                    $encabezado = $this->crear_encabezado(
                        $cufe, $invoice, $proveedor, $empresa_id, $creado_por
                    );
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

                    return [
                        'cufe'                      => $cufe,
                        'estado'                    => 'procesado',
                        'compras_doc_encabezado_id' => $encabezado->id,
                        'proveedor_encontrado'      => !is_null($proveedor),
                    ];
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
                'inv_bodega_id'      => $this->getBodegaDefaultId(),
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

        $parametros = config('compras');

        $descripcion = $invoice['anotation'] ?? '';
        if (!$proveedor) {
            $descripcion = '[SIN PROVEEDOR - REVISAR NIT: '
                . $invoice['supplier']['identification_number'] . '] '
                . $descripcion;
        }

        // Consecutivo: último + 1 para este tipo_doc_app
        $ultimo_consecutivo = ComprasDocEncabezado::where(
            'core_tipo_doc_app_id', $parametros['fc_tipo_doc_app_id']
        )->max('consecutivo') ?? 0;

        return ComprasDocEncabezado::create([
            'core_empresa_id'             => $empresa_id,
            'core_tipo_transaccion_id'    => $parametros['fc_tipo_transaccion_id'],
            'core_tipo_doc_app_id'        => $parametros['fc_tipo_doc_app_id'],
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

            // Auto-Mapeo:
            $inv_producto_id = 0;
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
            }

            ComprasDocRegistro::create([
                'compras_doc_encabezado_id' => $encabezado->id,
                'inv_producto_id'           => $inv_producto_id,
                'inv_motivo_id'             => 0,
                'contab_retencion_id'       => 0,
                'precio_unitario'           => round((float) $item['price'], 4),
                'cantidad'                  => (float) $item['quantity'],
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
            ]);
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

    private function getBodegaDefaultId(): int
    {
        $inv_bodega_id = (int) config('inventarios.item_bodega_principal_id');

        return $inv_bodega_id > 0 ? $inv_bodega_id : 1;
    }
}
