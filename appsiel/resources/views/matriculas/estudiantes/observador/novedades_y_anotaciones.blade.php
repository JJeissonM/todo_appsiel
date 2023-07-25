<?php 
	$periodos = App\Calificaciones\Periodo::get_todos_periodo_lectivo_actual();
?>


<h3 align="center"> ANOTACIONES </h3>
<div class="table-responsive">
	@foreach ($periodos as $periodo)
		<table class="table table-bordered table-striped" id="myTable" width="100%">
			<thead>
				<tr>
					<th colspan="3" style="background-color:rgb(255, 253, 125);">Observaciones generales periodo {{ $periodo->numero }}</th>
				</tr>
				<tr>
					<th style="background-color:rgb(255, 253, 125);">Fecha</th>
					<th style="background-color:rgb(255, 253, 125);">Novedad</th>
					<th style="background-color:rgb(255, 253, 125);">Teacher</th>
				</tr>
			</thead>
			<tbody>
					<?php 
						$novedades = App\Matriculas\NovedadesObservador::where([
							['id_estudiante','=',$estudiante->id],
							['id_periodo','=',$periodo->id]
						])
						->get();
					?>
					@foreach ($novedades as $novedad)						
						@php 
							$usuario = App\User::where('email',$novedad->creado_por)->value('name') 
						@endphp
						<tr>
							<td>{{ $novedad->fecha_novedad }}</td>
							<td>{{ $novedad->descripcion }}</td>
							<td>{{ $usuario }}</td>
						</tr>
					@endforeach
					<tr> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
					<tr> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
					<tr> <td>&nbsp;</td> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
			</tbody>
		</table>
	@endforeach
</div>