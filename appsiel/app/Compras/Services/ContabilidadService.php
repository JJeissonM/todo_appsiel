<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\RegistroRetencion;
use App\Contabilidad\Retencion;
use App\CxP\CxpMovimiento;

class ContabilidadService
{
    public function aplicar_retenciones_por_linea_compras(ComprasDocEncabezado $doc_encabezado)
    {
        $lineas = ComprasDocRegistro::where('compras_doc_encabezado_id', $doc_encabezado->id)
            ->where('contab_retencion_id', '>', 0)
            ->where('valor_retencion', '>', 0)
            ->get();

        if (empty($lineas->toArray())) {
            return false;
        }

        $service = new RetencionFuenteService();
        $total_retenciones = 0;
        $ultimo_dato_movimiento = [];

        foreach ($lineas as $linea) {
            $retencion = Retencion::find((int)$linea->contab_retencion_id);
            if (is_null($retencion)) {
                continue;
            }

            $datos_retencion = $service->calcular_valor_retencion_linea(
                $linea->precio_unitario,
                $linea->cantidad,
                $linea->tasa_impuesto,
                $retencion->tasa_retencion
            );

            $valor_base_retencion = (float)$datos_retencion['base_sin_iva'];
            $valor_retencion = (float)$datos_retencion['valor_retencion'];
            if ($valor_retencion <= 0) {
                continue;
            }

            $tipo = 'practicada';
            $datos = [
                'tipo' => $tipo,
                'numero_certificado' => '',
                'fecha_certificado' => '',
                'fecha_recepcion_certificado' => '',
                'numero_doc_identidad_agente_retencion' => '',
                'razon_social_agente_retencion' => '',
                'contab_retencion_id' => (int)$retencion->id,
                'valor_base_retencion' => $valor_base_retencion,
                'tasa_retencion' => (float)$retencion->tasa_retencion,
                'valor' => $valor_retencion,
                'detalle' => 'Factura de compras, línea #' . $linea->id,
                'compras_doc_registro_id' => $linea->id
            ] + $doc_encabezado->toArray();

            $datos['estado'] = 'Activo';

            RegistroRetencion::create($datos);

            // NIIF: retención practicada como menor valor pagado al proveedor y pasivo tributario.
            $datos['tipo_transaccion'] = '';
            $datos['core_tercero_id'] = (int)config('contabilidad.tercero_dian_id');
            (new ContabMovimiento())->contabilizar_linea_registro($datos, $retencion->cta_compras_id, 'Retención ' . $tipo, 0, $valor_retencion);

            $total_retenciones += $valor_retencion;
            $ultimo_dato_movimiento = $datos;

            $linea->tasa_retencion = (float)$retencion->tasa_retencion;
            $linea->valor_retencion = $valor_retencion;
            $linea->save();
        }

        if ($total_retenciones > 0 && !empty($ultimo_dato_movimiento)) {
            $ultimo_dato_movimiento['valor_documento'] = $total_retenciones;
            $ultimo_dato_movimiento['valor_pagado'] = 0;
            $ultimo_dato_movimiento['saldo_pendiente'] = $total_retenciones;
            $ultimo_dato_movimiento['estado'] = 'Pendiente';
            CxpMovimiento::create($ultimo_dato_movimiento);
        }

        return true;
    }

    // Compatibilidad con llamadas existentes.
    public function aplicar_retencion_factura_compras(ComprasDocEncabezado $doc_encabezado, $data)
    {
        return $this->aplicar_retenciones_por_linea_compras($doc_encabezado);
    }

    public function get_valor_retenciones($doc_encabezado)
    {
        return $this->get_retenciones($doc_encabezado)->sum('valor');
    }

    public function get_retenciones($doc_encabezado)
    {
        return RegistroRetencion::where([
            ['core_tipo_transaccion_id', '=', $doc_encabezado->core_tipo_transaccion_id],
            ['core_tipo_doc_app_id', '=', $doc_encabezado->core_tipo_doc_app_id],
            ['consecutivo', '=', $doc_encabezado->consecutivo]
        ])->get();
    }
}
