<div>

    IF = Inventario Físico <a class="btn btn-success btn-xs" id="btn_excel_v2" title="inventario_fisico"><i class="fa fa-file-excel-o"></i></a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="tbDatos">
            {{ Form::bsTableHeader(['Cód.','Producto','Cant. IF','Costo Unit. IF','Costo Tot. IF','Cant. sistema','Costo Tot. sistema','DIF. Cant.','DIF. Costo Tot.','']) }}
            <tbody>
                <?php 
                
                $total_cantidad = 0;
                $total_documento = 0;
                $total_cantidad_sistema = 0;
                $total_documento_sistema = 0;
                $total_cantidad_dif = 0;
                $total_documento_dif = 0;
                ?>
                @foreach($doc_registros as $linea )
                    <?php

                        $descripcion_item = $linea->item->get_value_to_show(true);

                        $diferencia = round( $linea->cantidad - $linea->cantidad_sistema , 2);

                        $diferencia_costo = $linea->costo_total - $linea->costo_total_sistema;

                        if ( $diferencia > 0 )
                        {
                            $resultado = '<span style="color:green;">Sobrante</span>';
                        }else{
                            $resultado = '<span style="color:red;">Faltante</span>';
                        }

                        if (  (-1 < $diferencia) && ($diferencia < 1 ) )
                        {
                            $resultado = '<span style="color:black;"><i class="fa fa-check"></i></span>';
                            $diferencia_costo = 0;
                        }
                    ?>
                    <tr>
                        <td class="text-center"> {{ $linea->producto_id }} </td>
                        <td> {{ $descripcion_item }} </td>

                        <!-- Datos del conteo físico -->
                        <td class="text-center"> {{ number_format( $linea->cantidad, 2, ',', '.') }}  </td>
                        <?php 
                            $costo_unit_conteo = 0;
                            if ($linea->cantidad != 0) {
                                $costo_unit_conteo = $linea->costo_total / $linea->cantidad;
                            }
                        ?>
                        <td class="text-right"> ${{ number_format( $costo_unit_conteo, 0, ',', '.') }} </td>
                        <td class="text-right"> ${{ number_format( $linea->costo_total, 0, ',', '.') }} </td>

                        <!-- Datos del sistema -->
                        <td class="text-center"> {{ number_format( $linea->cantidad_sistema, 2, ',', '.') }} </td>
                        <td class="text-right"> ${{ number_format( $linea->costo_total_sistema, 0, ',', '.') }} </td>

                        <!-- Datos de la diferencia -->
                        <td class="text-center"> {{ number_format( $diferencia, 2, ',', '.') }} </td>
                        <td class="text-right"> ${{ number_format( $diferencia_costo, 0, ',', '.') }} </td>

                        <td> 
                            <?php
                                echo $resultado;
                            ?>
                        </td>
                    </tr>
                    <?php 
                        $total_cantidad += $linea->cantidad;
                        $total_documento += $linea->costo_total;
                        $total_cantidad_sistema += $linea->cantidad_sistema;
                        $total_documento_sistema += $linea->costo_total_sistema;
                        $total_cantidad_dif += $diferencia;
                        $total_documento_dif += $diferencia_costo;
                    ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                            <?php 
                                if ( $total_cantidad_dif > 0 )
                                {
                                    $resultado = '<span style="color:green;">Sobrante</span>';
                                }else{
                                    $resultado = '<span style="color:red;">Faltante</span>';
                                }

                                if (  (-1 < $total_cantidad_dif) &&  ($total_cantidad_dif < 1) )
                                {
                                    $resultado = '<span style="color:black;"><i class="fa fa-check"></i></span>';
                                    $total_documento_dif = 0;
                                }
                            ?>

                    <td colspan="2">&nbsp;</td>
                    <td class="text-center"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                    <td class="text-right"> &nbsp; </td>
                    <td class="text-right"> {{ '$ '.number_format($total_documento, 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format($total_cantidad_sistema, 2, ',', '.') }} </td>
                    <td class="text-right"> {{ '$ '.number_format($total_documento_sistema, 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format($total_cantidad_dif, 2, ',', '.') }} </td>
                    <td class="text-right"> {{ '$ '.number_format($total_documento_dif, 0, ',', '.') }} </td>
                        <td> 
                            <?php

                                echo $resultado;
                            ?>
                        </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>