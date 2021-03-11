<br>
<b>1. IDENTIFICACIÓN </b>
<div style="width: 100%; text-align: right;">
	<b>FECHA EXAMEN</b>
	<br>
	{{ $consulta->fecha }}
</div>
<table class="table table-bordered">
	<tr>
		<td>
			<b>Nombres:</b> {{ $datos_historia_clinica->nombres }}
		</td>
		<td>
			<b>Apellidos:</b> {{ $datos_historia_clinica->apellidos }}
		</td>
		<td>
			<b>Identificación:</b> {{ number_format( $datos_historia_clinica->numero_identificacion, 0, ',', '.' ) }}
		</td>
		<td>
			<b>Cod. HC:</b> {{ $datos_historia_clinica->codigo }}
		</td>
	</tr>
	<tr>
		<td>
			<b>Género:</b> {{ $datos_historia_clinica->genero }}
		</td>
		<td>
			<b>Fecha de nacimiento: </b>{{ $datos_historia_clinica->fecha_nacimiento }} ( {{ \Carbon\Carbon::parse($datos_historia_clinica->fecha_nacimiento)->diff(\Carbon\Carbon::now())->format('%y años') }} )
		</td>
		<td>
			<b>Estado civil: </b>{{ $datos_historia_clinica->estado_civil }}
		</td>
		<td>
			<b>Escolaridad:</b> {{ $datos_historia_clinica->nivel_academico }}
		</td>
	</tr>
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
	<tr>
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
		<td colspan="2">
			<b>Remitido por:</b> {{ $remitido_por }}
		</td>
	</tr>
</table>
<?php 

	$datos_laborales = $datos_historia_clinica->datos_laborales();

	$cant_cols = 4;
	$i = $cant_cols;
?>
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