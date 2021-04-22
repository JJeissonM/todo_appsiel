@if ( $area_anterior != $linea->asignacion_asignatura->asignatura->area->descripcion AND $mostrar_areas == 'Si')
	<tr style="background: #ddd;">
			&nbsp;
			<!-- 
				122 = ID del Modelo "Areas"
				947 = ID del Campo "Mostrar etiqueta en boletines" -->
			@if( $linea->asignacion_asignatura->asignatura->area->get_valor_eav( 122, $linea->asignacion_asignatura->asignatura->area_id, 947) != "No" )
				<td colspan="{{$cant_columnas-1}}">
						<b> ÁREA: {{ strtoupper( $linea->asignacion_asignatura->asignatura->area->descripcion ) }}</b>
				</td>
				<td>

						@if( $mostrar_calificacion_media_areas )
							<?php
								$calificacion_media_ponderada = 0;
								$advertencia = '';
								foreach ( $lineas_cuerpo_boletin as $datos_linea )
								{
									if ( $datos_linea->area_id ==  $linea->asignacion_asignatura->asignatura->area->id )
									{
										$calificacion_media_ponderada += ($datos_linea->valor_calificacion * $datos_linea->peso_asignatura / 100 );
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

								$decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');

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

								echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b> Cal. media: ' . $lbl_nota_original . '</b> <span style="color:red;">' . $advertencia  . '</span>' ;
							?>
						@endif					
				</td>
			@endif
	</tr>
@endif