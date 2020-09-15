<div class="row">
	<div class="col-md-12">
        <div>
            <table class="table table-striped table-bordered" id="ingreso_registros">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Resultado</th>
                        <th>Revisar</th>
                    </tr>
                </thead>
                <tbody>
                	@foreach($estudiantes as $estudiante)

                		<?php
	
							$total_correctas = 0;
							$total_preguntas = 0.00001;
							$i = 0;

							// Se obtienen las respuestas enviadas de cada estudiante
							$respuestas = App\Cuestionarios\RespuestaCuestionario::where(['actividad_id'=>$actividad->id,'estudiante_id'=>$estudiante->id,'cuestionario_id'=>$cuestionario->id])->get();

							if( !empty( $respuestas->toArray() ) )
				            {   
				                // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
				                $respuestas = $respuestas[0];
				            }else{
				                $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];
				            }



							// Las respuestas están almacenadas en formato JSON { "pregunta_id":"respuesta" }
							$respuesta_enviada = json_decode( $respuestas->respuesta_enviada );
							$vec_respuestas_enviadas = [];

							// Crear array de respuestas con base en el JSON
							if ( !is_null($respuesta_enviada) ) {
								foreach ($respuesta_enviada as $key => $value) 
								{
									// $key corresponde a la pregunta_id
									$vec_respuestas_enviadas[$key] = $value;
								}
							}
						?>

						@foreach($preguntas as $pregunta)

							<?php
								$respuesta_correcta = $pregunta->respuesta_correcta;
								$respuesta_enviada = '';
								if ( array_key_exists($pregunta->id, $vec_respuestas_enviadas) ) 
								{
									$respuesta_enviada = $vec_respuestas_enviadas[$pregunta->id];
								}

								// Para las preguntas de Selección múltiple, se cambia el texto de la respuesta para una mejor visualización
				              	if($pregunta->tipo == 'Seleccion multiple única respuesta') 
				              	{
				              		$opciones = json_decode($pregunta->opciones,true);

				              		if ( !is_null( $opciones) )
				              		{
				              			foreach ($opciones as $respuesta_opcion => $value) 
					          			{
					          				if ( $respuesta_opcion == $respuesta_correcta ) 
					          				{
					          					$respuesta_correcta = $respuesta_opcion.') '.$value;
					          				}else{
					          				}

					          				if ( $respuesta_opcion == $respuesta_enviada ) 
					          				{
					          					$respuesta_enviada = $respuesta_opcion.') '.$value;
					          				}

					          			}
				              		}					          			

				              	}

								if ( $respuesta_enviada == $respuesta_correcta ) 
								{
									if($pregunta->tipo != 'Abierta') 
					              	{
					              		$total_correctas++;
					              	}									
								}

								if($pregunta->tipo != 'Abierta') 
				              	{
				              		$total_preguntas++;
				              	}

				              	$i++;
				  			?>
						@endforeach <!-- Por cada pregunta -->

						<tr>
							<td> 
								{{ $estudiante->nombre_completo }}
							</td>
							<td> 
								<b> {{ $total_correctas }} de {{ round( $total_preguntas ) }} &nbsp;&nbsp;  - &nbsp;&nbsp;  {{ round( $total_correctas / $total_preguntas * 100, 2 ) }}%  </b>
							</td>
							<td> 
								<button type="button" class="btn btn-primary btn-xs btn_ver_respuestas" data-estudiante_id="{{ $estudiante->id }}"><i class="fa fa-eye"> </i></button>
							</td>
						</tr>

					@endforeach <!-- Por cada estudiante -->
                </tbody>
            </table>
		</div>
	</div>
</div>