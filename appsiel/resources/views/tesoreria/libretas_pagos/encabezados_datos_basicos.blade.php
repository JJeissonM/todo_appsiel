
<table class="table table-bordered">
	<thead>
		<tr>
			<th> Datos del estudiante </th>
			<th> Datos del responsable financiero </th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<b> Nombre: </b> {{ $estudiante->tercero->descripcion }}
				<br>
				<b> Cod. Matrícula: </b> {{ $estudiante->matricula_activa()->codigo }}
				<br>
				<b> Curso: </b>{{ $estudiante->matricula_activa()->curso->descripcion }}
			</td>
			<td>
				<b> Nombre: </b> {{ $estudiante->responsable_financiero()->tercero->descripcion }}
				<br>
				<b> Cédula: </b> {{ number_format( $estudiante->responsable_financiero()->tercero->numero_identificacion, 0, ',', '.' ) }}
				<br>
				<b> Dirección: </b> {{ $estudiante->responsable_financiero()->tercero->direccion1 }}
				<br>
				<b> Teléfono: </b> {{ $estudiante->responsable_financiero()->tercero->telefono1 }}
			</td>
		</tr>
	</tbody>
</table>
