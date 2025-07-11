<h3> Documentos de ventas | {{ $pdv->descripcion }}</h3>
<hr>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        {{ Form::bsTableHeader( [ 'Acción', 'Fecha', 'Documento', 'Cliente', 'Cond. pago', 'Detalle', 'Valor total', 'Medio recaudo', 'Estado'] ) }}
        <tbody>

            <?php  
                $total_ventas = 0;
            ?>

            @foreach ($encabezados_documentos as $fila)

            <?php
                $lineas_registros_medios_recaudos = (json_decode($fila->lineas_registros_medios_recaudos,true));
                
                if( !is_array($lineas_registros_medios_recaudos) )
                {
                    dd($fila->lineas_registros_medios_recaudos, $fila);
                }
                
                $valor_propina = 0;
                $valor_datafono = 0;

                foreach ($lineas_registros_medios_recaudos as $linea_medio_recaudo)
                {
                    $arr_motivo = explode('-',$linea_medio_recaudo['teso_motivo_id']);
                    
                    if ((int)$arr_motivo[0] == (int)config('ventas_pos.motivo_tesoreria_propinas') ) {
                        $valor_propina += (float)substr($linea_medio_recaudo['valor'],1);
                    }
                    
                    if ((int)$arr_motivo[0] == (int)config('ventas_pos.motivo_tesoreria_datafono') ) {
                        $valor_datafono += (float)substr($linea_medio_recaudo['valor'],1);
                    }
                }

                $valor_factura = $fila->valor_total + $valor_propina + $valor_datafono + $fila->valor_ajuste_al_peso + $fila->valor_total_bolsas;
            ?>
                <tr>
                    
                    <td>
                        @can('vtas_pos_imprimir_documento_en_consultar_facturas_pdv')
                            <a class="btn btn-info btn-xs btn-detail" href="{{ url('pos_factura_imprimir/'.$fila->id.'?id=20&id_modelo=230&id_transaccion=47') }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a>
                        @endcan

                        &nbsp;&nbsp;&nbsp;

                        
                        @can('vtas_pos_visualizar_documento_en_consultar_facturas_pdv')
                            <a class="btn btn-primary btn-xs btn-detail" href="{{ url('pos_factura/'.$fila->id.'?id=20&id_modelo=230&id_transaccion=47') }}" title="Consultar" id="btn_print" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                        @endcan
                        
                        
                        &nbsp;&nbsp;&nbsp;
                        
                        @if( $fila->estado != 'Anulado' && $fila->estado == 'Pendiente' && $view != 'index')
                        
                            @can('vtas_pos_modificar_documento_en_consultar_facturas_pdv')
                                <a class="btn btn-warning btn-xs btn_modificar_factura" href="{{ url( 'pos_factura/' . $fila->id . '/edit?id=20&id_modelo=230&id_transaccion=47&action=edit' ) }}" title="Modificar"> <i class="fa fa-edit"></i> </a>
                            @endcan
                                
                            &nbsp;&nbsp;&nbsp;
                                
                            @can('vtas_pos_anular_documento_en_consultar_facturas_pdv')
                                <button class="btn btn-danger btn-xs btn_anular_factura" data-pdv_id="{{ $pdv->id }}" data-doc_encabezado_id="{{$fila->id}}" data-lbl_factura="{{$fila->get_label_documento()}}" title="Anular"> <i class="fa fa-trash"></i> </button>
                            @endcan

                            @if( $valor_propina != 0)
                                &nbsp;&nbsp;&nbsp;
                                <button class="btn btn-danger btn-xs btn_borrar_propina" data-pdv_id="{{ $pdv->id }}" data-doc_encabezado_id="{{$fila->id}}" data-valor_factura="{{$valor_factura}}" data-lbl_factura="{{$fila->get_label_documento()}}" title="Borrar propina"> <i class="fa fa-minus-circle"></i> </button>
                            @endif
                        
                        @endif
                    </td>
                    <td class="table-text">
                        {{ $fila->fecha }}
                    </td>
                    <td class="table-text">
                        {{ $fila->get_label_documento() }}
                    </td>
                    <td class="table-text">
                        {{ $fila->cliente->tercero->get_label_to_show() }}
                    </td>
                    <td class="table-text">
                        {{ $fila->condicion_pago }}
                    </td>
                    <td class="table-text">
                        {{ $fila->descripcion }}
                    </td>
                    <td class="table-text">
                        @can('vtas_pos_ver_valor_documento_en_consultar_facturas_pdv')
                            $ {{ number_format($valor_factura,0,',','.') }}
                        @else
                            $--
                        @endcan                            
                    </td>
                    <td class="table-text">
                        {!! formatear_medio_recaudo( $fila->lineas_registros_medios_recaudos ) !!}
                    </td>
                    <td class="table-text">
                        @if( $fila->estado == 'Anulado' )
                            <span class="label label-danger">{{ $fila->estado }}</span>
                        @elseif( $fila->estado == 'Pendiente' )
                            <span class="label label-warning">{{ $fila->estado }}</span>
                        @else
                            <span class="label label-success">{{ $fila->estado }}</span>
                        @endif
                    </td>
                </tr>
                <?php
                    if( $fila->estado != 'Anulado' )
                    { 
                        $total_ventas += $valor_factura;
                    }
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"> Total Venta </td>
                <td class="text-tight">
                    @can('vtas_pos_ver_valor_documento_en_consultar_facturas_pdv')
                        ${{ number_format( $total_ventas, 0, ',','.') }}
                    @else
                        $--
                    @endcan
                </td>
                <td colspan="2"> </td>
            </tr>
        </tfoot>
    </table>
</div>

<?php 
    function formatear_medio_recaudo( $lineas_registros_medios_recaudos )
    {
        $lista_medios_recaudos = '<ul>';
        $lineas_recaudos = json_decode( $lineas_registros_medios_recaudos );

        if ( !is_null( $lineas_recaudos ) )
        {
            foreach( $lineas_recaudos as $linea )
            {
                if(!isset(explode("-", $linea->teso_medio_recaudo_id)[1]))
                {
                    continue;
                }
                
                $lista_medios_recaudos .= '<li>';

                $lista_medios_recaudos .= explode("-", $linea->teso_medio_recaudo_id)[1];

                $lista_medios_recaudos .= '</li>';
            }
        }else{
                $lista_medios_recaudos .= '<li> Efectivo </li> ';
        }

        $lista_medios_recaudos .= '</ul>';

        return $lista_medios_recaudos;
    }
?>