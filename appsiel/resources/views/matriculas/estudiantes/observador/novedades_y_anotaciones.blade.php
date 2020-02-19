<?php 
	$novedades = App\Matriculas\NovedadesObservador::where('id_estudiante',$estudiante->id)->get();
?>

<h2> Novedades y anotaciones </h2>
	<hr>
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="myTable" width="100%">
			<thead>
				<tr>
					<th>Fecha</th>
					<th>Periodo</th>
					<th>Novedad</th>
					<th>Profesor</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($novedades as $novedad)
					@php $periodo = App\Calificaciones\Periodo::find($novedad->id_periodo) @endphp
					@php $usuario = App\User::where('email',$novedad->creado_por)->value('name') @endphp
					<tr>
						<td>{{ $novedad->fecha_novedad }}</td>
						<td>{{ $periodo->descripcion }}</td>
						<td>{{ $novedad->descripcion }}</td>
						<td>{{ $usuario }}</td>
					</tr>
				@endforeach
			</tbody>

		</table>
	</div>

	<br/><br/>