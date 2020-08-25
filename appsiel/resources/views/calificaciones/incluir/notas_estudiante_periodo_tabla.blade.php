<?php 
    $periodo = App\Calificaciones\Periodo::find($periodo_id);
?>

<input type="hidden" name="fecha_termina_periodo" id="fecha_termina_periodo" value="{{ $periodo->fecha_hasta }}">

<h3> Calificaciones </h3>
<h4> Pase el mouse por encima de la nota para ver el detalle de la actividad calificada. </h4>

@if( $observacion_boletin->puesto != '' )
    <div>
        <code> Puesto: {{ $observacion_boletin->puesto }} </code>    
    </div>
@endif

<div class="table-responsive" id="table_content">

    <table class="table table-striped tabla_registros" style="margin-top: -4px;">
        <thead>
            <tr>
                <th>
                   Asignatura
                </th>
                @for($k=1; $k < 16; $k++)
                    <th>C{{$k}}</th>
                @endfor
                <th> Final </th>
                <th> Desempeño </th>
                <th> Logros </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $cant = count($registros);
            ?>
            @for($i=0; $i < $cant ; $i++) 
                <tr class="fila-{{$j}}" >
                    <td>{{$registros[$i]->asignatura}}</td>
                    <?php
                        $promedio = 0;
                        $n = 0;
                        for ($k=1; $k < 16; $k++) { 
                            
                            $c = 'C'.$k;
                            $texto_calificacion = '';
                            
                            if ( $registros[$i]->$c != 0) 
                            {
                                $texto_calificacion = $registros[$i]->$c;
                                $n++;
                            }
                            
                            $fecha_calificacion = '';
                            $detalle_calificacion = 'Sin detalle.';

                            $encabezado_calificacion = App\Calificaciones\EncabezadoCalificacion::where( [ 'columna_calificacion' => $c, 'periodo_id' => $periodo_id, 'curso_id' => $curso_id, 'asignatura_id' => $registros[$i]->asignatura_id] )->get()->first();

                            if ( !is_null($encabezado_calificacion) )
                            {
                                $fecha_calificacion = 'Fecha: '.$encabezado_calificacion->fecha;
                                $detalle_calificacion = ',    Detalle de la actividad: '.$encabezado_calificacion->descripcion;
                            }
                    ?>
                            <td>
                                <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-html="true" data-placement="top" title="{{$fecha_calificacion.$detalle_calificacion}}"> {{$texto_calificacion}} </button >
                            </td>
                    <?php
                            $promedio+=(float)$registros[$i]->$c;
                        }
                            $prom = 0;
                            if ( $n != 0) {
                                $prom = $promedio/$n;
                            }

                            $escala = (object) array('id' => 0, 'nombre_escala' => '');

                            if ( $prom > 0 ) 
                            {
                                // Si la calificacion $prom no está en alguna escala de valoración, entonces $escala = null
                                $escala = App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion( $prom, $periodo->periodo_lectivo_id );
                            }
                        
                            $desempeno = '';
                            $logros = (object) array('descripcion' => '');
                            $n_nom_logros = 0;

                            if ( !is_null($escala) ) 
                            {
                                $desempeno = $escala->nombre_escala;
                                $logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('periodo_id',$periodo_id)->where('curso_id',$curso_id)->where('asignatura_id',$registros[$i]->asignatura_id)->where('estado','Activo')->get();

                                $n_nom_logros = count($logros);
                            }
                    ?>
                            <td> {{number_format($prom, 2, ',', '.')}} </td>
                            <td> {{$desempeno}} </td>
                            <td>
                               <ul>
                                    @foreach($logros as $un_logro)
                                        @if( !is_null( $un_logro ) )
                                            <li>{{ $un_logro->descripcion }}</li>
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
            @endfor
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
