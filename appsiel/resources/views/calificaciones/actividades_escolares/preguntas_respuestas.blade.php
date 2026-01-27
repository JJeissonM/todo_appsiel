@if( !empty( (array)$preguntas ) )
	@php
		$vec_respuestas = $vec_respuestas ?? [];
		$total_preguntas = count($preguntas);
		$contestadas = count($vec_respuestas);
		$porcentaje_progreso = $total_preguntas > 0 ? round($contestadas / $total_preguntas * 100) : 0;
	@endphp

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-warning">
				<div class="panel-heading clearfix">
					<strong>Progreso del cuestionario</strong>
					<span class="pull-right text-muted">{{ $contestadas }} de {{ $total_preguntas }} respondidas</span>
				</div>
				<div class="panel-body">
					<div class="progress">
						<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $porcentaje_progreso }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $porcentaje_progreso }}%;">
							{{ $porcentaje_progreso }}%
						</div>
					</div>
				<p class="text-muted" style="margin:0;">Selecciona una opción para cada pregunta, presiona <strong>Agregar respuesta</strong> y, al terminar, guarda todas las respuestas.</p>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Lista de preguntas</strong></div>
				<div class="panel-body">
					@php $i = 1; @endphp
					@foreach($preguntas as $pregunta)
						@php
							$ocultar = 'block';
							$valor_respuesta = null;
							if ( array_key_exists($pregunta->id, $vec_respuestas) )
							{
								$ocultar = 'none';
								$valor_respuesta = $vec_respuestas[$pregunta->id]['respuesta'];
								$vec_respuestas[$pregunta->id]['descripcion'] = $pregunta->descripcion;
								$vec_respuestas[$pregunta->id]['posicion_listado'] = $i;
								$vec_respuestas[$pregunta->id]['texto_respuesta'] = $valor_respuesta;
							}
						@endphp
						<div id="div_{{ $i }}" class="panel panel-info" style="display: {{ $ocultar }};">
							<div class="panel-heading">
								<strong>{{ $i }}. <span class="pregunta_descripcion" style="display: inline;"> {!! $pregunta->descripcion !!} </span></strong>
							</div>
							<div class="panel-body">
				@php
					$tipo_original = $pregunta->tipo ?? '';
					$tipo_normalizado = strtolower($tipo_original);
				$reemplazos = [
					'á' => 'a',
					'é' => 'e',
					'í' => 'i',
					'ó' => 'o',
					'ú' => 'u',
					'ñ' => 'n',
				];
					$tipo_normalizado = strtr($tipo_normalizado, $reemplazos);
					$tipo_normalizado = preg_replace('/[^a-z0-9\s-]/', '', $tipo_normalizado);

					switch (true)
					{
						case strpos($tipo_normalizado, 'abierta') !== false:
							$respuesta = '<br/>'.Form::textarea( 'pregunta_'.$pregunta->id, $valor_respuesta, [ 'id' => 'pregunta_'.$pregunta->id, 'class' => 'form-control' ] );
							break;

						case strpos($tipo_normalizado, 'seleccion') !== false && strpos($tipo_normalizado, 'multiple') !== false:
							$respuesta = '';
							$opciones = json_decode($pregunta->opciones,true);
							if ( !is_null($opciones) )
							{
								foreach ($opciones as $respuesta_opcion => $value)
								{
									$checked = '';
									if ( $respuesta_opcion == $valor_respuesta )
									{
										$checked = 'checked';
										$vec_respuestas[$pregunta->id]['texto_respuesta'] = $respuesta_opcion.') '.$value;
									}
									$respuesta.='<div class="radio">
									  <label><input type="radio" name="pregunta_'.$pregunta->id.'" value="'.$respuesta_opcion.'" '.$checked.'> <div style="display:inline;">'.' '.$respuesta_opcion.') '.$value.'</div></label>
									</div>';
								}
							}else{
								$respuesta = '<div class="alert alert-danger">
								  <strong>¡Error!</strong> Hay un problema en la creación de la pregunta. Consulte con el docente o administrador del sistema.</div>';
							}
							break;

						case strpos($tipo_normalizado, 'falso') !== false && strpos($tipo_normalizado, 'verdadero') !== false:
							$checkedF = '';
							$checkedV = '';
							if ( $valor_respuesta == 'Falso' ) {
								$checkedF = 'checked';
								$checkedV = '';
							}
							if ( $valor_respuesta == 'Verdadero' ) {
								$checkedF = '';
								$checkedV = 'checked';
							}
							$respuesta = '<div class="falso_verdadero">';
							$respuesta.='<div class="radio">
								  <label><input type="radio" name="pregunta_'.$pregunta->id.'" value="Falso" '.$checkedF.'> <div style="display:inline;">Falso</div></label>
							</div>';
							$respuesta.='<div class="radio">
								  <label><input type="radio" name="pregunta_'.$pregunta->id.'" value="Verdadero" '.$checkedV.'> <div style="display:inline;">Verdadero</div></label>
							</div>';
							$respuesta.='</div>';
							break;
						case $tipo_normalizado === '':
							$respuesta = '<div class="alert alert-warning"><strong>Info:</strong> Tipo de pregunta no definido.</div>';
							break;
						default:
							$respuesta = 'Error seleccion.';
							break;
					}
				@endphp

								{!! $respuesta !!}
								<button class="btn btn-primary btn-xs btn_agregar_respuesta" data-pregunta_id="{{$pregunta->id}}" data-tipo_pregunta="{{$pregunta->tipo}}" data-numero_pregunta="{{ $i }}" data-descripcion="{{ strip_tags($pregunta->descripcion) }}"> Agregar respuesta</button>
							</div>
						</div>
						@php $i++ @endphp
					@endforeach
				</div>
			</div>
		</div>

		@php
			$filas = '';
			foreach ($vec_respuestas as $pregunta_id => $pregunta_info)
			{
				$filas .= '<tr>
							<td style="display:none;">'.$pregunta_info['posicion_listado'].'</td>
							<td style="display:none;">'.$pregunta_id.'</td>
							<td style="display:none;">'.$pregunta_info['respuesta'].'</td>
							<td>'.$pregunta_info['descripcion'].'</td>
							<td>'.$pregunta_info['texto_respuesta'].'</td>
							<td><button type="button" class="btn btn-warning btn-xs btn_eliminar" title="Cambiar respuesta"><i class="glyphicon glyphicon-edit"></i></button></td>
							</tr>';
			}
		@endphp

		<div class="col-md-6">

		<div class="col-md-6">
			<div class="panel panel-success">
				<div class="panel-heading"><strong>Hoja de respuestas</strong></div>
				<div class="panel-body">
			<div class="alert alert-warning alert-dismissible">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Advertencia!</strong> 
			  <br>Si no presiona el botón "Guardar respuestas" ninguna respuesta será guardada.
			</div>
					<div class="table-responsive">
						<table class="table table-striped table-bordered" id="ingreso_registros">
							<thead>
								<tr>
									<th style="display:none;">No. Pregunta</th>
									<th style="display:none;">ID Pregunta</th>
									<th style="display:none;">Respuesta</th>
									<th>Pregunta</th>
									<th>Respuesta</th>
									<th>Corregir</th>
								</tr>
							</thead>
							<tbody>
								{!! $filas !!}
							</tbody>
						</table>
					</div>

					{{ Form::open(['url'=>'actividades_escolares/guardar_respuesta','id'=>'formulario_respuesta']) }}

						{{ Form::hidden('estudiante_id', $estudiante->id ) }}
						{{ Form::hidden('actividad_id', $actividad->id ) }}
						{{ Form::hidden('cuestionario_id', $cuestionario->id ) }}
						{{ Form::hidden('respuesta_enviada', $respuestas->respuesta_enviada, ['id'=>'respuesta_enviada', 'required'=>'required'] ) }}
						{{ Form::hidden('respuesta_id', $respuestas->id, ['id'=>'respuesta_enviada', 'required'=>'required'] ) }}
						
						<button class="btn btn-primary btn-sm btn_guardar_respuestas"><i class="fa fa-save"></i> Guardar TODAS las respuestas</button>

					{{ Form::close() }}
				</div>
			</div>
		</div>

	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-info">
				<div class="panel-heading"><strong>Estado</strong></div>
				<div class="panel-body">
		<p class="text-muted" style="margin:0;">Esperando la publicación de resultados.</p>
				</div>
			</div>
		</div>
	</div>

@endif

