<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\RegistroRetencion;
use App\Contabilidad\Retencion;
use App\CxP\CxpMovimiento;

class ContabilidadService
{
    public function aplicar_retencion_factura_compras( ComprasDocEncabezado $doc_encabezado, $data)
    {
        $valor_retencion = (float)$data['valor_total_retefuente'];

        $retencion = Retencion::find( (int)$data['retencion_id'] );

        $tasa_retencion = $retencion->tasa_retencion;

        $valor_base_retencion = 0; // PENDIENTE

        $tipo = 'practicada';
        $datos = [
                    'tipo' => $tipo,
                    'numero_certificado' => '',
                    'fecha_certificado' => '',
                    'fecha_recepcion_certificado' => '',
                    'numero_doc_identidad_agente_retencion' => '',
                    'razon_social_agente_retencion' => '',
                    'contab_retencion_id' => (int)$data['retencion_id'],
                    'valor_base_retencion' => $valor_base_retencion,
                    'tasa_retencion' => $tasa_retencion,
                    'valor' => $valor_retencion,
                    'detalle' => 'Factura de compras'
                ] + $doc_encabezado->toArray();

        $datos['estado'] = 'Activo';
        
        RegistroRetencion::create( $datos );

        // Contabilizar Retención
        $datos['tipo_transaccion'] = '';

        $datos['core_tercero_id'] = (int)config('contabilidad.tercero_dian_id');
        
        (new ContabMovimiento())->contabilizar_linea_registro( $datos, $retencion->cta_compras_id, 'Retención ' . $tipo, 0, $valor_retencion );

        // Generar CxP
        $datos['valor_documento'] = $valor_retencion;
        $datos['valor_pagado'] = 0;
        $datos['saldo_pendiente'] = $valor_retencion;
        $datos['estado'] = 'Pendiente';

        CxpMovimiento::create( $datos );

    }

    public function get_valor_retenciones( $doc_encabezado )
    {
        return $this->get_retenciones( $doc_encabezado )->sum('valor');
    }

    public function get_retenciones( $doc_encabezado )
    {
        return RegistroRetencion::where([
                                [ 'core_tipo_transaccion_id', '=', $doc_encabezado->core_tipo_transaccion_id ],
                                [ 'core_tipo_doc_app_id', '=', $doc_encabezado->core_tipo_doc_app_id ],
                                [ 'consecutivo', '=', $doc_encabezado->consecutivo ]
                            ])
                            ->get();
    }
}