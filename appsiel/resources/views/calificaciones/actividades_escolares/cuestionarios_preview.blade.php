<div class="panel panel-default">
	<div class="panel-heading">
		<h4 style="margin:0;">{{ $cuestionario->descripcion }}</h4>
		<p class="text-muted" style="margin:0;">{{ $cuestionario->detalle }}</p>
	</div>
	<div class="panel-body">
		<ol>
			@foreach($cuestionario->preguntas as $pregunta)
				<li>
					<strong>{{ $pregunta->descripcion }}</strong>
					@if($pregunta->tipo == 'Seleccion multiple única respuesta')
						<ul style="margin-left: 20px;">
							@foreach(json_decode($pregunta->opciones, true) ?? [] as $clave => $texto)
								<li {!! $clave == $pregunta->respuesta_correcta ? 'class="text-success"' : '' !!}>
									{{ $clave }}) {{ $texto }}
								</li>
							@endforeach
						</ul>
					@endif
					@if($pregunta->tipo == 'Falso-Verdadero')
						<ul style="margin-left: 20px;">
							<li class="{{ $pregunta->respuesta_correcta == 'Verdadero' ? 'text-success' : '' }}">Verdadero</li>
							<li class="{{ $pregunta->respuesta_correcta == 'Falso' ? 'text-success' : '' }}">Falso</li>
						</ul>
					@endif
				</li>
			@endforeach
		</ol>
	</div>
</div>
