<?php 
    $periodo = App\Calificaciones\Periodo::find($periodo->id);

    $reg_nota_reprobar = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->first();
    $nota_reprobar = 0;
    if ( !is_null($reg_nota_reprobar) )
    {
        $nota_reprobar = $reg_nota_reprobar->calificacion_maxima;
    }    
    
    function get_array_pesos($linea_asignatura)
    {
        return [ 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
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
        $cantidad_calificaciones = 16;
    ?>

    <table id="tbDatos" class="table table-striped tabla_registros" style="margin-top: -4px;">
        @include('calificaciones.incluir.encabezados_fijos.lineal.encabezados_tabla',['arr_labels_adicionales'=> ['Final','Desempeño','Logros']])
        <tbody>
            <?php
                $j = 1;
                $cant = count($registros);
            ?>
            @for($i=0; $i < $cant ; $i++) 
                <tr class="fila-{{$j}}" >
                    <td style="font-size: 16px;">{{$registros[$i]->asignatura}}</td>
                    <?php

                        $arr_pesos = get_array_pesos($registros[$i]->getAttributes());

                        $sumatoria_calificaciones = 0;
                        $mostrar_promedio = true;
                        $num_calificaciones = 0;
                        for ($k=1; $k < $cantidad_calificaciones; $k++) {                            

                            $c = 'C'.$k;
                            $fecha_calificacion = '';
                            $detalle_calificacion = 'Sin detalle.';
                            $texto_calificacion = '';
                            $style="color: #000000;";

                            $peso = $arr_pesos[$k];
                            $lbl_peso = '';
                            
                            $registro_calificacion = $registros[$i]->$c;

                            $sumatoria_calificaciones += (float)$registro_calificacion;

                            $texto_calificacion = number_format( $registro_calificacion, 2, '.', ',');
                            
                            $mostrar_promedio = false;

                            if( $texto_calificacion <= $nota_reprobar && $texto_calificacion != 0 )
                            {
                                $style="color: #f00;";
                            }

                            if( $texto_calificacion == 0 )
                            {
                                $texto_calificacion = '-';
                            }
                    ?>
                            <td style="text-align: center;">
                                <span style="{{$style}}"> {{ $texto_calificacion }} </span >
                            </td>
                    <?php

                            if ((float)$registro_calificacion != 0) {
                                $num_calificaciones++;
                            }
                            
                        } // Fin por cada Encabezado de calificaciones

                            $prom = $sumatoria_calificaciones;                            
                            
                            if( $num_calificaciones > 0){
                                $prom = $prom / $num_calificaciones;
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
                            if( $prom <= $nota_reprobar){
                                $style2="color: #f00;";
                            }


                    ?>
                            <th style="font-size: 16px; {{$style2}}"> 
                                {{number_format($prom, 2, '.', ',')}} 
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