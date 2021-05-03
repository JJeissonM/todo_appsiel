<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Empleado','Concepto','Cant. horas','Vlr. unitario','Vlr. total','']) }}
        <tbody>
            <?php
                $total_cantidad_horas = 0;
                $total_valor_devengo = 0;
                $empleados = $orden_de_trabajo->empleados;
            ?>
            @foreach( $empleados as $empelado_orden_trabajo )
                <tr>
                    <td> {{ $empelado_orden_trabajo->contrato->tercero->descripcion }} / {{ $empelado_orden_trabajo->contrato->tercero->numero_identificacion }} </td>
                    <td> {{ $empelado_orden_trabajo->concepto->descripcion }} </td>
                    <td> {{ number_format( $empelado_orden_trabajo->cantidad_horas, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $empelado_orden_trabajo->valor_por_hora, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $empelado_orden_trabajo->valor_devengo, 2, ',', '.') }} </td>
                    <td>
                        @if( !in_array( $orden_de_trabajo->estado, ['Anulado'] ) )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$empelado_orden_trabajo->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php 
                    $total_cantidad_horas += $empelado_orden_trabajo->cantidad_horas;
                    $total_valor_devengo += $empelado_orden_trabajo->valor_devengo;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td style="text-align: right;"> {{ number_format($total_cantidad_horas, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> $ {{ number_format($total_valor_devengo, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>
</div>