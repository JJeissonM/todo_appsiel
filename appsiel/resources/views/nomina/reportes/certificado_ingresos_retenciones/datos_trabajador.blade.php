<table class="table table-bordered">
	<tr>
		<td style="width: 10px;">
			<div style="vertical-align: center;">
				<span style="writing-mode: vertical-rl;"> Trabajador </span>
			</div>
		</td>
		<td style="width: 170px;">
			24. Tipo de documento
			<br>
			{{ $empleado->tercero->id_tipo_documento_id }}
		</td>
		<td style="width: 230px;">
			25. Número de Identificación
			<br>
			{{ number_format( $empleado->tercero->numero_identificacion, 0, ',', '.' ) }}
		</td>
		<td>
			Apellidos y nombres
			<br>
			{{ $empleado->tercero->apellido1 }}	{{ $empleado->tercero->apellido2 }}	{{ $empleado->tercero->nombre1 }}	{{ $empleado->tercero->otros_nombres }}
		</td>
	</tr>
</table>