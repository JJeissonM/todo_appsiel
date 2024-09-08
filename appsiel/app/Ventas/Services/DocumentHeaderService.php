<?php 

namespace App\Ventas\Services;

use App\Contabilidad\ContabMovimiento;
use App\Core\EncabezadoDocumentoTransaccion;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Matriculas\FacturaAuxEstudiante;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\Cliente;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class DocumentHeaderService
{
    /*
        Proceso de eliminar FACTURA DE VENTAS
        Se eliminan los registros de:
            - cxc_documentos_pendientes (se debe verificar que no tenga un abono, sino se debe eliminar primero el abono) y su movimiento en contab_movimientos
            - inv_movimientos de la REMISIÓN y su contabilidad. Además se actualiza el estado a Anulado en inv_doc_registros e inv_doc_encabezados
            - vtas_movimientos y su contabilidad. Además se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public function cancel_document_by_id( int $document_header_id, bool $cancel_deliveries_notes )
    {
        $document_header = VtasDocEncabezado::find( $document_header_id );

        $array_wheres = ['core_empresa_id'=>$document_header->core_empresa_id,
            'core_tipo_transaccion_id' => $document_header->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $document_header->core_tipo_doc_app_id,
            'consecutivo' => $document_header->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $abonos = CxcAbono::where('doc_cxc_transacc_id',$document_header->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$document_header->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$document_header->consecutivo)
                            ->get();

        if (!empty($abonos->toArray())) {
            $lista_abonos = '';
            foreach ($abonos as $abono) {
                $lista_abonos .= ' *** ' . $abono->payment_document_header()->get_label_documento();
            }
            return (object)[
                'status'=>'mensaje_error',
                'message'=>'Factura ' . $document_header->get_label_documento()  . ' NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería): ' . $lista_abonos
            ];
        }

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las remisiones relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode( ',', $document_header->remision_doc_encabezado_id );
        $cant_registros = count($ids_documentos_relacionados);
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $remision = InvDocEncabezado::find( $ids_documentos_relacionados[$i] );
            if ( !is_null($remision) )
            {
                if ( $cancel_deliveries_notes ) // cancel_deliveries_notes es tipo boolean
                {
                    InventarioController::anular_documento_inventarios( $remision->id );
                }else{
                    $remision->update(['estado'=>'Pendiente', 'modificado_por' => $modificado_por]);
                }    
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar y de tesorería
        CxcMovimiento::where($array_wheres)->delete();
        TesoMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de ventas
        VtasMovimiento::where($array_wheres)->delete();
        // 5to. Se marcan como anulados los registros del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $document_header->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $document_header->update(['estado'=>'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);

        // 7mo. Si es una factura de Estudiante
        $factura_estudiante = FacturaAuxEstudiante::where('vtas_doc_encabezado_id',$document_header->id)->get()->first();
        if (!is_null($factura_estudiante))
        {
            $factura_estudiante->delete();
        }

        return (object)[
            'status'=>'flash_message',
            'message'=>'Factura de ventas ' . $document_header->get_label_documento()  . ' ANULADA correctamente.'
        ];
    }
    
    public function actions_buttos_to_show_view( $doc_encabezado, $docs_relacionados )
    {
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion');

        $actions = [];

        switch ($doc_encabezado-> core_tipo_transaccion_id) {
            case '23':
                if( $doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado != 'Pendiente' )
                {
                    if( !$docs_relacionados[1] )
                    {

                        $actions[] = (object)[
                            'tag_html' => 'a',
                            'target' => null,
                            'id' => null,
                            'url' => url( 'ventas/' . $doc_encabezado->id . '/edit' . $variables_url ),
                            'title' => 'Modificar',
                            'color_bootstrap' => null,
                            'faicon' => 'edit',
                            'size' => null,
                        ];
                        
                        $actions[] = (object)[
                            'tag_html' => 'a',
                            'target' => null,
                            'id' => null,
                            'url' => url('ventas_notas_credito/create?factura_id=' . $doc_encabezado->id . '&id=' . Input::get('id') . '&id_modelo=167&id_transaccion=38'),
                            'title' => 'Nota crédito',
                            'color_bootstrap' => null,
                            'faicon' => 'file-text',
                            'size' => null,
                        ];
                    }
                    
                    $actions[] = (object)[
                        'tag_html' => 'a',
                        'target' => '_blank',
                        'id' => null,
                        'url' => url('tesoreria/recaudos_cxc/create?id=' . Input::get('id') . '&id_modelo=153&id_transaccion=32'),
                        'title' => 'Hacer abono',
                        'color_bootstrap' => null,
                        'faicon' => 'money',
                        'size' => null,
                    ];
                    
                    $actions[] = (object)[
                        'tag_html' => 'button',
                        'target' => null,
                        'id' => 'btn_anular',
                        'url' => url('tesoreria/recaudos_cxc/create?id=' . Input::get('id') . '&id_modelo=153&id_transaccion=32'),
                        'title' => 'Anular',
                        'color_bootstrap' => null,
                        'faicon' => 'close',
                        'size' => null,
                    ];
                    
                    if ( Auth::user()->hasPermissionTo('vtas_recontabilizar') ) {
                        $actions[] = (object)[
                            'tag_html' => 'a',
                            'target' => null,
                            'id' => null,
                            'url' => url( 'ventas_recontabilizar/' . $doc_encabezado->id . $variables_url ),
                            'title' => 'Recontabilizar',
                            'color_bootstrap' => null,
                            'faicon' => 'cog',
                            'size' => null,
                        ];
                    }
                }
                break;
            
            default:
                # code...
                break;
        }        

        return $actions;
    }

    public function crear_movimiento_ventas( $vta_doc_encabezado )
    {
        $lineas_registros = $vta_doc_encabezado->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            $datos = $vta_doc_encabezado->toArray() + $linea->toArray();

            // Movimiento de Ventas
            $datos['estado'] = 'Activo';

            VtasMovimiento::create($datos);
        }
    }

    public function contabilizar_movimiento_debito( $vta_doc_encabezado, $caja_banco_id = null )
    {
        $datos = $vta_doc_encabezado->toArray();
        $datos['registros_medio_pago'] = [];

        $movimiento_contable = new ContabMovimiento();
        $detalle_operacion = 'Contabilización ' . $vta_doc_encabezado->tipo_transaccion->descripcion . ' ' . $vta_doc_encabezado->tipo_documento_app->prefijo . ' ' . $vta_doc_encabezado->consecutivo;
        if ( $vta_doc_encabezado->forma_pago == 'credito')
        {
            // La cuenta de CARTERA se toma de la clase del cliente
            $cta_x_cobrar_id = Cliente::get_cuenta_cartera( $vta_doc_encabezado->cliente_id );
            $datos['tipo_transaccion'] = 'cxc';
            $movimiento_contable->contabilizar_linea_registro( $datos, $cta_x_cobrar_id, $detalle_operacion, $vta_doc_encabezado->valor_total, 0);
        }
        
        // Agregar el movimiento a tesorería
        if ( $vta_doc_encabezado->forma_pago == 'contado')
        {
            if( is_null( $caja_banco_id ) )
            {
                if ( empty( $datos['registros_medio_pago'] ) )
                {   
                    // Por defecto
                    $caja = TesoCaja::get()->first();
                    $teso_caja_id = $caja->id;
                    $teso_cuenta_bancaria_id = 0;
                    $contab_cuenta_id = $caja->contab_cuenta_id;
                }else{

                    // WARNING!!! Por ahora solo se está aceptando un solo medio de pago
                    $contab_cuenta_id = TesoCaja::find( 1 )->contab_cuenta_id;

                    $teso_caja_id = $datos['registros_medio_pago']['teso_caja_id'];
                    if ($teso_caja_id != 0)
                    {
                        $contab_cuenta_id = TesoCaja::find( $teso_caja_id )->contab_cuenta_id;
                    }

                    $teso_cuenta_bancaria_id = $datos['registros_medio_pago']['teso_cuenta_bancaria_id'];
                    if ($teso_cuenta_bancaria_id != 0)
                    {
                        $contab_cuenta_id = TesoCuentaBancaria::find( $teso_cuenta_bancaria_id )->contab_cuenta_id;
                    }                    
                }
            }else{
                // $caja_banco_id se manda desde Ventas POS
                $caja = TesoCaja::find( $caja_banco_id );
                $teso_caja_id = $caja->id;
                $teso_cuenta_bancaria_id = 0;
                $contab_cuenta_id = $caja->contab_cuenta_id;
            }
            
            $datos['teso_caja_id'] = $teso_caja_id;
            $datos['teso_cuenta_bancaria_id'] = $teso_cuenta_bancaria_id;
            $datos['tipo_transaccion'] = 'recaudo';
            $movimiento_contable->contabilizar_linea_registro( $datos, $contab_cuenta_id, $detalle_operacion, $vta_doc_encabezado->valor_total, 0 );
        }
    }

    // Contabilizar Ingresos de ventas e Impuestos
    public function contabilizar_movimiento_credito( $vta_doc_encabezado )
    {
        $datos = $vta_doc_encabezado->toArray();
        $detalle_operacion = 'Contabilización ' . $vta_doc_encabezado->tipo_transaccion->descripcion . ' ' . $vta_doc_encabezado->tipo_documento_app->prefijo . ' ' . $vta_doc_encabezado->consecutivo;

        $lineas_registros = $vta_doc_encabezado->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            $una_linea_registro = $datos + $linea->toArray();
            $una_linea_registro['creado_por'] = 'paula@appsiel.com.co';
            if(Auth::user()){
                $una_linea_registro['creado_por']  = Auth::user()->email;
            }   
            $una_linea_registro['modificado_por'] = '';
            $una_linea_registro['estado'] = 'Activo';
            $una_linea_registro['tipo_transaccion'] = 'facturacion_ventas';

            $movimiento_contable = new ContabMovimiento();

            // IVA generado (CR)
            // Si se ha liquidado impuestos en la transacción
            $valor_total_impuesto = 0;
            if ( $una_linea_registro['tasa_impuesto'] > 0 )
            {
                $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_ventas( $una_linea_registro['inv_producto_id'] );
                $valor_total_impuesto = abs( $una_linea_registro['valor_impuesto'] * $una_linea_registro['cantidad'] );

                $movimiento_contable->contabilizar_linea_registro( $una_linea_registro, $cta_impuesto_ventas_id, $detalle_operacion, 0, abs($valor_total_impuesto) );
            }

            // Contabilizar Ingresos (CR)
            // La cuenta de ingresos se toma del grupo de inventarios
            $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $una_linea_registro['inv_producto_id'] );
            $movimiento_contable->contabilizar_linea_registro( $una_linea_registro, $cta_ingresos_id, $detalle_operacion, 0, $una_linea_registro['base_impuesto_total'] );
        }                
    }

    /*
        Movimiento de Tesoreria o Cartera de clientes (CxC)
    */
    public function crear_registro_pago( $forma_pago, $datos, $total_documento )
    {
        // Cargar la cuenta por cobrar (CxC)
        if ( $forma_pago == 'credito')
        {
            $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
            $datos['referencia_tercero_id'] = $datos['cliente_id'];
            $datos['valor_documento'] = $total_documento;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $total_documento;
            $datos['estado'] = 'Pendiente';
            CxcMovimiento::create( $datos );
        }

        if ( $forma_pago == 'contado')
        {
            $teso_movimiento = new TesoMovimiento();
            $teso_movimiento->almacenar_registro_pago_contado( $datos, $datos['registros_medio_pago'], 'entrada', $total_documento );
        }
    }

    public function determinar_posibles_existencias_negativas( $vta_doc_encabezado )
    {
        if ((int)config('ventas.permitir_inventarios_negativos')) {
            return 0;
        }

        $lineas_registros = $vta_doc_encabezado->lineas_registros;
        foreach ($lineas_registros as $linea)
        {
            if ( $linea->item->tipo == 'servicio' )
            {
                continue;
            }
            
            $existencia_actual = InvMovimiento::get_existencia_actual( $linea->inv_producto_id, $vta_doc_encabezado->cliente->inv_bodega_id, $vta_doc_encabezado->fecha );

            if ( ( $existencia_actual - abs($linea->cantidad) ) < 0 )
            {
                return 1;
            }
        }
        return 0;
    }

    public function clonar_encabezado( $vta_doc_encabezado_padre, $fecha, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $descripcion, $modelo_id )
    {
        $datos = $vta_doc_encabezado_padre->toArray();

        if ( $fecha != null )
        {
            $datos['fecha'] = $fecha;
        }

        if ( $core_tipo_transaccion_id != null )
        {
            $datos['core_tipo_transaccion_id'] = $core_tipo_transaccion_id;
        }

        if ( $core_tipo_doc_app_id != null )
        {
            $datos['core_tipo_doc_app_id'] = $core_tipo_doc_app_id;
        }

        if ( $descripcion != null )
        {
            $datos['descripcion'] = $descripcion;
        }

        $datos['consecutivo'] = 0;
        $datos['id'] = 0;

        return (new EncabezadoDocumentoTransaccion( $modelo_id ))->crear_nuevo( $datos );
    }

    public function clonar_lineas_registros( $vta_doc_encabezado_padre, $vtas_doc_encabezado_hijo_id )
    {
        $lineas_registros = $vta_doc_encabezado_padre->lineas_registros;

        foreach ($lineas_registros as $linea)
        {
            $datos = $linea->toArray();
            $datos['vtas_doc_encabezado_id'] = $vtas_doc_encabezado_hijo_id;
            $datos['creado_por'] = 'paula@appsiel.com.co';
            if(Auth::user()){
                $datos['creado_por'] = Auth::user()->email;
            }   
            $datos['modificado_por'] = '';

            VtasDocRegistro::create( $datos );
        }
    }
}