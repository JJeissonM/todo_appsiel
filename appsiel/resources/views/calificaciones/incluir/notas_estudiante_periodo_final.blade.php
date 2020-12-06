<!-- Promedio de todos los periodos -->
<input type="hidden" name="fecha_termina_periodo" id="fecha_termina_periodo" value="{{ $periodo->fecha_hasta }}">

<h3> Calificaciones periodo FINAL</h3>
<h4> Las notas son calculadas con base en la nota final de los demás periodos. </h4>

@if( $observacion_boletin->puesto != '' )
    <div>
        <code> Puesto: {{ $observacion_boletin->puesto }} </code>    
    </div>
@endif

<div class="table-responsive" id="table_content">

    <table class="table table-striped tabla_registros" style="margin-top: -4px;">
        <thead>
            <tr>
                <th> </th>
                <th colspan="{{ count($periodos_del_anio_lectivo) }}"> Periodos </th>
                <th> </th>
                <th> </th>
            </tr>
            <tr>
                <th>
                   Asignatura
                </th>
                @foreach( $periodos_del_anio_lectivo as $un_periodo )
                    <th>{{ $un_periodo->descripcion }}</th>
                @endforeach
                <th> Desempeño </th>
                <th> Logros </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
            ?>
            @foreach( $registros as $fila ) 
                <?php
                    //dd( $fila->periodos );
                ?>
                <tr class="fila-{{$j}}" >
                    <td>{{ $fila->asignatura->descripcion }}</td>
                    @foreach( $fila->periodos as $un_periodo )
                        <td> {{ number_format( $un_periodo->calificacion, 2, ',', '.') }} </td>
                    @endforeach                    
                    <td> {{ $fila->escala_valoracion_periodo_final }} </td>
                    <td>
                       <ul>
                            @foreach($fila->logros as $logro)
                                @if( $logro != '' )
                                    <li>{{ $logro->descripcion }}</li>
                                @else
                                    <li> </li>
                                @endif
                            @endforeach
                        </ul>
                    </td>
                </tr>
                <?php
                    $j++;
                    if ($j==3) {
                        $j=1;
                    }
                ?>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-md-12">
        <label class="form-label">Observaciones del periodo</label>
        <div class="well"> {{ $observacion_boletin->observacion }} </div>
    </div>
</div>

<br>
<?php

    $escala = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->get();

    $tbody = '<table style="border: 1px solid; border_collapsed: collapsed; width:170px; font-size: 3.5mm;">
                <tr>
                    <td colspan="3" style="text-align:center;border: 1px solid;">Escala de valoración
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;border: 1px solid;">Desempeño</td>
                    <td style="text-align:center;border: 1px solid;">Mín.</td>
                    <td style="text-align:center;border: 1px solid;">Máx.</td>
                </tr>';
    foreach($escala as $linea)
    {
        $tbody.='<tr>
                    <td style="border: 1px solid;">'.$linea->nombre_escala.'</td>
                    <td style="text-align:center;border: 1px solid;">'.$linea->calificacion_minima.'</td>
                    <td style="text-align:center;border: 1px solid;">'.$linea->calificacion_maxima.'</td>
                </tr>';
    }

    $tbody.='</table>';
    echo $tbody;
?>
