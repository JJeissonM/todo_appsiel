<h2 align="center"> Movimiento de cartera </h2>

<table class="table table-bordered" id="documentos_pendientes">
    {{ Form::bsTableHeader(['Inmueble','Documento','Fecha','Fecha Vence','Detalle','Vlr. cartera','Saldo pend.','Acción']) }}
    <tbody>
        <?php
        for($i=0; $i<count($movimiento_cxc); $i++){ 
            //$id = $movimiento_cxc[$i]['id'];

            $tipo_transaccion = App\Sistema\TipoTransaccion::find( $movimiento_cxc[$i]['core_tipo_transaccion_id'] );

            /*$documento_id = 0;*/
            $documento_id = app($tipo_transaccion->modelo_encabezados_documentos)
                                ->where('core_tipo_transaccion_id',$movimiento_cxc[$i]['core_tipo_transaccion_id'])
                                ->where('core_tipo_doc_app_id',$movimiento_cxc[$i]['core_tipo_doc_app_id'])
                                ->where('consecutivo',$movimiento_cxc[$i]['consecutivo'])
                                ->value('id');
            
            //print_r( explode('\\',$tipo_transaccion->modelo_encabezados_documentos) );
            /*
            para RC debe llamar a 
            $view_pdf = RecaudoController::vista_preliminar($id,'imprimir');

            para FA debe llamar a 
            $view_pdf = CxCController::vista_preliminar($id,'show');
            */

            ?>
            <tr id="{{ $documento_id }}">
                <td class="text-center"> {{ $movimiento_cxc[$i]['codigo'] }} </td>
                <td class="text-center"> {{ $movimiento_cxc[$i]['documento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha_vencimiento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['detalle_operacion'] }} </td>
                <td class="col_valor_cartera text-right"> {{ number_format($movimiento_cxc[$i]['valor_cartera'], 0, ',', '.') }} </td>
                <td class="col_saldo_pendiente text-right" > {{ number_format($movimiento_cxc[$i]['saldo_pendiente'], 0, ',', '.') }} </td>
                <td> 
                    <button class="btn btn-primary btn-xs btn_ver_documento"><i class="fa fa-eye"></i></button>
                </td>
            </tr>
        <?php 
        } ?>
    </tbody>
</table>