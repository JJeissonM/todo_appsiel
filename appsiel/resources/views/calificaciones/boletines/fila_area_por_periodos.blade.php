@if ( $area_anterior != $linea->area_descripcion && $mostrar_areas == 'Si')
	<?php
		$cant_columnas_aux = $cant_columnas;

        if($mostrar_logros)
        {
            $cant_columnas_aux;
        }

	    $decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');

        // 122 = ID del Modelo "Areas"  947 = ID del Campo "Mostrar etiqueta en boletines"
        $area_descripcion = '';
        if( $linea->area->get_valor_eav( 122, $linea->area_id, 947) != 'No' )
        {
            $area_descripcion = strtoupper( $linea->area_descripcion );
        }
	?>
	<tr style="background: #ddd;">
        <td colspan="{{ $cant_columnas_aux }}" style="text-align: center;">
            <b> {{ $area_descripcion }}</b>
        </td>
    </tr>
    @if( $mostrar_calificacion_media_areas )
        <tr style="background: #ddd;">
            <td style="text-align: right; font-size:0.7em;"><i>Promedio del área >></i></td>

            @if($mostrar_intensidad_horaria)
                <td>&nbsp;</td>
            @endif

            <?php
                $n = 0;
                $total_poderadas = 0;
                foreach($periodos as $periodo_lista)	{

                    $cali_periodo = 0;
                    $calificacion_media_ponderada = 0;
                    $advertencia = '';
                    foreach ( $lineas_cuerpo_boletin as $datos_linea )
                    {
                        if($datos_linea->asignatura_id == 146) // Italiano
                        {
                            //continue;
                        }

                        if ( $datos_linea->area_id == $linea->area_id )
                        {
                            $calificacion_nota_original = $datos_linea->calificaciones_todos_los_periodos_asignatura_estudiante->where('id_periodo', $periodo_lista->id)->first();
                            
                            $lbl_cali_periodo = '&nbsp;';
                            if ( $calificacion_nota_original != null )
                            {
                                $cali_periodo = (float)$calificacion_nota_original->calificacion;
                                $lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' );

                                $cali_nivelacion_periodo = $datos_linea->calificaciones_niveladas_todos_los_periodos_asignatura_estudiante->where('id_periodo', $periodo_lista->id)->first();

                                if( $cali_nivelacion_periodo != null )
                                {
                                    $cali_periodo = (float)$cali_nivelacion_periodo->calificacion;
                                    $lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' ) . '<sup>n</sup>';
                                }
                            }

                            $peso_asignatura = $datos_linea->peso_asignatura;
                            if (in_array( $datos_linea->asignatura_id, [127,128] )) { // Las acompañantes de Italiano
                                //$peso_asignatura += 16.667;
                            }
                            $calificacion_media_ponderada += ($cali_periodo * $peso_asignatura / 100 );
                            if ( $datos_linea->peso_asignatura == 0 )
                            {
                                $advertencia = 'Una o mas asignaturas no tienen peso para el cálculo de la media ponderada. Sus valores en la poderación son cero.';
                            }
                        }
                    }

                    $escala_valoracion_area = App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion( $calificacion_media_ponderada, $periodo->periodo_lectivo_id );
                    
                    $lbl_escala_valoracion_area = '';
                    if ( $escala_valoracion_area )
                    {
                        $lbl_escala_valoracion_area = $escala_valoracion_area->nombre_escala;
                    }

                    $lbl_nota_original = number_format( $calificacion_media_ponderada, $decimales, ',', '.' );
                    
                    // Calificacion del periodo
                    echo '<td style="text-align: center; width: 50px; padding: 1px;"> ' . $lbl_nota_original . ' <span style="color:red;">' . $advertencia . '</span></td>';

                    $total_poderadas += $calificacion_media_ponderada;
                    $n++;
                }

                $prom_area = 0;
                if ($n != 0) {
                    $prom_area = $total_poderadas / $n;
                }

                $lbl_calificacion_area = number_format( $prom_area, $decimales, ',', '.' );
                /*
                $escala_valoracion_prom_area = App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion( $calificacion_media_ponderada, $periodo->periodo_lectivo_id );
                
                $lbl_escala_valoracion_prom_area = '';
                if ( $escala_valoracion_prom_area )
                {
                    $lbl_escala_valoracion_prom_area = $escala_valoracion_prom_area->nombre_escala;
                } 
                */               
	
                if( $mostrar_calificacion_requerida )
                {
                    $tope_escala_valoracion_minima = App\Calificaciones\EscalaValoracion::where( 'periodo_lectivo_id', $periodo->periodo_lectivo_id )->orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;

                    $cali_faltante = 4 * $tope_escala_valoracion_minima - $total_poderadas;

                    if ( $cali_faltante > 5) {
                        $lbl_calificacion_area = 'Perdida';
                        if ( $linea->asignatura_id == 146 ) { // Italiano
                            //$lbl_calificacion_area = '-';
                        }
                    }else{
                        $lbl_calificacion_area = number_format( $cali_faltante, $decimales, ',', '.' );
                    }
                }
            ?>
            
            <td style="text-align: center; width: 50px; padding: 1px;"> {{ $lbl_calificacion_area }} </td>

            @if($mostrar_logros)
                <td>&nbsp;</td>
            @endif
        </tr>
    @endif
@endif