<style> 
    .celda_firma { 
        width: 100px;
    }

    .celda_nombre_empleado { 
        width: 150px; 
    } 

    .cpto-deduccion {
        color: #c0392b;
        font-weight: 600;
    }

    .fila-oculta {
        display: none;
    }
    
.table.sticky th {
    position: sticky; top: 0;
}

.sticky-col-1,
.sticky-col-2 {
    position: sticky !important;
    background: #ffffff;
    z-index: 3;
}

    .sticky-col-1 {
        left: 0;
        width: 20px;
        min-width: 20px;
        max-width: 20px;
        text-align: center;
    }

    .sticky-col-2 {
        left: 20px;
        width: 110px;
        min-width: 110px;
        max-width: 110px;
    }

.table.sticky th.sticky-col-1,
.table.sticky th.sticky-col-2 {
    z-index: 4;
    background: #ffffff;
}

.tabla_registros {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
}

.table-responsive {
    position: relative;
    overflow-x: auto;
}
@if( isset($es_impresion) && $es_impresion )
    .tabla_registros th, .tabla_registros td {
        font-size: 10px !important;
        padding: 4px 2px !important;
    }

    .texto_moneda td {
        font-size: 11px !important;
    }
@endif
</style>
<br>
@if( !isset($es_impresion) || !$es_impresion )
<div class="row" style="margin-bottom:10px;">
    <div class="col-md-4">
        <input type="text" id="buscar_registros_liquidacion" class="form-control" placeholder="Buscar...">
    </div>
</div>
@endif
<div class="table-responsive">
    <table id="tabla_registros_documento" data-conceptos-count="{{ count($conceptos) }}" class="tabla_registros table table-bordered table-striped sticky contenido" style="margin-top: 1px; width: 100%;">
        <thead>
            <tr class="">
                <th class="sticky-col-1"> No. </th>
                <th class="sticky-col-2"> EMPLEADO </th>
                <th> C.C. </th>
                @foreach ($conceptos as $concepto)
                    <th class="{{ $concepto->naturaleza == 'deduccion' ? 'cpto-deduccion' : '' }}"> {{$concepto->abreviatura}} </th>
                @endforeach
                <th> T. DEVEN. </th>
                <th>T. DEDUCC.</th>
                <th>T. A PAGAR</th>
                <th width="100px">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $i=1;
                $vec_totales = array_fill(0, count($conceptos)+3, 0);  
                $total_dev_doc = 0;
                $total_ded_doc = 0;
                $usar_totales_precalculados = !empty($totales_por_empleado_concepto);
            ?>
            @foreach( $empleados as $empleado )
                <?php
                    if ($usar_totales_precalculados) {
                        $total_devengos_empleado = $totales_por_empleado[$empleado->id]['dev'] ?? 0;
                        $total_deducciones_empleado = $totales_por_empleado[$empleado->id]['ded'] ?? 0;
                    } else {
                        $total_devengos_empleado = 0;
                        $total_deducciones_empleado = 0;
                    }
                ?>
                <tr data-contrato-id="{{ $empleado->id }}" data-search="{{ strtolower($empleado->tercero->descripcion.' '.$empleado->tercero->numero_identificacion) }}">
                    <td class="text-center sticky-col-1"> {{ $i }} </td>
                    <td class="text-left celda_nombre_empleado sticky-col-2"> {{ $empleado->tercero->descripcion }} </td>
                    <td class="text-center"> {{ $empleado->tercero->numero_identificacion }} </td>
                    <?php 
                        $pos = 0;
                    ?>
                    @foreach( $conceptos as $concepto )
                        <?php
                            if ($usar_totales_precalculados) {
                                $total_dev_ded_empleado = $totales_por_empleado_concepto[$empleado->id][$concepto->id] ?? ['dev' => 0, 'ded' => 0];
                                $dev = $total_dev_ded_empleado['dev'];
                                $ded = $total_dev_ded_empleado['ded'];
                            } else {
                                $total_dev_ded_empleado = $concepto->get_total_dev_ded_empleado_registros_documento( $encabezado_doc_id, $empleado->id );
                                $dev = $total_dev_ded_empleado->sum_devengos;
                                $ded = $total_dev_ded_empleado->sum_deducciones;
                                $total_devengos_empleado += $dev;
                                $total_deducciones_empleado += $ded;
                            }

                            $valor = $dev + $ded;
                            //dd( $total_dev_ded_empleado, $concepto->modo_liquidacion );
                        ?>
                        <td class="{{ $concepto->naturaleza == 'deduccion' ? 'cpto-deduccion' : '' }}" title="{{$concepto->descripcion}}"> 
                            {{ Form::TextoMoneda( $valor ) }}
                        </td>
                        <?php
                            // Acumular totales del concepto
                            $vec_totales[$pos] += $dev + $ded;

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

                        $total_dev_doc += $total_devengos_empleado;
                        $total_ded_doc += $total_deducciones_empleado;
                    ?>
                </tr>
                <?php
                    $i++;
                ?>
            @endforeach
            <tr class="fila-totales">
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







