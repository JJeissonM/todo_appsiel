<?php
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
			<td rowspan="3" width="120px">

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
				<b>Tipo:</b> {{ $registro->tercero->tipo }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Cliente:</b> {{ $registro->tercero->descripcion }}
			</td>
			<td>
				<b>Razón social:</b> 
				@if( $registro->tercero->razon_social == '' )
					{{ $registro->tercero->nombre1 }} {{ $registro->tercero->otros_nombres }} {{ $registro->tercero->apellido1 }} {{ $registro->tercero->apellido2 }}
				@else
					{{ $registro->tercero->razon_social }}
				@endif
			</td>
			<td>
				<b>Identificación:</b> {{ number_format( $registro->tercero->numero_identificacion, 0, ',', '.' ) }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Dirección:</b> {{ $registro->tercero->direccion1 }}, {{ $registro->tercero->ciudad->descripcion }} - {{ $registro->tercero->ciudad->departamento->descripcion }}
			</td>
			<td>
				<b>Teléfono:</b> {{ $registro->tercero->telefono1 }}
			</td>
			<td>
				<b>Email:</b> {{ $registro->tercero->email }}
			</td>
		</tr>
	</table>
</div>