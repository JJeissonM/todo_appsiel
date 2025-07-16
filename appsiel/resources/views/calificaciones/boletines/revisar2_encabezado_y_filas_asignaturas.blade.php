
<table class="contenido table table-striped">
<thead>
    <tr>
        <th>{{ config('calificaciones.etiqueta_asignatura') }}</th>
        <th>{{ config('calificaciones.etiqueta_valoracion') }}</th>
        <th>{{ config('calificaciones.etiqueta_logros') }}</th>
    </tr>
</thead>
<tbody>
    <?php

        $tbody = '';
        foreach($asignaturas as $asignatura) 
        {
            if ($asignatura->id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
                continue;
            }
            
            // Se llama a la calificacion de cada asignatura (en la colección de calificaciones) 
            $obj_calificacion = $calificaciones->where('id_estudiante',$estudiante->id_estudiante)->where('id_asignatura',$asignatura->id)->first();
            
            $calificacion = 0;
            $escala = (object) array('id' => 0, 'nombre_escala' => '');
            
            $lbl_nivelacion = '';
            
            // Se calcula el texto de la calificación
            if ( !is_null($obj_calificacion) ) 
            {
                $calificacion = $obj_calificacion->calificacion;
                if ( !is_null( $obj_calificacion->nota_nivelacion() ) )
                {
                    $calificacion = $obj_calificacion->nota_nivelacion()->calificacion;
                    $lbl_nivelacion = 'n';
                }

                $escala = App\Calificaciones\EscalaValoracion::where('calificacion_minima','<=',$calificacion)
                                ->where('calificacion_maxima','>=',$calificacion)
                                ->where('periodo_lectivo_id','=',$periodo->periodo_lectivo_id)->first();									
            }

            $tbody.='<tr>
                    <td width="350px" title="ID: '.$asignatura->id.'">'.$asignatura->descripcion .'</td>';

            if( $calificacion == 0)
            {
                $tbody.='<td '.$estilo_advertencia.'>&nbsp;</td>';
            }else{
                if ( is_null($escala) ) 
                {
                    $escala = (object) array('id' => 0, 'nombre_escala' => '');
                }
                $tbody.='<td>' . number_format( (float)$calificacion, $decimales, ',', '.' ).'<sup>' . $lbl_nivelacion . '</sup> ('.$escala->nombre_escala.')</td>';
            }
            
            $tbody .=  \View::make('calificaciones.boletines.revisar2_incluir_celda_logros',[
                            'escala'=>$escala,'periodo_id'=>$periodo->id,'curso_id'=>$estudiante->curso_id,'asignatura_id'=>$asignatura->id, 'obj_calificacion' => $obj_calificacion, 'id_estudiante' => $estudiante->id, 'metas_del_curso_en_el_periodo' => $metas_del_curso_en_el_periodo])->render();

            $tbody.='</tr>';

        } //fin recorrido de asignaturas del estudiante

        echo $tbody;						
    ?>