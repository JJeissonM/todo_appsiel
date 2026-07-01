<?php
	$tercero = $registro->tercero;
	$ciudad = !is_null($tercero) ? $tercero->ciudad : null;
	$departamento = !is_null($ciudad) ? $ciudad->departamento : null;

	$color = 'orange';
	if ($registro->estado == 'Activo') {
		$color = 'green';
	}
	if ($registro->estado == 'Inactivo') {
		$color = 'red';
	}
?>
<div class="table-responsive">
	<h5 style="width: 100%; text-align: center;">Datos básicos</h5>

	<div>
		<b> Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $registro->estado }}
	</div>

	<table class="table table-bordered">
		<tr>
			<td rowspan="4" width="120px">

				<?php
					if ( $registro->imagen == '') {
	                    $campo_imagen = 'avatar.png';
	                }else{
	                    $campo_imagen = $registro->imagen;
	                }
	                $url = config('configuracion.url_instancia_cliente')."/storage/app/fotos_terceros/".$campo_imagen.'?'.rand(1,1000);
	                $imagen = '<img alt="imagen.jpg" src="'.asset($url).'" style="width: 80px; height: 100px;" />';
	            ?>

				{!! $imagen !!}
			</td>
			</tr>
			<tr>
				<td colspan="3">
					<b>Tipo:</b> {{ !is_null($tercero) ? $tercero->tipo : '' }}
				</td>
			</tr>
			<tr>
				<td>
					<b>Cliente:</b> {{ !is_null($tercero) ? $tercero->descripcion : '' }}
				</td>
				<td>
					<b>Razón social:</b>
					@if( is_null($tercero) )
						Sin tercero
					@elseif( $tercero->razon_social == '' )
						{{ $tercero->nombre1 }} {{ $tercero->otros_nombres }} {{ $tercero->apellido1 }} {{ $tercero->apellido2 }}
					@else
						{{ $tercero->razon_social }}
					@endif
				</td>
				<td>
					<b>Identificación:</b> {{ !is_null($tercero) ? number_format( $tercero->numero_identificacion, 0, ',', '.' ) : '' }}
				</td>
			</tr>
			<tr>
				<td>
					<b>Dirección:</b> {{ !is_null($tercero) ? $tercero->direccion1 : '' }}{{ !is_null($ciudad) ? ', '.$ciudad->descripcion : '' }}{{ !is_null($departamento) ? ' - '.$departamento->descripcion : '' }}
				</td>
				<td>
					<b>Teléfono:</b> {{ !is_null($tercero) ? $tercero->telefono1 : '' }}
				</td>
				<td>
					<b>Email:</b> {{ !is_null($tercero) ? $tercero->email : '' }}
				</td>
			</tr>
	</table>
</div>
