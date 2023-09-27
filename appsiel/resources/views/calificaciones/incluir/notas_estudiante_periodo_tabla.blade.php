<?php
    $reg_nota_reprobar = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->first();
    $nota_reprobar = 0;
    if ( !is_null($reg_nota_reprobar) )
    {
        $nota_reprobar = $reg_nota_reprobar->calificacion_maxima;
    }
    
    $nivelado = false;
    $total_pesos = 0;
?>

<input type="hidden" name="fecha_termina_periodo" id="fecha_termina_periodo" value="{{ $periodo->fecha_hasta }}">

<h3> Calificaciones </h3>
<p> Para ver detalles, ubique el cursor sobre la calificación. </p>

<br>

@if( $observacion_boletin->puesto != '' )
    <div>
        <code> Puesto: {{ $observacion_boletin->puesto }} </code>    
    </div>
@endif

<div class="table-responsive" id="table_content">

    <table id="tbDatos" class="table table-striped tabla_registros" style="margin-top: -4px;">
        <thead>
            <tr style="font-size: 16px;">
                <th>Asignatura</th>
                @foreach($lbl_calificaciones_aux as $lbl_calificacion_aux)
                    <th style="width:35px;"> 
                        {{$lbl_calificacion_aux->label}}
                        @if($lbl_calificacion_aux->peso != '')
                            <br> 
                            <span style="font-size: 0.6em;">{{$lbl_calificacion_aux->peso}}</span>
                            <?php
                                $total_pesos += (float)$lbl_calificacion_aux->peso;
                            ?>
                        @endif
                    </th>
                @endforeach
                <th style="width:60px;">Final</th>
                <th>Desempeño</th>
                <th>Logros</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $cant = count($registros);
            ?>
            @for($i=0; $i < $cant ; $i++) 
                <tr class="fila-{{$j}}" >
                    <td style="font-size: 16px;">{{$registros[$i]->asignatura}}</td>
                    <?php
                        $promedio = 0;
                        $n = 0;
                        foreach($lbl_calificaciones_aux as $lbl_calificacion_aux)
                        {                            
                            $c = $lbl_calificacion_aux->label;
                            $texto_calificacion = '';
                            
                            if ( $registros[$i]->$c != 0) 
                            {
                                $texto_calificacion = number_format($registros[$i]->$c, 2, '.', ',');
                                $n++;
                            }

                            $style="color: #000000;";
                            if( $texto_calificacion <= $nota_reprobar )
                            {
                                $style="color: #f00;";
                            }
                            
                            
                            $fecha_calificacion = '';
                            $detalle_calificacion = 'Sin detalle.';

                            $encabezado_calificacion = App\Calificaciones\EncabezadoCalificacion::where( [ 'columna_calificacion' => $c, 'periodo_id' => $periodo->id, 'curso_id' => $curso->id, 'asignatura_id' => $registros[$i]->asignatura_id] )->get()->first();

                            if ( $encabezado_calificacion != null )
                            {
                                $fecha_calificacion = 'Fecha: '.$encabezado_calificacion->fecha;
                                $detalle_calificacion = ',    Detalle de la actividad: '.$encabezado_calificacion->descripcion;
                            }
                    ?>
                            <td>
                                <button style="{{$style}}" type="button" class="btn btn-secondary" data-toggle="tooltip" data-html="true" data-placement="top" title="{{$fecha_calificacion.$detalle_calificacion}}"> {{$texto_calificacion}} </button >
                            </td>
                    <?php
                            if ($total_pesos > 0) {
                                $promedio += (float)$registros[$i]->$c  * (float)$lbl_calificacion_aux->peso / 100;
                            }else{
                                $promedio += (float)$registros[$i]->$c;
                            }
                            
                            $n++;
                        } // Fin cada calificacion aux.

                            $prom = $promedio;

                            if ($total_pesos <= 0 && $n != 0) {
                                $prom = $promedio / $n;
                            }

                            // La calificación de nivelación reemplaza la nota promedio final.
                            $cali_nivelacion_periodo = $periodo->get_calificacion_nivelacion( $curso->id, $estudiante->id, $registros[$i]->asignatura_id );
                            
                            if( $cali_nivelacion_periodo != null )
                            {
                                $prom = $cali_nivelacion_periodo->calificacion;
                                $nivelado = true;
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

                            if ( $escala != null ) 
                            {
                                $desempeno = $escala->nombre_escala;
                                $logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('periodo_id',$periodo->id)->where('curso_id',$curso->id)->where('asignatura_id', $registros[$i]->asignatura_id)->where('estado','Activo')->get();

                                $n_nom_logros = count($logros);
                            }
                            $style2="color: #000000;";
                            if($prom<=$nota_reprobar){
                                $style2="color: #f00;";
                            }
                    ?>
                            <th style="font-size: 16px; {{$style2}}">
                                {{number_format($prom, 2, '.', ',')}}
                                @if($nivelado)
                                 <sup>n</sup>
                                @endif
                            </th>
                            <td> {{$desempeno}} </td>
                            <td>
                               <ul>
                                    @foreach($logros as $un_logro)
                                        @if( $un_logro != '' )
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

@if($nivelado)
    <p>
        <sup>n</sup>: nivelación
    </p>

    <br>
@endif

<?php

    $escalas = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->get();

    $tbody = '<table style="border: 1px solid; border_collapsed: collapsed; width:170px; font-size: 3.5mm;">
                <tr>
                    <td colspan="3" style="text-align:center;border: 1px solid; padding: 10px;">Escala de valoración
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;border: 1px solid; padding: 10px;">Desempeño</td>
                    <td style="text-align:center;border: 1px solid; padding: 10px;">Calificación Mínima</td>
                    <td style="text-align:center;border: 1px solid; padding: 10px;">Calificación Máxima</td>
                </tr>';
    foreach($escalas as $linea)
    {
        $tbody.='<tr>
                    <td style="border: 1px solid; padding: 10px;">'.$linea->nombre_escala.'</td>
                    <td style="text-align:center;border: 1px solid; padding: 10px;">'.number_format($linea->calificacion_minima,2,'.',',').'</td>
                    <td style="text-align:center;border: 1px solid; padding: 10px;">'.number_format($linea->calificacion_maxima,2,'.',',').'</td>
                </tr>';
    }

    $tbody.='</table>';
    echo $tbody;
?>