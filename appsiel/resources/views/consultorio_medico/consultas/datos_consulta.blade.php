<br>
<table class="table table-bordered">
	<tr>
		<td>
			<b>Fecha/Hora consulta: </b> {{ $consulta->fecha }} / {{ $consulta->created_at->format('h:i:s a') }}
		</td>
		<td>
			<b>Consultorio:</b> {{ $consulta->consultorio->descripcion }}
		</td>
		<td>
			<b>Tipo Consulta:</b> {{ $consulta->tipo_consulta }}
		</td>
	</tr>
	
	<tr>
		<td colspan="3">
			<?php
				if ( !is_null( $consulta->profesional_salud->tercero ) )
				{
					$profesional_salud = $consulta->profesional_salud->tercero->descripcion;
				}else{
					$profesional_salud = App\User::find($consulta->profesional_salud_id)->name;
				} 
			?>
			<b>Atendido por:</b> {{ $profesional_salud }}
		</td>
	</tr>
	<tr>
		<td>
			<b>Nombre acompañante:</b> {{ $consulta->nombre_acompañante }}
		</td>
		<td>
			<b>Parentezco:</b> {{ $consulta->parentezco_acompañante }}
		</td>
		<td>
			<b>Doc. Identificación:</b> {{ number_format((int)$consulta->documento_identidad_acompañante, 0, ',', '.') }}
		</td>
	</tr>
</table>