<?php
	$nombre_completo=$estudiante->nombres." ".$estudiante->apellido1." ".$estudiante->apellido2;
	
	use App\Http\Controllers\Matriculas\EstudianteController;
	$nom_curso = EstudianteController::nombre_curso($estudiante->id);
	$sql_matricula = App\Matriculas\Matricula::where('id_estudiante','=',$estudiante->id)->where('estado','=','Activo')->get();

	if (count($sql_matricula)>0) {
		$matricula = $sql_matricula[0];
		$cedula_acudiente = $matricula->cedula_acudiente;
		$acudiente = $matricula->acudiente;
		$telefono_acudiente = $matricula->telefono_acudiente;
		$email_acudiente = $matricula->email_acudiente;
		$advertencia = "";
	}else{
		$advertencia = '<br/><div class="alert alert-danger">
						  <strong>Advertencia!</strong> El estudiante no tiene ninguna matrícula activa.
						</div>';
		$cedula_acudiente = "";
		$acudiente = "";
		$telefono_acudiente = "";
		$email_acudiente = "";
	}

	$doc=DB::table('tipos_documentos_id')->where('codigo',$estudiante->tipo_doc_id)->value('abreviatura');
?>

<table class="table table-bordered table-striped">
	<tr>
		<td colspan="2"><strong><h3>Datos básicos del estudiante</h3></strong>{!! $advertencia !!}</td>
	</tr>	
	<tr>
		<td><strong>Nombre: </strong> {{ $nombre_completo }}</td>
		<td rowspan="7" align="center">
			<img alt="foto.jpg" src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen.'?'.rand(1,1000)) }}" style="width: 130px; height: 180px;" />
		</td>
	</tr>
	<tr>
		<td><strong>Curso: </strong>{{ $nom_curso }}</td>
	</tr>
	<tr>
		<td><strong>Doc. Identidad: </strong>{{ $doc }} {{ $estudiante->doc_identidad }}</td>
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
		<td colspan="2" style="border: none;">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: none;">
			<?php 
				echo tabla_datos('Papá', $estudiante->papa, $estudiante->ocupacion_papa, $estudiante->telefono_papa, $estudiante->email_papa);
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
				echo tabla_datos('Mamá', $estudiante->mama, $estudiante->ocupacion_mama, $estudiante->telefono_mama, $estudiante->email_mama);
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
			<table>
				<tr>
					<td colspan="6" style="background-color: #ddd;"><strong>Datos del Acudiente</strong></td>
				</tr>
				<tr>
					<td width="30">Nombre</td> 
					<td width="5">:</td> 
					<td> {{$acudiente}}</td>
					<td width="30">Cédula</td> 
					<td width="5">:</td> 
					<td>{{ $cedula_acudiente }}</td>
				</tr>
				<tr>
					<td width="30">Teléfono</td> 
					<td width="5">:</td> 
					<td>{{ $telefono_acudiente }}</td>
					<td width="30">E-mail</td> 
					<td width="5">:</td> 
					<td>{{ $email_acudiente }}</td>
				</tr>
			</table>
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

function tabla_datos($lbl, $nombre, $ocupacion, $telefono, $email)
{
	return '<table>
				<tr>
					<td colspan="6" style="background-color: #ddd;"><strong>Datos del '.$lbl.'</strong></td>
				</tr>
				<tr>
					<td width="30">Nombre</td> 
					<td width="5">:</td> 
					<td>'.$nombre.'</td>
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
				</tr>
			</table>';
}
?>