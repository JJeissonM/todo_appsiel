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
        $grupo_pesos = ['C1', 'C2', 'C3', 'C4'];
        $pesos_columnas_sin_grupo = [
            'C5' => 10,
            'C6' => 10,
            'C7' => 20,
            'C8' => 20,
            'C9' => 10,
            'C10' => 0,
            'C11' => 0,
            'C12' => 0,
            'C13' => 0,
            'C14' => 0,
            'C15' => 0
        ];

        // Por cada caja de texto de la fila
        $total_def = 0;
        $peso_columna = 0;
        $suma_calificaciones_grupo = 0;
        $num_calificaciones_grupo = 0;

        $arr_pesos = [];
        foreach ($linea_asignatura as $field => $value) {

            if (in_array($field,['asignatura','asignatura_id'])) {
                continue;
            }

            if ( in_array( $field, $grupo_pesos) ) {
                if ( $value != 0 && $value != '') {
                    $num_calificaciones_grupo++;	
                }
            }else{

                if ($num_calificaciones_grupo != 0) {
                    $peso_columna = 30 / $num_calificaciones_grupo;

                    switch ($num_calificaciones_grupo) {
                        case '1':
                            $arr_pesos = [ 0, $peso_columna, 0, 0, 0];
                            break;
                        case '2':
                            $arr_pesos = [ 0, $peso_columna, $peso_columna, 0, 0];
                            break;
                        case '3':
                            $arr_pesos = [ 0, $peso_columna, $peso_columna, $peso_columna, 0];
                            break;
                        case '4':
                            $arr_pesos = [ 0, $peso_columna, $peso_columna, $peso_columna, $peso_columna];
                            break;
                    }

                    $num_calificaciones_grupo = 0; // Para que no vuelva a entrar
                }
                
                $arr_pesos[] = $pesos_columnas_sin_grupo[$field];
            }
        }

        return $arr_pesos;
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
        $limite=10;
        /*
        if(count($registros)>0){
            $ultimoTotal=0;
            foreach($registros as $r){
                $total=1;
                for($m=1; $m < $limite; $m++){
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
        */
    ?>

    <table id="tbDatos" class="table table-striped tabla_registros" style="margin-top: -4px;">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th colspan="4" style="background: #a9d8c8; text-align:center;">Tareas (30%)</th>
                <th style="background: #ee8f6a; text-align:center;">Ex. saberes prev. (10%)</th>
                <th style="background: #fffc2e; text-align:center;">Exposición (10%)</th>
                <th style="background: #5b94e9; text-align:center;">Mesa trabajo (20%)</th>
                <th style="background: #e070e0; text-align:center;">Ex. Final (20%)</th>
                <th style="background: #8df8a5; text-align:center;">Prueba externa (10%)</th>
                <th colspan="3">&nbsp;</th>
            </tr>
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

                        $arr_pesos = get_array_pesos($registros[$i]->getAttributes());

                        $sumatoria_calificaciones = 0;
                        $mostrar_promedio = true;
                        $n = 0;
                        for ($k=1; $k < $limite; $k++) {                            

                            $c = 'C'.$k;
                            $fecha_calificacion = '';
                            $detalle_calificacion = 'Sin detalle.';
                            $texto_calificacion = '';
                            $style="color: #000000;";

                            $peso = $arr_pesos[$k];
                            $lbl_peso = '';
                            
                            $registro_calificacion = $registros[$i]->$c;
                                    
                            $registro_calificacion = $registro_calificacion * $peso / 100;

                            $texto_calificacion = number_format( $registro_calificacion, 2, '.', ',');
                            $lbl_peso = '<br><span style="font-size: 0.8em;">' . $registros[$i]->$c . ' x ' . $peso . '%</span>';
                            $mostrar_promedio = false;

                            if( $texto_calificacion <= $nota_reprobar )
                            {
                                $style="color: #f00;";
                            }
                            
                    ?>
                            <td>
                                <button style="{{$style}}" type="button" class="btn btn-secondary" data-toggle="tooltip" data-html="true" data-placement="top" title="{{$fecha_calificacion.$detalle_calificacion}}"> {{$texto_calificacion}} </button >
                                {!! $lbl_peso !!}
                            </td>
                    <?php
                            $sumatoria_calificaciones += (float)$registro_calificacion;
                        }
                            $prom = $sumatoria_calificaciones;

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
                            <th style="font-size: 16px; {{$style2}}"> {{number_format($prom, 2, '.', ',')}} </th>
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