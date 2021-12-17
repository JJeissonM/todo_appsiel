<br>
<?php 
	if ( $consulta->paciente->tercero->imagen == '')
	{
        $url_imagen = asset('assets/images/avatar.png');
    }else{
        $url_imagen = config('configuracion.url_instancia_cliente')."/storage/app/fotos_terceros/".$consulta->paciente->tercero->imagen;
    }
?>


<div style="width: 120px; height: 140px; position: absolute; top: 105px; right: 10; border: 1px solid; background: white; text-align: center;">
	<img src="{{ $url_imagen }}" style="width: 115px; height: 135px; display: inline; padding: 5px;">
</div>

<table class="table table-bordered" style="width: 80%;">
	<tr align="center">
		<td><b>Cod Historia Clínica</b></td>
		<td><b>Tipo de Evaluación Médica</b></td>
		<td><b>Fecha Examen: </b></td>
	</tr>
	<tr align="center">
		<td>{{ $datos_historia_clinica->codigo }}</td>
		<td>{{config('consultorio_medico.lbl_tipo_evaluacion_medica_ocupacional')}}</td>
		<td> {{ $consulta->fecha }} </td>
	</tr>
</table>
<br>
<table class="table table-bordered" style="width: 80% !important;">
	<tr>
		<td colspan="3">
			<b>Nombres completo:</b> {{ $datos_historia_clinica->nombres }} {{ $datos_historia_clinica->apellidos }}
		</td>
		<td>
			<b>Identificación:</b> {{ number_format( $datos_historia_clinica->numero_identificacion, 0, ',', '.' ) }}
		</td>
	</tr>
	<tr>
		<td>
			<b>Género:</b> {{ $datos_historia_clinica->genero }}
		</td>
		<td>
			<b>Estado civil: </b>{{ $datos_historia_clinica->estado_civil }}
		</td>
		<td colspan="2">
			<b>Fecha de nacimiento: </b>{{ $datos_historia_clinica->fecha_nacimiento }} ( {{ \Carbon\Carbon::parse($datos_historia_clinica->fecha_nacimiento)->diff(\Carbon\Carbon::now())->format('%y años') }} )
		</td>
	</tr>
</table>
<br>
<table class="table table-bordered">
	<tr>
		<td>
			<b>Dirección:</b> {{ $datos_historia_clinica->direccion1 }}
		</td>
		<td>
			<b>Teléfono:</b> {{ $datos_historia_clinica->telefono1 }}
		</td>
		<td colspan="2">
			<b>Email:</b> {{ $datos_historia_clinica->email }}
		</td>
	</tr>
</table>
<table class="table table-bordered">
	<tr>
		<td>
			<b>Escolaridad:</b> {{ $datos_historia_clinica->nivel_academico }}
		</td>
		<td>
			<b>Ocupación:</b> {{ $datos_historia_clinica->ocupacion }}
		</td>
			<?php
				$grupo_sanguineo = App\Salud\GrupoSanguineo::get_valor_campo( $datos_historia_clinica->grupo_sanguineo );
				$remitido_por = App\Salud\EntidadRemisora::get_valor_campo( $datos_historia_clinica->remitido_por );
			?>
		<td>
			<b>Grupo Sanguineo:</b> {{ $grupo_sanguineo }}
		</td>
		<td>
			<b>Remitido por:</b> {{ $remitido_por }}
		</td>
	</tr>
</table>
<?php 
	$datos_laborales = $datos_historia_clinica->datos_laborales();
	$cant_cols = 4;
	$i = $cant_cols;
?>
@if( !empty($datos_laborales->toArray()) )
	<table class="table table-bordered">
		<tbody>
			@foreach($datos_laborales as $registro_eav)
				
				@if($i % $cant_cols == 0)
					<tr>
				@endif

				<td>
					<b> {{ $registro_eav->campo->descripcion }}: </b> {{ $registro_eav->valor }}
				</td>

				<?php
					$i++;
				?>
				
				@if($i % $cant_cols == 0)
					</tr>
				@endif
			@endforeach
		</tbody>
	</table>
@endif