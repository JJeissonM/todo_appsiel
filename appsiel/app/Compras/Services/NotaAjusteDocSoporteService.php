<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\RegistroRetencion;
use App\Contabilidad\Retencion;
use App\Core\EncabezadoDocumentoTransaccion;
use App\CxP\CxpAbono;
use App\CxP\CxpMovimiento;
use App\FacturacionElectronica\NotaAjusteDocSoporte;
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvProducto;
use App\Inventarios\Services\InvDocumentsLinesService;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Crea y anula la nota de ajuste al documento soporte (transacción 62) con todos sus efectos:
 * inventario, compras, contabilidad, retenciones (reverso proporcional) y CxP o tesorería.
 * Los valores salen de NotaAjusteDocSoporteCalculo. Quien llama abre la transacción de BD.
 */
class NotaAjusteDocSoporteService
{
    // Motivo de inventarios "Devolución por compras" (el mismo de la nota crédito de compras, transacción 36).
    const MOTIVO_DEVOLUCION_ID = 18;

    /**
     * @param array $encabezado core_tipo_doc_app_id, fecha, descripcion, url_id_modelo (modelo de la nota)
     * @param array $solicitud  [id línea DS => cantidad (concepto 1) o base sin IVA (3-5)]
     */
    public function crear(ComprasDocEncabezado $ds, $concepto, array $encabezado, array $solicitud)
    {
        $ds = ComprasDocEncabezado::where('id', $ds->id)->lockForUpdate()->firstOrFail();
        $this->validar_doc_soporte($ds, $encabezado['fecha']);

        $lineasDs = $ds->lineas_registros()->where('estado', 'Activo')->get()->keyBy('id');
        $lineasNota = NotaAjusteDocSoporteCalculo::calcular($concepto, $this->lineas_para_calculo($lineasDs), $solicitud);
        $totales = NotaAjusteDocSoporteCalculo::totales(
            $lineasNota,
            (float)$ds->reteica_valor,
            $lineasDs->sum(function ($l) { return abs((float)$l->base_impuesto); })
        );

        $cxpProveedor = $this->cxp_proveedor($ds, true);
        NotaAjusteDocSoporteCalculo::validar_saldo($ds->forma_pago, $totales['neto'], $cxpProveedor ? $cxpProveedor->saldo_pendiente : 0);

        $nota = $this->crear_encabezado($ds, $concepto, $encabezado);
        $datos = $this->datos_base($nota);
        $detalle = $nota->descripcion != '' ? $nota->descripcion : NotaAjusteDocSoporte::CONCEPTOS[(int)$concepto];

        $devolucion = in_array((int)$concepto, [NotaAjusteDocSoporteCalculo::DEVOLUCION, NotaAjusteDocSoporteCalculo::ANULACION]);
        $docInventarioId = $devolucion ? $this->crear_devolucion_inventario($ds, $nota, $lineasNota, $lineasDs) : 0;

        foreach ($lineasNota as $valores) {
            $lineaDs = $lineasDs[$valores['origen_id']];
            $producto = InvProducto::find($lineaDs->inv_producto_id);
            $esProducto = $producto && $producto->tipo == 'producto';
            $motivoId = ($devolucion && $esProducto) ? self::MOTIVO_DEVOLUCION_ID : (int)$lineaDs->inv_motivo_id;

            $lineaDatos = [
                'inv_bodega_id' => $this->bodega($ds, $lineaDs),
                'inv_motivo_id' => $motivoId,
                'inv_producto_id' => $lineaDs->inv_producto_id,
                'precio_unitario' => $valores['cantidad'] > 0 ? round($valores['total'] / $valores['cantidad'], 2) : $valores['total'],
                // La nota resta: cantidades y valores en negativo.
                'cantidad' => -$valores['cantidad'],
                'precio_total' => -$valores['total'],
                'base_impuesto' => -$valores['base'],
                'tasa_impuesto' => $valores['tasa_impuesto'],
                'valor_impuesto' => -$valores['iva'],
                'compras_doc_registro_origen_id' => $lineaDs->id,
                'creado_por' => $nota->creado_por,
                'estado' => 'Activo',
            ];

            $registro = ComprasDocRegistro::create(['compras_doc_encabezado_id' => $nota->id] + $lineaDatos);
            ComprasMovimiento::create($lineaDatos + $datos);

            // Contabilidad: reverso del IVA descontable y del costo/inventario de la línea (CR).
            $datosLinea = $lineaDatos + $datos;
            if ($valores['iva'] > 0) {
                ContabilidadController::contabilizar_registro2($datosLinea, InvProducto::get_cuenta_impuesto_devolucion_compras($lineaDs->inv_producto_id), $detalle, 0, $valores['iva']);
            }
            ContabilidadController::contabilizar_registro2($datosLinea, $this->cuenta_base($esProducto, $motivoId, $lineaDs->inv_producto_id), $detalle, 0, $valores['base']);

            if (!$devolucion && $esProducto) {
                $this->recostear_entrada($ds, $lineaDs, $valores['base'], 'crear');
            }
            if ($devolucion) {
                $lineaDs->update(['cantidad_devuelta' => abs((float)$lineaDs->cantidad_devuelta) + $valores['cantidad']]);
            }

            $this->reversar_retefuente($ds, $nota, $registro, $lineaDs, $valores, $datos);
        }

        $this->reversar_reteica($ds, $nota, $totales, $datos);
        $this->registrar_pago_proveedor($ds, $nota, $totales['neto'], $datos, $detalle, $cxpProveedor);

        $nota->update(['valor_total' => -$totales['total'], 'entrada_almacen_id' => $docInventarioId ?: '']);

        return $nota;
    }

    /**
     * Revierte todo lo que hizo crear(). Solo para notas que la DIAN no ha aceptado.
     */
    public function anular(NotaAjusteDocSoporte $nota)
    {
        $nota = NotaAjusteDocSoporte::where('id', $nota->id)->lockForUpdate()->firstOrFail();
        if ($nota->estado == 'Anulado') {
            return $nota;
        }
        if ($nota->enviado_electronicamente()) {
            throw new \InvalidArgumentException('La nota ya fue aceptada por la DIAN y no se puede anular.');
        }

        $ds = ComprasDocEncabezado::where('id', $nota->compras_doc_relacionado_id)->lockForUpdate()->firstOrFail();
        $identidad = $this->identidad($nota);
        $devolucion = in_array((int)$nota->nota_ajuste_concepto, [NotaAjusteDocSoporteCalculo::DEVOLUCION, NotaAjusteDocSoporteCalculo::ANULACION]);

        if ((int)$nota->entrada_almacen_id > 0) {
            InventarioController::anular_documento_inventarios((int)$nota->entrada_almacen_id);
        }

        foreach ($nota->lineas_registros()->where('estado', 'Activo')->get() as $registro) {
            $lineaDs = ComprasDocRegistro::find($registro->compras_doc_registro_origen_id);
            if ($lineaDs === null) {
                continue;
            }
            if ($devolucion) {
                $lineaDs->update(['cantidad_devuelta' => max(0, abs((float)$lineaDs->cantidad_devuelta) - abs((float)$registro->cantidad))]);
            } else {
                $producto = InvProducto::find($lineaDs->inv_producto_id);
                if ($producto && $producto->tipo == 'producto') {
                    $this->recostear_entrada($ds, $lineaDs, abs((float)$registro->base_impuesto), 'anular');
                }
            }
        }

        // Pagos: abonos de la nota a la CxP del proveedor y a las de retenciones, y tesorería.
        foreach (CxpAbono::where($identidad)->get() as $abono) {
            $cxp = CxpMovimiento::where('core_empresa_id', $nota->core_empresa_id)
                ->where('core_tipo_transaccion_id', $abono->doc_cxp_transacc_id)
                ->where('core_tipo_doc_app_id', $abono->doc_cxp_tipo_doc_id)
                ->where('consecutivo', $abono->doc_cxp_consecutivo)
                ->where('core_tercero_id', $abono->core_tercero_id)
                ->lockForUpdate()->first();
            if ($cxp) {
                $this->mover_saldo($cxp, -$abono->abono);
            }
            $abono->delete();
        }
        CxpMovimiento::where($identidad)->delete();
        TesoMovimiento::where($identidad)->delete();

        RegistroRetencion::where($identidad)->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
        ContabMovimiento::where($identidad)->delete();
        ComprasMovimiento::where($identidad)->delete();

        $modificadoPor = Auth::user()->email;
        ComprasDocRegistro::where('compras_doc_encabezado_id', $nota->id)->update(['estado' => 'Anulado', 'modificado_por' => $modificadoPor]);
        $nota->update(['estado' => 'Anulado', 'modificado_por' => $modificadoPor]);

        return $nota;
    }

    protected function validar_doc_soporte(ComprasDocEncabezado $ds, $fechaNota)
    {
        if ((int)$ds->core_tipo_transaccion_id != NotaAjusteDocSoporte::TRANSACCION_DOC_SOPORTE_ID) {
            throw new \InvalidArgumentException('La nota de ajuste solo aplica a documentos soporte.');
        }
        if ($ds->estado == 'Anulado') {
            throw new \InvalidArgumentException('El documento soporte está anulado.');
        }
        if (!$ds->enviado_electronicamente()) {
            throw new \InvalidArgumentException('El documento soporte no ha sido aceptado por la DIAN: anúlelo en lugar de hacer una nota de ajuste.');
        }
        // NSBG06: la nota no puede ser anterior al DS.
        if (strtotime($fechaNota) < strtotime($ds->fecha)) {
            throw new \InvalidArgumentException('La fecha de la nota no puede ser anterior a la del documento soporte (' . $ds->fecha . ').');
        }
    }

    /**
     * Líneas del DS con lo ya ajustado por notas activas (cantidad y base).
     */
    protected function lineas_para_calculo($lineasDs)
    {
        $ajustes = ComprasDocRegistro::whereIn('compras_doc_registro_origen_id', $lineasDs->keys()->all())
            ->where('estado', 'Activo')
            ->get()
            ->groupBy('compras_doc_registro_origen_id');

        $lineas = [];
        foreach ($lineasDs as $linea) {
            $previos = $ajustes->get($linea->id, collect());
            $lineas[] = [
                'id' => $linea->id,
                'cantidad' => $linea->cantidad,
                'base' => $linea->base_impuesto,
                'iva' => $linea->valor_impuesto,
                'tasa_impuesto' => $linea->tasa_impuesto,
                'valor_retencion' => (int)$linea->contab_retencion_id > 0 ? $linea->valor_retencion : 0,
                'cantidad_ajustada' => $previos->sum(function ($r) { return abs((float)$r->cantidad); }),
                'base_ajustada' => $previos->sum(function ($r) { return abs((float)$r->base_impuesto); }),
            ];
        }

        return $lineas;
    }

    protected function crear_encabezado(ComprasDocEncabezado $ds, $concepto, array $encabezado)
    {
        $datos = [
            'core_tipo_transaccion_id' => NotaAjusteDocSoporte::TRANSACCION_ID,
            'core_tipo_doc_app_id' => $encabezado['core_tipo_doc_app_id'],
            'fecha' => $encabezado['fecha'],
            'core_empresa_id' => $ds->core_empresa_id,
            'core_tercero_id' => $ds->core_tercero_id,
            'proveedor_id' => $ds->proveedor_id,
            'comprador_id' => $ds->comprador_id,
            'forma_pago' => $ds->forma_pago,
            'fecha_vencimiento' => $encabezado['fecha'],
            'compras_doc_relacionado_id' => $ds->id,
            'nota_ajuste_concepto' => (int)$concepto,
            'descripcion' => isset($encabezado['descripcion']) ? trim($encabezado['descripcion']) : '',
            'estado' => 'Activo',
            'valor_total' => 0,
            'creado_por' => Auth::user()->email,
            'modificado_por' => '',
        ];

        return (new EncabezadoDocumentoTransaccion($encabezado['url_id_modelo']))->crear_nuevo($datos);
    }

    protected function datos_base($nota)
    {
        return [
            'core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id,
            'consecutivo' => $nota->consecutivo,
            'fecha' => $nota->fecha,
            'core_empresa_id' => $nota->core_empresa_id,
            'core_tercero_id' => $nota->core_tercero_id,
            'proveedor_id' => $nota->proveedor_id,
            'forma_pago' => $nota->forma_pago,
            'descripcion' => $nota->descripcion,
            'creado_por' => $nota->creado_por,
            'estado' => 'Activo',
        ];
    }

    protected function identidad($nota)
    {
        return [
            'core_empresa_id' => $nota->core_empresa_id,
            'core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id,
            'consecutivo' => $nota->consecutivo,
        ];
    }

    /**
     * Devolución en compras (transacción 37) solo por los productos inventariables; los servicios no mueven inventario.
     */
    protected function crear_devolucion_inventario(ComprasDocEncabezado $ds, $nota, array $lineasNota, $lineasDs)
    {
        $lineas = [];
        $bodegaId = 0;
        foreach ($lineasNota as $valores) {
            $lineaDs = $lineasDs[$valores['origen_id']];
            $producto = InvProducto::find($lineaDs->inv_producto_id);
            $entrada = $this->linea_entrada($ds, $lineaDs);
            if (!$producto || $producto->tipo != 'producto' || $entrada === null || $valores['cantidad'] <= 0) {
                continue;
            }
            $linea = $entrada->toArray();
            $linea['cantidad'] = $valores['cantidad'];
            $linea['inv_motivo_id'] = self::MOTIVO_DEVOLUCION_ID;
            $linea['costo_total'] = $valores['cantidad'] * $entrada->costo_unitario;
            $lineas[] = (object)$linea;
            $bodegaId = $entrada->inv_bodega_id;
        }

        if (empty($lineas)) {
            return 0;
        }

        $parametros = config('compras');
        $request = new Request($this->datos_base($nota) + [
            'core_tipo_transaccion_id' => $parametros['dvc_tipo_transaccion_id'],
            'core_tipo_doc_app_id' => $parametros['dvc_tipo_doc_app_id'],
            'inv_bodega_id' => $bodegaId,
            'estado' => 'Facturada',
            'descripcion' => 'Nota de ajuste DS ' . $nota->consecutivo,
        ]);

        return InventarioController::crear_documento($request, $lineas, $parametros['dvc_modelo_id']);
    }

    protected function linea_entrada(ComprasDocEncabezado $ds, $lineaDs)
    {
        if ((int)$lineaDs->inv_doc_registro_id > 0) {
            $registro = InvDocRegistro::find($lineaDs->inv_doc_registro_id);
            if ($registro) {
                return $registro;
            }
        }

        $entradas = array_filter(array_map('intval', explode(',', (string)$ds->entrada_almacen_id)));
        $candidatas = InvDocRegistro::whereIn('inv_doc_encabezado_id', $entradas)
            ->where('inv_producto_id', $lineaDs->inv_producto_id)
            ->get();

        $exacta = $candidatas->first(function ($key, $r) use ($lineaDs) {
            return abs(abs((float)$r->cantidad) - abs((float)$lineaDs->cantidad)) < 0.0001;
        });

        return $exacta ?: $candidatas->first();
    }

    protected function bodega(ComprasDocEncabezado $ds, $lineaDs)
    {
        $entrada = $this->linea_entrada($ds, $lineaDs);

        return $entrada ? $entrada->inv_bodega_id : 0;
    }

    /**
     * Misma cuenta que debitó el DS: motivo de inventarios para productos, grupo de inventarios para servicios.
     */
    protected function cuenta_base($esProducto, $motivoId, $productoId)
    {
        if ($esProducto) {
            return InvMotivo::find($motivoId)->cta_contrapartida_id;
        }

        return InvProducto::get_cuenta_inventarios($productoId);
    }

    /**
     * Rebaja por valor de un producto: baja el costo unitario de su línea de entrada (lo sube al anular).
     */
    protected function recostear_entrada(ComprasDocEncabezado $ds, $lineaDs, $base, $accion)
    {
        $entrada = $this->linea_entrada($ds, $lineaDs);
        if ($entrada === null || abs((float)$entrada->cantidad) == 0) {
            return;
        }
        $ajusteUnitario = $base / abs((float)$entrada->cantidad);
        $costo = $accion == 'crear' ? $entrada->costo_unitario - $ajusteUnitario : $entrada->costo_unitario + $ajusteUnitario;

        (new InvDocumentsLinesService())->update_document_line($entrada, max(0, $costo), $entrada->cantidad);
    }

    /**
     * Reverso proporcional de la retefuente de la línea: DB al pasivo de la retención, registro negativo
     * en el reporte de retenciones y abono (o saldo a favor) en la CxP de la DIAN.
     */
    protected function reversar_retefuente(ComprasDocEncabezado $ds, $nota, $registro, $lineaDs, array $valores, array $datos)
    {
        if ($valores['retencion'] <= 0) {
            return;
        }
        $retencion = Retencion::find((int)$lineaDs->contab_retencion_id);
        if ($retencion === null) {
            return;
        }

        $baseRetencion = $lineaDs->base_retencion !== null ? abs((float)$lineaDs->base_retencion) : abs((float)$lineaDs->base_impuesto);
        $baseNota = NotaAjusteDocSoporteCalculo::proporcional($baseRetencion, $valores['base'], abs((float)$lineaDs->base_impuesto));

        $this->reversar_retencion($ds, $nota, $retencion, $valores['retencion'], $baseNota, (float)$lineaDs->tasa_retencion,
            $registro->id, (int)config('contabilidad.tercero_dian_id'), $datos);
    }

    protected function reversar_reteica(ComprasDocEncabezado $ds, $nota, array $totales, array $datos)
    {
        if ($totales['reteica'] <= 0 || !(int)$ds->reteica_retencion_id) {
            return;
        }
        $retencion = Retencion::find((int)$ds->reteica_retencion_id);
        if ($retencion === null) {
            return;
        }
        $base = NotaAjusteDocSoporteCalculo::proporcional((float)$ds->reteica_base, $totales['base'],
            (float)ComprasDocRegistro::where('compras_doc_encabezado_id', $ds->id)->where('estado', 'Activo')->sum('base_impuesto'));

        $this->reversar_retencion($ds, $nota, $retencion, $totales['reteica'], $base, (float)$ds->reteica_tasa,
            0, (int)config('contabilidad.tercero_reteica_id'), $datos);
    }

    protected function reversar_retencion(ComprasDocEncabezado $ds, $nota, Retencion $retencion, $valor, $base, $tasa, $registroId, $terceroId, array $datos)
    {
        $detalle = 'Reverso retención nota de ajuste DS #' . $ds->id;

        RegistroRetencion::create($datos + [
            'tipo' => 'practicada',
            'numero_certificado' => '', 'fecha_certificado' => '', 'fecha_recepcion_certificado' => '',
            'numero_doc_identidad_agente_retencion' => '', 'razon_social_agente_retencion' => '',
            'contab_retencion_id' => $retencion->id,
            'compras_doc_registro_id' => $registroId,
            'valor_base_retencion' => -$base,
            'tasa_retencion' => $tasa,
            'valor' => -$valor,
            'detalle' => $detalle,
            'modificado_por' => '',
        ]);

        $datosTercero = ['core_tercero_id' => $terceroId, 'inv_producto_id' => 0, 'tipo_transaccion' => ''] + $datos;
        ContabilidadController::contabilizar_registro2($datosTercero, $retencion->cta_compras_id, $detalle, $valor, 0);

        $cxp = CxpMovimiento::where('core_empresa_id', $ds->core_empresa_id)
            ->where('core_tipo_transaccion_id', $ds->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $ds->core_tipo_doc_app_id)
            ->where('consecutivo', $ds->consecutivo)
            ->where('core_tercero_id', $terceroId)
            ->lockForUpdate()->first();

        $this->abonar_o_saldo_a_favor($ds, $nota, $cxp, $valor, $terceroId, 0, $detalle);
    }

    /**
     * Crédito: DB a la CxP y abono a la CxP del DS. Contado: DB a la caja/banco del DS y entrada en tesorería.
     */
    protected function registrar_pago_proveedor(ComprasDocEncabezado $ds, $nota, $neto, array $datos, $detalle, $cxpProveedor)
    {
        if ($neto <= 0) {
            return;
        }
        $datos['inv_producto_id'] = 0;

        if ($ds->forma_pago == 'credito') {
            $cuenta = (new CuentaPorPagarService())->resolver($ds->proveedor_id, (int)$ds->cta_x_pagar_id > 0 ? $ds->cta_x_pagar_id : null, $ds->core_empresa_id);
            ContabilidadController::contabilizar_registro2($datos, $cuenta, $detalle, $neto, 0);
            $this->abonar_o_saldo_a_favor($ds, $nota, $cxpProveedor, $neto, $ds->core_tercero_id, $ds->proveedor_id, $detalle);

            return;
        }

        $pagoDs = TesoMovimiento::where('core_empresa_id', $ds->core_empresa_id)
            ->where('core_tipo_transaccion_id', $ds->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $ds->core_tipo_doc_app_id)
            ->where('consecutivo', $ds->consecutivo)
            ->first();
        $creditoDs = ContabMovimiento::where('core_empresa_id', $ds->core_empresa_id)
            ->where('core_tipo_transaccion_id', $ds->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $ds->core_tipo_doc_app_id)
            ->where('consecutivo', $ds->consecutivo)
            ->where('core_tercero_id', $ds->core_tercero_id)
            ->where('valor_debito', 0)
            ->where(function ($q) {
                $q->where('teso_caja_id', '>', 0)->orWhere('teso_cuenta_bancaria_id', '>', 0);
            })
            ->first();
        if ($pagoDs === null || $creditoDs === null) {
            throw new \InvalidArgumentException('No se encontró el pago de contado del documento soporte en tesorería.');
        }

        ContabilidadController::contabilizar_registro2($datos, $creditoDs->contab_cuenta_id, $detalle, $neto, 0,
            (int)$pagoDs->teso_caja_id, (int)$pagoDs->teso_cuenta_bancaria_id);

        // El proveedor devuelve dinero: entrada (positiva) en la misma caja o banco del DS.
        $motivo = TesoMotivo::find((int)config('tesoreria.motivo_recibo_caja_id')) ?: TesoMotivo::where('movimiento', 'entrada')->first();
        TesoMovimiento::create($datos + [
            'teso_motivo_id' => $motivo ? $motivo->id : $pagoDs->teso_motivo_id,
            'teso_caja_id' => $pagoDs->teso_caja_id,
            'teso_cuenta_bancaria_id' => $pagoDs->teso_cuenta_bancaria_id,
            'teso_medio_recaudo_id' => $pagoDs->teso_medio_recaudo_id,
            'valor_movimiento' => $neto,
        ]);
    }

    protected function cxp_proveedor(ComprasDocEncabezado $ds, $bloquear = false)
    {
        $query = CxpMovimiento::where('core_empresa_id', $ds->core_empresa_id)
            ->where('core_tipo_transaccion_id', $ds->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $ds->core_tipo_doc_app_id)
            ->where('consecutivo', $ds->consecutivo)
            ->where('core_tercero_id', $ds->core_tercero_id)
            ->orderBy('id');

        return $bloquear ? $query->lockForUpdate()->first() : $query->first();
    }

    /**
     * Abona a la CxP del DS hasta su saldo; el excedente queda como saldo a favor (CxP negativa de la nota).
     */
    protected function abonar_o_saldo_a_favor(ComprasDocEncabezado $ds, $nota, $cxp, $valor, $terceroId, $proveedorId, $detalle)
    {
        $abono = $cxp ? min($valor, max(0, (float)$cxp->saldo_pendiente)) : 0;

        if ($abono > 0) {
            $this->mover_saldo($cxp, $abono);
            CxpAbono::create($this->identidad($nota) + [
                'fecha' => $nota->fecha,
                'core_tercero_id' => $terceroId,
                'modelo_referencia_tercero_index' => 'App\Compras\Proveedor',
                'referencia_tercero_id' => $proveedorId,
                'doc_cxp_transacc_id' => $ds->core_tipo_transaccion_id,
                'doc_cxp_tipo_doc_id' => $ds->core_tipo_doc_app_id,
                'doc_cxp_consecutivo' => $ds->consecutivo,
                'doc_cruce_transacc_id' => 0, 'doc_cruce_tipo_doc_id' => 0, 'doc_cruce_consecutivo' => 0,
                'abono' => $abono,
                'creado_por' => $nota->creado_por,
            ]);
        }

        $resto = round($valor - $abono, 2);
        if ($resto > 0) {
            CxpMovimiento::create($this->identidad($nota) + [
                'core_tercero_id' => $terceroId,
                'modelo_referencia_tercero_index' => 'App\Compras\Proveedor',
                'referencia_tercero_id' => $proveedorId,
                'fecha' => $nota->fecha,
                'fecha_vencimiento' => $nota->fecha,
                'valor_documento' => -$resto,
                'valor_pagado' => 0,
                'saldo_pendiente' => -$resto,
                'estado' => 'Pendiente',
                'detalle' => $detalle,
                'creado_por' => $nota->creado_por,
            ]);
        }
    }

    /** $abono > 0 baja el saldo de la CxP; < 0 lo restituye (anulación). */
    protected function mover_saldo(CxpMovimiento $cxp, $abono)
    {
        $saldo = round((float)$cxp->saldo_pendiente - $abono, 2);
        $cxp->update([
            'valor_pagado' => round((float)$cxp->valor_pagado + $abono, 2),
            'saldo_pendiente' => $saldo,
            'estado' => $saldo == 0 ? 'Pagado' : 'Pendiente',
        ]);
    }
}
