<style>
@if( isset($es_impresion) && $es_impresion )
    <?php $tamano_letra_tabla = $tamano_letra_tabla ?? 10; ?>
    .tabla_registros_impresion {
        border-collapse: collapse;
        border-spacing: 0;
        margin-top: 0 !important;
        table-layout: fixed;
        width: 100%;
    }

    .tabla_registros_impresion th,
    .tabla_registros_impresion td {
        border: 1px solid #222;
        font-size: {{ $tamano_letra_tabla }}px !important;
        line-height: 1.05 !important;
        padding: 2px 2px !important;
        vertical-align: middle;
    }

    .tabla_registros_impresion th {
        font-weight: bold;
        text-align: center;
    }

    .tabla_registros_impresion .col-numero {
        text-align: center;
        width: 20px;
    }

    .tabla_registros_impresion .col-empleado {
        width: 120px;
    }

    .tabla_registros_impresion .col-cc {
        text-align: center;
        width: 62px;
    }

    .tabla_registros_impresion .col-firma {
        width: 70px;
    }

    .tabla_registros_impresion .valor {
        text-align: right;
    }

    .tabla_registros_impresion .cpto-deduccion {
        color: #c0392b;
        font-weight: 600;
    }
@else
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
@endif
</style>
@if( !isset($es_impresion) || !$es_impresion )
<br>
@endif
@if( !isset($es_impresion) || !$es_impresion )
<div class="row" style="margin-bottom:10px;">
    <div class="col-md-4">
        <input type="text" id="buscar_registros_liquidacion" class="form-control" placeholder="Buscar...">
    </div>
</div>
@endif
<div class="{{ isset($es_impresion) && $es_impresion ? '' : 'table-responsive' }}">
    <table id="tabla_registros_documento" @if(!isset($es_impresion) || !$es_impresion) data-conceptos-count="{{ count($conceptos) }}" @endif class="{{ isset($es_impresion) && $es_impresion ? 'tabla_registros_impresion' : 'tabla_registros table table-bordered table-striped sticky contenido' }}" style="margin-top: 1px; width: 100%;">
        <thead>
            <tr class="">
                <th class="{{ isset($es_impresion) && $es_impresion ? 'col-numero' : 'sticky-col-1' }}"> No. </th>
                <th class="{{ isset($es_impresion) && $es_impresion ? 'col-empleado' : 'sticky-col-2' }}"> EMPLEADO </th>
                <th class="{{ isset($es_impresion) && $es_impresion ? 'col-cc' : '' }}"> C.C. </th>
                @foreach ($conceptos as $concepto)
                    <th class="{{ $concepto->naturaleza == 'deduccion' ? 'cpto-deduccion' : '' }}"> {{$concepto->abreviatura}} </th>
                @endforeach
                <th> T. DEVEN. </th>
                <th>T. DEDUCC.</th>
                <th>T. A PAGAR</th>
                <th class="{{ isset($es_impresion) && $es_impresion ? 'col-firma' : '' }}" width="100px">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $i=1;
                $vec_totales = array_fill(0, count($conceptos)+3, 0);  
                $total_dev_doc = 0;
                $total_ded_doc = 0;
                $usar_totales_precalculados = !empty($totales_por_empleado_concepto);
                $es_tabla_impresion = isset($es_impresion) && $es_impresion;
                $formato_moneda = function($valor) use ($es_tabla_impresion) {
                    if ($es_tabla_impresion) {
                        return '$ '.number_format($valor, 0, ',', '.');
                    }

                    return Form::TextoMoneda($valor);
                };
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
                <tr @if(!isset($es_impresion) || !$es_impresion) data-contrato-id="{{ $empleado->id }}" data-search="{{ strtolower($empleado->tercero->descripcion.' '.$empleado->tercero->numero_identificacion) }}" @endif>
                    <td class="{{ isset($es_impresion) && $es_impresion ? 'col-numero' : 'text-center sticky-col-1' }}"> {{ $i }} </td>
                    <td class="{{ isset($es_impresion) && $es_impresion ? 'col-empleado' : 'text-left celda_nombre_empleado sticky-col-2' }}"> {{ $empleado->tercero->descripcion }} </td>
                    <td class="{{ isset($es_impresion) && $es_impresion ? 'col-cc' : 'text-center' }}"> {{ $empleado->tercero->numero_identificacion }} </td>
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
                        <td class="{{ (isset($es_impresion) && $es_impresion ? 'valor ' : '') . ($concepto->naturaleza == 'deduccion' ? 'cpto-deduccion' : '') }}" @if(!isset($es_impresion) || !$es_impresion) title="{{$concepto->descripcion}}" @endif> 
                            {!! $formato_moneda( $valor ) !!}
                        </td>
                        <?php
                            // Acumular totales del concepto
                            $vec_totales[$pos] += $dev + $ded;

                            $pos++;
                        ?>
                    @endforeach
                        <?php

                        ?>
                    <td class="{{ isset($es_impresion) && $es_impresion ? 'valor' : '' }}" @if(!isset($es_impresion) || !$es_impresion) title="Total devengos" @endif>
                        {!! $formato_moneda( $total_devengos_empleado ) !!}
                    </td>

                    <td class="{{ isset($es_impresion) && $es_impresion ? 'valor' : '' }}" @if(!isset($es_impresion) || !$es_impresion) title="Total deducciones" @endif>
                        {!! $formato_moneda( $total_deducciones_empleado ) !!}
                    </td>

                    <td class="{{ isset($es_impresion) && $es_impresion ? 'valor' : '' }}" @if(!isset($es_impresion) || !$es_impresion) title="Total a pagar" @endif>
                        {!! $formato_moneda( $total_devengos_empleado - $total_deducciones_empleado ) !!}
                    </td>

                    <td class="{{ isset($es_impresion) && $es_impresion ? 'col-firma' : 'celda_firma' }}"> &nbsp; </td>

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
                    <td class="{{ isset($es_impresion) && $es_impresion ? 'valor' : '' }}">
                        {!! $formato_moneda( $vec_totales[$j] ) !!}
                    </td>
                @endfor
                <td> &nbsp; </td>
            </tr>
        </tbody>
    </table>
</div>

