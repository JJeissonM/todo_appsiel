<?php 
    $periodo = App\Calificaciones\Periodo::find($periodo->id);

    $reg_nota_reprobar = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->first();
    $nota_reprobar = 0;
    if ( !is_null($reg_nota_reprobar) )
    {
        $nota_reprobar = $reg_nota_reprobar->calificacion_maxima;
    }        
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

    <?php
        $limite=16;
        if(count($registros)>0){
            $ultimoTotal=0;
            foreach($registros as $r){
                $total=1;
                for($m=1; $m<16; $m++){
                    $label="C".$m;
                    if($r->$label!=0){
                        $total=$total+1;
                    }
                }
                if($total>$ultimoTotal){
                    $ultimoTotal=$total;
                }
            }
            $limite=$ultimoTotal;
        }

    ?>

    <table id="tbDatos" class="table table-striped tabla_registros" style="margin-top: -4px;">
        <thead>
            <tr style="font-size: 16px;">
                <th>Asignatura</th>
                @for($k=1; $k < $limite; $k++)
                <th>C{{$k}}</th>
                @endfor
                <th>Final</th>
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
                        $sumatoria_calificaciones = 0;
                        $sumatoria_pesos = 0;
                        $mostrar_promedio = true;
                        $n = 0;
                        for ($k=1; $k < $limite; $k++) {                            

                            $c = 'C'.$k;
                            $fecha_calificacion = '';
                            $detalle_calificacion = 'Sin detalle.';
                            $texto_calificacion = '';
                            $style="color: #000000;";

                            $encabezado_calificacion = App\Calificaciones\EncabezadoCalificacion::where( [ 'columna_calificacion' => $c, 'periodo_id' => $periodo->id, 'curso_id' => $curso->id, 'asignatura_id' => $registros[$i]->asignatura_id] )->get()->first();
                            $peso = 0;
                            $lbl_peso = '';
                            if ( $encabezado_calificacion != null )
                            {
                                $fecha_calificacion = 'Fecha: '.$encabezado_calificacion->fecha;
                                $detalle_calificacion = ',    Detalle de la actividad: '.$encabezado_calificacion->descripcion;
                                $peso = (int)$encabezado_calificacion->peso;
                            }
                            
                            $registro_calificacion = $registros[$i]->$c;

                            if ( $registro_calificacion != 0) 
                            {
                                if ( $peso == 0 ) {
                                    $texto_calificacion = number_format($registro_calificacion,2,'.',',');
                                }else{
                                    
                                    $registro_calificacion = $registro_calificacion * $peso / 100;

                                    $texto_calificacion = number_format( $registro_calificacion, 2, '.', ',');
                                    $lbl_peso = '<br><span style="font-size: 0.8em;"> x ' . $peso . '% = ' . $texto_calificacion . '</span>';
                                    $mostrar_promedio = false;
                                }
                                
                                $n++;
                            }

                            if( $registros[$i]->$c <= $nota_reprobar )
                            {
                                $style="color: #f00;";
                            }
                            
                    ?>
                            <td>
                                @if( (int)$registros[$i]->$c == 0 )
                                    --
                                @else
                                    <button style="{{$style}}" type="button" class="btn btn-secondary" data-toggle="tooltip" data-html="true" data-placement="top" title="{{$fecha_calificacion.$detalle_calificacion}}"> {{ $registros[$i]->$c }} </button >
                                    {!! $lbl_peso !!}
                                @endif
                                
                            </td>
                    <?php
                            $sumatoria_calificaciones += (float)$registro_calificacion;
                            $sumatoria_pesos += $peso;
                        }
                            $prom = $sumatoria_calificaciones;
                            if ( $n != 0 && $mostrar_promedio ) {
                                $prom = $sumatoria_calificaciones / $n;
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
                                $logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('periodo_id',$periodo->id)->where('curso_id',$curso->id)->where('asignatura_id',$registros[$i]->asignatura_id)->where('estado','Activo')->get();

                                $n_nom_logros = count($logros);
                            }
                            $style2="color: #000000;";
                            if($prom<=$nota_reprobar){
                                $style2="color: #f00;";
                            }
                    ?>
                            <td style="font-size: 16px; {{$style2}}"> 
                                {{number_format($prom, 2, '.', ',')}}
                                @if($sumatoria_pesos < 100)
                                    <br><span style="font-size: 0.8em; color: orange;"> <span class="fa fa-warning"></span> {{ 100 - $sumatoria_pesos }}% pendiente <br> por calificar</span>
                                @endif
                            </td>
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