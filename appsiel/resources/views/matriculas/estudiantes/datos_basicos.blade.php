<table class="table table-bordered table-striped">
	<tr>
		<td colspan="2"><h3> <strong>Datos básicos del estudiante</strong> </h3></td>
	</tr>	
	<tr>
		<td><strong>Nombre: </strong> {{ $estudiante->nombre_completo }}</td>
		<td rowspan="6" align="center">
			<img alt="foto.jpg" src="{{ asset( config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen ) }}" style="width: 130px; height: 180px;" />
		</td>
	</tr>
	<tr>
		<td><strong>Doc. Identidad: </strong> {{ $estudiante->tipo_y_numero_documento_identidad }}</td>
	</tr>
	<tr>
		<td><strong>Fecha nacimiento: </strong>{{ $estudiante->fecha_nacimiento }} <?php echo " (".calcular_edad($estudiante->fecha_nacimiento).")";?></td>
	</tr>
	<tr>
		<td><strong>Género: </strong>{{ $estudiante->genero }}</td>
	</tr>
	<tr>
		<td><strong>Dirección: </strong>{{ $estudiante->direccion1 }}</td>
	</tr>
	<tr>
		<td><strong>Teléfono: </strong>{{ $estudiante->telefono1 }}</td>
	</tr>
	<tr>
		<td><strong>Email: </strong>{{ $estudiante->email }}</td>
	</tr>
</table>

@include('matriculas.estudiantes.datos_basicos_padres')

<?php
	function calcular_edad($fecha_nacimiento)
	{
	    $datetime1 = new DateTime($fecha_nacimiento);
	    $datetime2 = new DateTime('now');
	    $interval = $datetime1->diff($datetime2);
	    $edad=$interval->format('%R%a');
	    return floor($edad/365)." Años";
	}
?>