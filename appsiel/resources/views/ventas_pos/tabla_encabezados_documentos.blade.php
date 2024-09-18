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

                $lineas_registros_medios_recaudos = (json_decode($fila['campo7'],true));
                
                if( !is_array($lineas_registros_medios_recaudos) )
                {
                    dd($fila['campo7'], $fila);
                }
                
                $valor_propina = 0;
                $valor_datafono = 0;

                foreach ($lineas_registros_medios_recaudos as $linea_medio_recaudo) {
                    $arr_motivo = explode('-',$linea_medio_recaudo['teso_motivo_id']);
                    
                    if ((int)$arr_motivo[0] == (int)config('ventas_pos.motivo_tesoreria_propinas') ) {
                        $valor_propina += (float)substr($linea_medio_recaudo['valor'],1);
                    }
                    
                    if ((int)$arr_motivo[0] == (int)config('ventas_pos.motivo_tesoreria_datafono') ) {
                        $valor_datafono += (float)substr($linea_medio_recaudo['valor'],1);
                    }
                }
            ?>
                <tr>
                    
                    <td>
                        @can('vtas_pos_imprimir_documento_en_consultar_facturas_pdv')
                            <a class="btn btn-info btn-xs btn-detail" href="{{ url('pos_factura_imprimir/'.$fila['campo9'].'?id=20&id_modelo=230&id_transaccion=47') }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a>
                        @endcan

                        &nbsp;&nbsp;&nbsp;

                        
                        @can('vtas_pos_visualizar_documento_en_consultar_facturas_pdv')
                            <a class="btn btn-primary btn-xs btn-detail" href="{{ url('pos_factura/'.$fila['campo9'].'?id=20&id_modelo=230&id_transaccion=47') }}" title="Consultar" id="btn_print" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                        @endcan
                        
                        
                        &nbsp;&nbsp;&nbsp;
                        
                        @if( $fila['campo8'] != 'Anulado' && $fila['campo8'] == 'Pendiente' && $view != 'index')
                            
                        
                            @can('vtas_pos_modificar_documento_en_consultar_facturas_pdv')
                                <a class="btn btn-warning btn-xs btn_modificar_factura" href="{{ url( 'pos_factura/' . $fila['campo9'] . '/edit?id=20&id_modelo=230&id_transaccion=47&action=edit' ) }}" title="Modificar"> <i class="fa fa-edit"></i> </a>
                            @endcan
                                
                            &nbsp;&nbsp;&nbsp;
                                
                            @can('vtas_pos_anular_documento_en_consultar_facturas_pdv')
                                <button class="btn btn-danger btn-xs btn_anular_factura" data-pdv_id="{{ $pdv->id }}" data-doc_encabezado_id="{{$fila['campo9']}}" data-lbl_factura="{{$fila['campo2']}}" title="Anular"> <i class="fa fa-trash"></i> </button>
                            @endcan

                            @if( $valor_propina != 0)
                                &nbsp;&nbsp;&nbsp;
                                <button class="btn btn-danger btn-xs btn_borrar_propina" data-pdv_id="{{ $pdv->id }}" data-doc_encabezado_id="{{$fila['campo9']}}" data-valor_factura="{{$fila['campo6']}}" data-lbl_factura="{{$fila['campo2']}}" title="Borrar propina"> <i class="fa fa-minus-circle"></i> </button>
                            @endif
                        
                        @endif
                    </td>
                    <?php
                        $cantidad = count( $fila );
                    ?>
                    @for( $i=1; $i < $cantidad; $i++)
                        <td class="table-text">
                            @if( $i == 7 )
                                {!! formatear_medio_recaudo( $fila['campo'.$i] ) !!}
                            @else
                                @if( $i == 6)
                                    @can('vtas_pos_ver_valor_documento_en_consultar_facturas_pdv')
                                        {!! $fila['campo'.$i] + $valor_propina + $valor_datafono !!}
                                    @else
                                        $--
                                    @endcan
                                @else
                                    {!! $fila['campo'.$i] !!}
                                @endif
                            @endif                                    
                        </td>
                    @endfor
                </tr>
                <?php
                    if( $fila['campo8'] != 'Anulado' )
                    { 
                        $total_ventas += $fila['campo6'] + $valor_propina + $valor_datafono;
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