<style> 
    .celda_firma { 
        width: 100px;
    }

    .celda_nombre_empleado { 
        width: 150px; 
    } 
    
    .table.sticky th {
        position: sticky; top: 0;
    }
</style>
<br>
<div class="table-responsive">
    <table  class="tabla_registros table table-bordered table-striped sticky contenido" style="margin-top: 1px; width: 100%;">
        <thead>
            <tr class="">
                <th> NO. </th>
                <th> EMPLEADO </th>
                <th> IDENTIFCACIÓN </th>
                @foreach ($conceptos as $concepto)
                    <th> {{$concepto->abreviatura}} </th>
                @endforeach
                <th> T. DEVENGOS </th>
                <th>T. DEDUCCIONES</th>
                <th>TOTAL A PAGAR</th>
                <th width="100px">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $total_1=0;
                $i=1;

                $vec_totales = array_fill(0, count($conceptos)+3, 0);  
            ?>
            @foreach( $empleados as $empleado )
                <?php
                    $total_devengos_empleado = 0;
                    $total_deducciones_empleado = 0;
                ?>
                <tr>
                    <td class="text-center"> {{ $i }} </td>
                    <td class="text-left celda_nombre_empleado"> {{ $empleado->tercero->descripcion }} </td>
                    <td class="text-center"> {{ $empleado->tercero->numero_identificacion }} </td>
                    <?php 
                        $pos = 0;
                    ?>
                    @foreach( $conceptos as $concepto )
                        <?php
                            $total_dev_ded_empleado = $concepto->get_total_dev_ded_empleado_registros_documento( $encabezado_doc_id, $empleado->id );

                            $valor = $total_dev_ded_empleado->sum_devengos + $total_dev_ded_empleado->sum_deducciones;
                            //dd( $total_dev_ded_empleado, $concepto->modo_liquidacion );
                        ?>
                        <td title="{{$concepto->descripcion}}"> 
                            {{ Form::TextoMoneda( $valor ) }}
                        </td>
                        <?php
                            $total_devengos_empleado += $total_dev_ded_empleado->sum_devengos;

                            $total_deducciones_empleado += $total_dev_ded_empleado->sum_deducciones;

                            // Acumular totales del concepto
                            $vec_totales[$pos] += $total_dev_ded_empleado->sum_devengos + $total_dev_ded_empleado->sum_deducciones;

                            $pos++;
                        ?>
                    @endforeach
                        <?php

                        ?>
                    <td title="Total devengos">
                        {{ Form::TextoMoneda( $total_devengos_empleado ) }}
                    </td>

                    <td title="Total deducciones">
                        {{ Form::TextoMoneda( $total_deducciones_empleado ) }}
                    </td>

                    <td title="Total a pagar">
                        {{ Form::TextoMoneda( $total_devengos_empleado - $total_deducciones_empleado ) }}
                    </td>

                    <td class="celda_firma"> &nbsp; </td>

                    <?php
                        $vec_totales[$pos] += $total_devengos_empleado;
                        $pos++;
                        $vec_totales[$pos] += $total_deducciones_empleado;
                        $pos++;
                        $vec_totales[$pos] += $total_devengos_empleado - $total_deducciones_empleado;
                    ?>
                </tr>
                <?php
                    $i++;
                ?>
            @endforeach
            <tr>
                <td colspan="3">&nbsp;</td>
                <?php
                    $cant = count( $vec_totales );
                ?>
                @for($j=0; $j < $cant; $j++)
                    <td>
                        {{ Form::TextoMoneda( $vec_totales[$j] ) }}
                    </td>
                @endfor
                <td> &nbsp; </td>
            </tr>
        </tbody>
    </table>
</div>