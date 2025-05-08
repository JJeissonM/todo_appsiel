@if ( $area_anterior != $linea->area_descripcion && $mostrar_areas == 'Si')
	<?php
		$cant_columnas_aux = $cant_columnas;
	?>
	<tr style="background-color: {{config('configuracion.color_principal_empresa')}}90;">
			<!--  122 = ID del Modelo "Areas"  947 = ID del Campo "Mostrar etiqueta en boletines" -->
			@if( $linea->area->get_valor_eav( 122, $linea->area_id, 947) != 'No' )
				<td colspan="{{ $cant_columnas_aux }}" style="text-align: center;">
					<b> {{ strtoupper( $linea->area_descripcion )}}</b>
					&nbsp;
					@if( $mostrar_calificacion_media_areas )
						<?php
							$calificacion_media_ponderada = 0;
							$advertencia = '';
							foreach ( $lineas_cuerpo_boletin as $datos_linea )
							{
								if ( $datos_linea->area_id == $linea->area_id )
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
						?>

						@if( $curso->maneja_calificacion == 1 )
							(<b> Cal. media: {!! $lbl_nota_original !!} </b> <span style="color:red;"> {!! $advertencia !!} </span>)
						@endif
						
					@endif
				</td>
			@else
				<td colspan="{{ $cant_columnas }}">&nbsp;</td>
			@endif
	</tr>
@endif