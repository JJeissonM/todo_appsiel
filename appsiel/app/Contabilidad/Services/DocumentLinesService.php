<?php 

namespace App\Contabilidad\Services;

use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxC\CxcMovimiento;

class DocumentLinesService
{
    public function store_lines_and_accounting( ContabDocEncabezado $document_head, string $document_lines )
    {
        $tabla_registros_documento = json_decode($document_lines);

        $cantidad = count($tabla_registros_documento);

        for ( $i=0; $i < $cantidad; $i++ )
        {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
            $vec_1 = explode("-", $tabla_registros_documento[$i]->Cuenta);
            $contab_cuenta_id = $vec_1[0];

            $vec_2 = explode("-", $tabla_registros_documento[$i]->Tercero);


            $core_tercero_id = (int)$vec_2[0];
            
            if ( $core_tercero_id == 0 )
            {
                $core_tercero_id = (int)$document_head->core_tercero_id;
            }

            $detalle_operacion = $tabla_registros_documento[$i]->Detalle;

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor_debito = substr($tabla_registros_documento[$i]->debito, 1);
            $valor_credito = substr($tabla_registros_documento[$i]->credito, 1);

            /**/
            $registro_doc = ContabDocRegistro::create(
                            [ 'contab_doc_encabezado_id' => $document_head->id ] + 
                            [ 'contab_cuenta_id' => (int)$contab_cuenta_id ] + 
                            [ 'core_tercero_id' => $core_tercero_id ] + 
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => (float)$valor_debito] + 
                            [ 'valor_credito' => (float)$valor_credito] + 
                            [ 'tipo_transaccion' => $tabla_registros_documento[$i]->tipo_transaccion ]
                        );


            // 1.1. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento
            if ($detalle_operacion == '') {
                $detalle_operacion = $document_head->descripcion;
            }

            $datos = array_merge( $document_head->toArray(), [
                                                            'id_registro_doc_tipo_transaccion' => $registro_doc->id,
                                                            'fecha_vencimiento' => $tabla_registros_documento[$i]->fecha_vencimiento,
                                                            'documento_soporte' => $tabla_registros_documento[$i]->documento_soporte_tercero,
                                                            'tipo_transaccion' => $tabla_registros_documento[$i]->tipo_transaccion] );
            
            $this->contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            // Generar CxP.
            if ( $tabla_registros_documento[$i]->tipo_transaccion == 'crear_cxp' )
            {
                $datos['valor_documento'] = $valor_credito;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $valor_credito;
                $datos['fecha_vencimiento'] = $tabla_registros_documento[$i]->fecha_vencimiento;
                $datos['doc_proveedor_consecutivo'] = $tabla_registros_documento[$i]->documento_soporte_tercero;
                $datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $datos );
            }

            // Generar AnticIipo CxP.
            if ( $tabla_registros_documento[$i]->tipo_transaccion == 'crear_anticipo_cxp' )
            {

                $datos['valor_documento'] = $valor_debito * -1;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $valor_debito * -1;
                $datos['fecha_vencimiento'] = $datos['fecha'];
                $datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $datos );
            }

            // Generar CxC.
            if ( $tabla_registros_documento[$i]->tipo_transaccion == 'crear_cxc' )
            {
                $datos['valor_documento'] = $valor_debito;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $valor_debito;
                $datos['fecha_vencimiento'] = $tabla_registros_documento[$i]->fecha_vencimiento;
                $datos['estado'] = 'Pendiente';
                CxcMovimiento::create( $datos );
            }

            // Generar Anticpo CxC.
            if ( $tabla_registros_documento[$i]->tipo_transaccion == 'crear_anticipo_cxc' )
            {
                $datos['valor_documento'] = $valor_credito * -1;
                $datos['valor_pagado'] = 0;
                $datos['saldo_pendiente'] = $valor_credito * -1;
                $datos['fecha_vencimiento'] = $tabla_registros_documento[$i]->fecha_vencimiento;
                $datos['estado'] = 'Pendiente';
                CxcMovimiento::create( $datos );
            }

        }
    }

    public function contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id]
                        );
    }
}