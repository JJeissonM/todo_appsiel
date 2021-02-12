<table class="table table-bordered">
	<tr>
		<td colspan="4">
			<p align="center">
				<span style="font-size: 1.6em; font-weight: bold;">Historia Clínica</span>
				<br>
				No. {{ $datos_historia_clinica->codigo }}
			</p>
		</td>
	</tr>
	@if( !isset( $mostrar_avatar ))
		<tr>
			<td rowspan="6" width="160px">

				<?php
					if ( $datos_historia_clinica->imagen == '') {
	                    $campo_imagen = 'avatar.png';
	                }else{
	                    $campo_imagen = $datos_historia_clinica->imagen;
	                }
	                $url = config('configuracion.url_instancia_cliente')."/storage/app/fotos_terceros/".$campo_imagen.'?'.rand(1,1000);
	                $imagen = '<img alt="imagen.jpg" src="'.asset($url).'" style="width: 140px; height: 160px;" />';
	            ?>

				{!! $imagen !!}
			</td>
		</tr>
	@else
		<tr>
			<td rowspan="6">
				&nbsp;
			</td>
		</tr>
	@endif
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
	</tr>
	<tr>
		<td>
			<b>Dirección:</b> {{ $datos_historia_clinica->direccion1 }}
		</td>
		<td>
			<b>Teléfono:</b> {{ $datos_historia_clinica->telefono1 }}
		</td>
		<td>
			<b>Email:</b> {{ $datos_historia_clinica->email }}
		</td>
	</tr>
	<tr>
		<td>
			<b>Nivel académico:</b> {{ $datos_historia_clinica->nivel_academico }}
		</td>
		<td>
			<b>Ocupación:</b> {{ $datos_historia_clinica->ocupacion }}
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr>
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
		<td>
			&nbsp;
		</td>
	</tr>
</table>