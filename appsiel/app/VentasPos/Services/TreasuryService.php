<?php

namespace App\VentasPos\Services;

use App\CxC\CxcMovimiento;
use App\Http\Controllers\Tesoreria\RecaudoCxcController;
use Illuminate\Http\Request;
use \View;

class TreasuryService
{
    /**
     * Crea un abono a un documento CxC.
     */
    public function crear_abonos_documento($doc_encabezado_factura, $lineas_registros_medios_recaudos)
    {
        $aux_object = $this->get_lineas_registros_medios_recaudos( $lineas_registros_medios_recaudos );

        if ( $aux_object->valor_abono == 0) {
            return false;
        }
        
        (new RecaudoCxcController())->store( $this->build_request_object($doc_encabezado_factura, $aux_object) );
    }

    /**
     * Construye el objeto de solicitud para crear un cruce de documentos CxC.
     */
    public function build_request_object( $doc_encabezado_factura, $aux_object)
    {
        $request = new Request();

        $request["core_empresa_id"] = $doc_encabezado_factura->core_empresa_id;
        $request["core_tipo_doc_app_id"] = config('tesoreria.recaudos_cxc_tipo_doc_app_id');
        $request["fecha"] = $doc_encabezado_factura->fecha;
        $request["core_tercero_id"] = $doc_encabezado_factura->core_tercero_id;
        $request["referencia_tercero_id"] = $doc_encabezado_factura->cliente_id;
        $request["descripcion"] = "";
        $request["documento_soporte"] = "";
        $request["consecutivo"] = "";
        $request["estado"] = "Activo";
        $request["modificado_por"] = "0";
        $request["creado_por"] = $doc_encabezado_factura->creado_por;
        $request["core_tipo_transaccion_id"] = config('tesoreria.recaudos_cxc_tipo_transaccion_id');

        $request["url_id"] = "3"; // Tesoreria
        $request["url_id_modelo"] = config('tesoreria.recaudos_cxc_modelo_id');
        $request["url_id_transaccion"] = config('tesoreria.recaudos_cxc_tipo_transaccion_id');

        $request["cliente_id"] = $doc_encabezado_factura->cliente_id;

        $request["tipo_recaudo_aux"] = "";

        $request["lineas_registros_retenciones"] = "";
        $request["lineas_registros_descuento_pronto_pagos"] = "";
        $request["lineas_registros_asientos_contables"] = "";

        $request["lineas_registros_efectivo"] = '[' . $aux_object->lineas_registros_efectivo . ']';

        $request["lineas_registros_transferencia_consignacion"] = '[' . $aux_object->lineas_registros_transferencia_consignacion . ']';

        $cxc_movimiento_id = CxcMovimiento::where('core_empresa_id', $doc_encabezado_factura->core_empresa_id)
            ->where('core_tipo_transaccion_id', $doc_encabezado_factura->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $doc_encabezado_factura->core_tipo_doc_app_id)
            ->where('consecutivo', $doc_encabezado_factura->consecutivo)
            ->first()->id;

        $request["lineas_registros"] = '[{"id_doc":"' . $cxc_movimiento_id . '","Cliente":"--","Documento interno":"--","Fecha":"--","Fecha vencimiento":"--","Valor Documento":"00","Valor pagado":"00","Saldo pendiente":"00","abono":"' . $aux_object->valor_abono . '"},{"id_doc":"","Cliente":"","Documento interno":"00","Fecha":"","Fecha vencimiento":"","Valor Documento":"","Valor pagado":"","Saldo pendiente":""}]';

        $request["lineas_registros_tarjeta_debito"] = "";
        $request["lineas_registros_tarjeta_credito"] = "";
        $request["lineas_registros_cheques"] = "";

        return $request;
    }

    public function get_lineas_registros_medios_recaudos( $lineas_registros_medios_recaudos )
    {
        $valor_abono = 0;
        $lineas_registros_efectivo = '';
        $lineas_registros_transferencia_consignacion = '';
        
        $lineas_recaudos = json_decode($lineas_registros_medios_recaudos);

        if ( $lineas_recaudos != null )
        {
            foreach ($lineas_recaudos as $linea)
            {
                if(explode("-", $linea->teso_medio_recaudo_id)[0] == 0) // Se excluyen los Anticipos
                {
                    continue;
                }

                $teso_caja_id = explode("-", $linea->teso_caja_id)[0];

                if ( $teso_caja_id == 0 )
                {
                    // Si la caja es 0, es una linea de registro por transferencia o consignación
                    $lineas_registros_transferencia_consignacion .= '{"tipo_operacion_id_transferencia_consignacion":"' . config('tesoreria.tipo_operacion_recaudos_cxc') . '","teso_motivo_id_transferencia_consignacion":"' . explode("-", $linea->teso_motivo_id)[0] . '","banco_id_transferencia_consignacion":"' . explode("-", $linea->teso_cuenta_bancaria_id)[0] . '","valor_transferencia_consignacion":"' . (float)substr($linea->valor, 1) . '","Operación":"--","Motivo":"--","numero_comprobante_transferencia_consignacion":"","Caja":"--","Valor":"00"},';

                }else{

                    $lineas_registros_efectivo .= '{"tipo_operacion_id_efectivo":"' . config('tesoreria.tipo_operacion_recaudos_cxc') . '","teso_motivo_id_efectivo":"' . explode("-", $linea->teso_motivo_id)[0] . '","caja_id_efectivo":"' . explode("-", $linea->teso_caja_id)[0] . '","valor_efectivo":"' . (float)substr($linea->valor, 1) . '","Operación":"--","Motivo":"--","Caja":"--","Valor":"00"},';
                }
                
                $valor_abono += (float)substr($linea->valor, 1);
            }

            $lineas_registros_transferencia_consignacion .= '{"tipo_operacion_id_transferencia_consignacion":"","teso_motivo_id_transferencia_consignacion":"00","banco_id_transferencia_consignacion":"","valor_transferencia_consignacion":""}';

            $lineas_registros_efectivo .= '{"tipo_operacion_id_efectivo":"","teso_motivo_id_efectivo":"00","caja_id_efectivo":""}';    
        }

        return (object)[
            'lineas_registros_efectivo' => $lineas_registros_efectivo,
            'lineas_registros_transferencia_consignacion' => $lineas_registros_transferencia_consignacion,
            'valor_abono' => $valor_abono
        ];
    }
}
