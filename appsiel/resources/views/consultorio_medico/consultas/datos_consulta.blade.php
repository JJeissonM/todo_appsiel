<h3> Consulta No. {{ $consulta->id }} </h3>
<table class="table table-bordered">
	<tr>
		<td>
			<b>Fecha/Hora consulta: </b> {{ $consulta->fecha }} / {{ $consulta->created_at->format('h:i:s a') }}
		</td>
		<td>
			@php $consultorio = App\Salud\Consultorio::find($consulta->consultorio_id); @endphp 
			<b>Consultorio:</b> {{ $consultorio->descripcion }}
		</td>
		<td>
			<b>Tipo Consulta:</b> {{ $consulta->tipo_consulta }}
		</td>
	</tr>
	
	<tr>
		<td colspan="3">
			@php 
				// ADVERTENCIA el campo profesional_salud_id corresponde al ID del usuario que creó la consulta
				//$profesional_salud = App\Salud\ProfesionalSalud::find($consulta->profesional_salud_id);
				$profesional_salud = App\User::find($consulta->profesional_salud_id);
				//dd($profesional_salud->name);  
			@endphp 
			<b>Atendido por:</b> {{ $profesional_salud->name }}
		</td>
	</tr>
<!---->
	<tr>
		<td>
			<b>Nombre acompañante:</b> {{ $consulta->nombre_acompañante }}
		</td>
		<td>
			<b>Parentezco:</b> {{ $consulta->parentezco_acompañante }}
		</td>
		<td>
			<b>Doc. Identificación:</b> {{ $consulta->documento_identidad_acompañante }}
		</td>
	</tr>
</table>