<div class="table-responsive">
	<h5 style="width: 100%; text-align: center;">Datos básicos</h5>
	<table class="table table-bordered">
		<tr>
			<td rowspan="3" width="120px">

				<?php
					if ( $registro->imagen == '') {
	                    $campo_imagen = 'avatar.png';
	                }else{
	                    $campo_imagen = $registro->imagen;
	                }
	                $url = config('configuracion.url_instancia_cliente')."/storage/app/inventarios/".$campo_imagen.'?'.rand(1,1000);
	                $imagen = '<img alt="imagen.jpg" src="'.asset($url).'" style="width: 100px; height: 100px;" />';
	            ?>

				{!! $imagen !!}
			</td>
		</tr>
		<tr>
			<td>
				<b>Descripción:</b> {{ $registro->descripcion }}
			</td>
			<td>
				<b>Unidad medida:</b> {{ $registro->unidad_medida1 }}
			</td>
			<td>
				<b>Categoría:</b> {{ $registro->grupo_inventario->descripcion }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Precio compra:</b> ${{ number_format( $registro->precio_compra, 0, ',', '.' ) }}
			</td>
			<td>
				<b>Precio venta:</b> ${{ number_format( $registro->precio_venta, 0, ',', '.' ) }}
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
	</table>
</div>