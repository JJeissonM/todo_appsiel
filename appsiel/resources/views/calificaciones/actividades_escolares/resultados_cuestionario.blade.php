<?php
	
	$total_correctas = 0;
	$total_preguntas = 0.0000001; // Para que no haya división sobre cero
	$i = 0;

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
<div class="row">
	<div class="col-md-12">
		<h4> Resultados </h4>
        <hr>
        <div>
            <table class="table table-striped table-bordered" id="ingreso_registros">
                <thead>
                    <tr>
                        <th>Pregunta</th>
                        <th>Respuesta enviada</th>
                        <th>Respuesta correcta</th>
                        <th>Resultado {{ Form::btnInfo( "Nota: las preguntas <Abiertas> no se tienen en cuenta para el cálculo del resultado." ) }} </th>
                    </tr>
                </thead>
                <tbody>
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

			              		$lista_preguntas = '<ul>';
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

			          				$lista_preguntas .= '<li>'.$respuesta_opcion.') '.$value.'</li>';
			          			}
			          			$lista_preguntas .= '</ul>';
			              	}else{
			              		$lista_preguntas = '';
			              	}

							if ( $respuesta_enviada == $respuesta_correcta ) 
							{
								$resultado = '<i class="fa fa-check"></i> Correcta';
								$clase = 'success';

								if($pregunta->tipo != 'Abierta') 
				              	{
				              		$total_correctas++;
				              	}
								
							}else{
								$resultado = '<i class="fa fa-remove"></i> Incorrecta';
								$clase = 'danger';
							}

							if($pregunta->tipo != 'Abierta') 
			              	{
			              		$total_preguntas++;
			              	}

			              	$i++;
			  			?>
						<tr class="{{ $clase }}">
							<td> 
								<b> {{ $i }}. {!! $pregunta->descripcion !!} </b>
								{!! $lista_preguntas !!}
							</td>
							<td> {{ $respuesta_enviada }} </td>
							<td> {{ $respuesta_correcta }} </td>
							<td> {!! $resultado !!} </td>
						</tr>
					@endforeach		
                </tbody>
                <tfoot>
                	<tr>
						<td colspan="3"></td>
						<td> 
							<b> {{ $total_correctas }} de {{ round( $total_preguntas ) }} &nbsp;&nbsp;  - &nbsp;&nbsp;  {{ round( $total_correctas / $total_preguntas * 100, 2 ) }}%  </b>
						</td>
					</tr>
                </tfoot>
            </table>
		</div>
	</div>
</div>