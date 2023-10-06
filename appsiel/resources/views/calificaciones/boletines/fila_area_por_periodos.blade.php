@if ( $area_anterior != $linea->area_descripcion && $mostrar_areas == 'Si')
	<?php
		$cant_columnas_aux = $cant_columnas - 1;
	    $decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');
	?>
	<tr style="background: #ddd;">
			<!--  122 = ID del Modelo "Areas"  947 = ID del Campo "Mostrar etiqueta en boletines" -->
			@if( $linea->area->get_valor_eav( 122, $linea->area_id, 947) != 'No' )
				<td colspan="{{ $cant_columnas }}">
					<b> {{ strtoupper( $linea->area_descripcion ) }}</b>
				</td>
            @else
                <td colspan="{{ $cant_columnas }}">&nbsp;</td>
            @endif
    </tr>
    @if( $mostrar_calificacion_media_areas )
        <tr style="background: #ddd;">
            <td>&nbsp;</td>
            <?php
                $n = 0;
                $total_poderadas = 0;
                foreach($periodos as $periodo_lista)	{

                    $cali_periodo = 0;
                    $calificacion_media_ponderada = 0;
                    $advertencia = '';
                    foreach ( $lineas_cuerpo_boletin as $datos_linea )
                    {
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

                            $calificacion_media_ponderada += ($cali_periodo * $datos_linea->peso_asignatura / 100 );
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

                    switch( config('calificaciones.etiqueta_calificacion_boletines') )
                    {
                        case 'numero_y_letras':
                            $lbl_nota_original = number_format( $calificacion_media_ponderada, $decimales, ',', '.' ) . ' (' . $lbl_escala_valoracion_area . ')';
                            break;

                        case 'solo_numeros':
                            $lbl_nota_original = number_format( $calificacion_media_ponderada, $decimales, ',', '.' );
                            break;

                        case 'solo_letras':
                            $lbl_nota_original = $lbl_escala_valoracion_area;
                            break;

                        default:
                            $lbl_nota_original = number_format( $calificacion_media_ponderada, $decimales, ',', '.' ) . ' (' . $lbl_escala_valoracion_area . ')';
                            break;
                    }
                    
                    echo '<td style="text-align: center; width: 28px;"> ' . $lbl_nota_original . ' <span style="color:red;">' . $advertencia . '</span></td>';

                    $total_poderadas += $calificacion_media_ponderada;
                    $n++;
                }

                $prom_area = 0;
                if ($n != 0) {
                    $prom_area = $total_poderadas / $n;
                }

                $escala_valoracion_prom_area = App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion( $calificacion_media_ponderada, $periodo->periodo_lectivo_id );$lbl_escala_valoracion_prom_area = '';
                if ( $escala_valoracion_prom_area )
                {
                    $lbl_escala_valoracion_prom_area = $escala_valoracion_prom_area->nombre_escala;
                }
            ?>
            <td style="text-align: center; width: 28px;"> {{ number_format( $prom_area, $decimales, ',', '.' ) }} ({{ $lbl_escala_valoracion_prom_area }})</td>
        </tr>
    @endif
@endif