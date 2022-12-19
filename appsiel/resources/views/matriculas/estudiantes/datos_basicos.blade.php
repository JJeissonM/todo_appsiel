
<table class="table table-bordered table-striped">
	<tr>
		<td colspan="2"><h3> <strong>Datos básicos del estudiante</strong> </h3></td>
	</tr>
	<tr>
		<?php 
			$curso_label = 'Sin matricula registrada';
			if($estudiante->matriculas->last() != null)
			{
				$curso_label = $estudiante->matriculas->last()->curso->descripcion . ' - ' .  $estudiante->matriculas->last()->periodo_lectivo->descripcion;
			}
		?>
		<td colspan="2"><strong>Curso actual: </strong> {{ $curso_label }} </td>
	</tr>

	<tr>
		<td><strong>Nombre: </strong> {{ $estudiante->nombre_completo }}</td>
		<td rowspan="6" align="center">
			<?php
				if ( $estudiante->imagen != '' )
				{
					$src = asset( config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen );
				}else{
					$src = asset( 'assets/images/avatar.png/' );
				}
			?>
			<img alt="foto.jpg" src="{{ $src }}" style="width: 140px; height: 170px;" />
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
		<td colspan="2"><strong>Email: </strong>{{ $estudiante->email }}</td>
	</tr>
</table>

@include('matriculas.estudiantes.datos_basicos_padres',['vista'=>'show'])

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