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
	<tr>
		<td colspan="2" style="border: none;">
			<?php 
				echo tabla_datos('Papá', $estudiante->papa, $estudiante->ocupacion_papa, $estudiante->telefono_papa, $estudiante->email_papa, $estudiante->cedula_papa);
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: none;">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: none;">
			<?php 
				echo tabla_datos('Mamá', $estudiante->mama, $estudiante->ocupacion_mama, $estudiante->telefono_mama, $estudiante->email_mama, $estudiante->cedula_);
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: none;">
			&nbsp;
		</td>
	</tr>
</table>

<?php
function calcular_edad($fecha_nacimiento)
{
    $datetime1 = new DateTime($fecha_nacimiento);
    $datetime2 = new DateTime('now');
    $interval = $datetime1->diff($datetime2);
    $edad=$interval->format('%R%a');
    return floor($edad/365)." Años";
}

function tabla_datos($lbl, $nombre, $ocupacion, $telefono, $email, $cedula)
{
	return '<table style="width: 100%;">
				<tr>
					<td colspan="9" style="background-color: #ddd;"><strong>Datos del '.$lbl.'</strong></td>
				</tr>
				<tr>
					<td width="30">Nombre</td> 
					<td width="5">:</td> 
					<td>'.$nombre.'</td>
					<td width="30">Cédula</td> 
					<td width="5">:</td> 
					<td>'.$cedula.'</td>
					<td width="30">Ocupación</td> 
					<td width="5">:</td> 
					<td>'.$ocupacion.'</td>
				</tr>
				<tr>
					<td width="30">Teléfono</td> 
					<td width="5">:</td> 
					<td>'.$telefono.'</td>
					<td width="30">E-mail</td> 
					<td width="5">:</td> 
					<td>'.$email.'</td>
					<td width="30"></td> 
					<td width="5"></td> 
					<td></td>
				</tr>
			</table>';
}
?>