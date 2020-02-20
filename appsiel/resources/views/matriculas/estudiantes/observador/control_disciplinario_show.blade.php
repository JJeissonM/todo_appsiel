<?php 
	
	$control_disciplinario = App\Matriculas\ControlDisciplinario::get_un_estudiante($estudiante->id);

?>
<h2>Control Académico y disciplinario</h2>
	<hr>
	<br>
	<div class="alert alert-info alert-dismissible">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  Pase el mouse por encima de cada código de abajo para leer su descripción.
	</div>
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable" width="100%">
			<thead>
				<tr>
					<th>Semana</th>
					<th>Registros</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($control_disciplinario as $fila)

					@php
						$semana = App\Core\SemanasCalendario::find($fila->semana_id);
					@endphp
					
					@php
						$registros = App\Matriculas\ControlDisciplinario::where([
																'estudiante_id' => $fila->estudiante_id,
																'semana_id' => $fila->semana_id,
																'curso_id' => $fila->curso_id])
														->get();
					@endphp

					<tr>
						<td>{{ $semana->descripcion }}</td>
						<td>
							@foreach($registros as $fila2)
								@php
									$asignatura = App\Calificaciones\Asignatura::find($fila2->asignatura_id);
								@endphp
								{{ $asignatura->descripcion }}: {!! imprimir_codigos($fila2) !!}
							@endforeach
						</td>
					</tr>
				@endforeach
			</tbody>

		</table>

		<!--
		<h4> Calificación general </h4>
		Positivo: { { App\Matriculas\CodigoDisciplinario::where( ['estudiante_id' => $fila->estudiante_id, 'tipo_codigo' => 'positivo'] )->count( ) }}
		<br>
		Negativo: { { $negativo }}
	-->
	</div>

	<br/><br/>

<?php

	function imprimir_codigos($fila)
	{
		$mostrar = '';
		if ( !is_null($fila) ) 
		{
			//$fila = $fila[0];
		}else{
			$fila = (object)['codigo_1_id' => 0, 'codigo_2_id' => 0, 'codigo_3_id' => 0, 'observacion_adicional' => ''];
		}

		// Si hay al menos un código
		if( ($fila->codigo_1_id + $fila->codigo_2_id + $fila->codigo_3_id) > 0 )
		{
			$mostrar = '<ul>';
		}

		if ( $fila->codigo_1_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($fila->codigo_1_id);
			$mostrar .= '<li> '.$el_codigo->id.': '.$el_codigo->descripcion.'</li>';
		}

		if ( $fila->codigo_2_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($fila->codigo_2_id);
			$mostrar .= '<li> '.$el_codigo->id.': '.$el_codigo->descripcion.'</li>';
		}

		if ( $fila->codigo_3_id != 0) {
			$el_codigo = App\Matriculas\CodigoDisciplinario::find($fila->codigo_3_id);
			$mostrar .= '<li> '.$el_codigo->id.': '.$el_codigo->descripcion.'</li>';
		}

		if ( $fila->observacion_adicional != '') {

			$mostrar .= '<li><code>'.$fila->observacion_adicional.'</code></li>';

		}

		return $mostrar.'</ul>';
	}
?>