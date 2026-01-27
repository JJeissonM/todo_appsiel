<?php

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

@php
	$total_correctas = 0;
	$total_preguntas = 0;
	foreach ($preguntas as $pregunta_item)
	{
		if ( $pregunta_item->tipo == 'Abierta' )
		{
			continue;
		}

		$respuesta_estudiante = $vec_respuestas_enviadas[$pregunta_item->id] ?? '';
		if ( $respuesta_estudiante === $pregunta_item->respuesta_correcta )
		{
			$total_correctas++;
		}

		$total_preguntas++;
	}

	$porcentaje_total = $total_preguntas > 0 ? round( $total_correctas / $total_preguntas * 100, 2 ) : 0;
	$contador = 0;
@endphp

<div class="panel panel-success">
	<div class="panel-heading">
		<strong>Resultados</strong>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-6">
				<p class="lead" style="margin-top:0;">Correctas: <strong>{{ $total_correctas }} de {{ $total_preguntas }}</strong></p>
			</div>
			<div class="col-md-6 text-right">
				<div class="progress" style="margin-top:18px;">
					<div class="progress-bar progress-bar-striped progress-bar-success" role="progressbar" aria-valuenow="{{ $porcentaje_total }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $porcentaje_total }}%;">
						{{ $porcentaje_total }}%
					</div>
				</div>
				<p class="text-muted" style="margin-bottom:0;">Preguntas calificadas: {{ $total_preguntas }}</p>
			</div>
		</div>
		<hr>
		<div class="table-responsive">
			<table class="table table-striped table-hover">
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
						@php
							$contador++;
							$respuesta_correcta = $pregunta->respuesta_correcta;
							$respuesta_enviada = $vec_respuestas_enviadas[$pregunta->id] ?? '';
							$lista_preguntas = '';

							if($pregunta->tipo == 'Seleccion multiple única respuesta') 
							{
								$opciones = json_decode($pregunta->opciones,true);
								if ( !is_null($opciones) )
								{
									$lista_preguntas .= '<ul>';
									foreach ($opciones as $respuesta_opcion => $value) 
									{
										if ( $respuesta_opcion == $respuesta_correcta ) 
										{
											$respuesta_correcta = $respuesta_opcion.') '.$value;
										}

										if ( $respuesta_opcion == $respuesta_enviada ) 
										{
											$respuesta_enviada = $respuesta_opcion.') '.$value;
										}

										$lista_preguntas .= '<li>'.$respuesta_opcion.') '.$value.'</li>';
									}
									$lista_preguntas .= '</ul>';
								}
							}

							if ( $respuesta_enviada == $respuesta_correcta && $pregunta->tipo != 'Abierta' ) 
							{
								$resultado = '<i class="fa fa-check"></i> Correcta';
								$clase = 'success';
							}else{
								$resultado = '<i class="fa fa-remove"></i> Incorrecta';
								$clase = 'danger';
							}
						@endphp
						<tr class="{{ $clase }}">
							<td> 
								<b> {{ $contador }}. {!! $pregunta->descripcion !!} </b>
								{!! $lista_preguntas !!}
							</td>
							<td> {{ $respuesta_enviada }} </td>
							<td> {{ $respuesta_correcta }} </td>
							<td> {!! $resultado !!} </td>
						</tr>
					@endforeach		
				</tbody>
			</table>
		</div>
	</div>
</div>
