<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Empleado','Concepto','Cant. horas','Vlr. unitario','Vlr. total']) }}
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
                    <td align="center">
                        <div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('nom_ordenes_trabajo_cambiar_cantidad_horas_empleados') . "/" . $orden_de_trabajo->id . "/" . $orden_de_trabajo->nom_concepto_id . "/" . $empelado_orden_trabajo->contrato->id }}"> {{ $empelado_orden_trabajo->cantidad_horas }} 
                        </div>
                    </td>
                    <td class="text-right">
                        $ <div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('nom_ordenes_trabajo_cambiar_valor_por_hora_empleados') . "/" . $orden_de_trabajo->id . "/" . $orden_de_trabajo->nom_concepto_id . "/" . $empelado_orden_trabajo->contrato->id }}"> {{ $empelado_orden_trabajo->valor_por_hora }} 
                        </div>
                    </td>
                    <td style="text-align: right;"> $ {{ number_format( $empelado_orden_trabajo->valor_devengo, 2, ',', '.') }} </td>
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
                <td align="center"> {{ number_format($total_cantidad_horas, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> $ {{ number_format($total_valor_devengo, 2, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>