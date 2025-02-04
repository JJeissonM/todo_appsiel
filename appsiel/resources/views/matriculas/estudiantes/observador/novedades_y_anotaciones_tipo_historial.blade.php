<?php 
	$novedades = App\Matriculas\NovedadesObservador::where('id_estudiante',$estudiante->id)->orderBy('fecha_novedad')->get();
?>

<h4> HISTORIAL DEL ESTUDIANTE </h4>
<hr>
<div class="table-responsive">
	<table class="table table-bordered table-striped" width="100%">
		<tbody>
			@foreach ($novedades as $novedad)
				@php $periodo = App\Calificaciones\Periodo::find($novedad->id_periodo) @endphp
				@php $usuario = App\User::where('email',$novedad->creado_por)->value('name') @endphp
				<tr>
					<td>{{ $novedad->fecha_novedad }} : {{ $novedad->descripcion }} Docente: {{ $usuario }}</td></td>				
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
<br/><br/>