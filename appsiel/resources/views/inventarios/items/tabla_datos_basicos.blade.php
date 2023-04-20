<div class="table-responsive">
	<h5 style="width: 100%; text-align: center;">Datos básicos</h5>
	<table class="table table-bordered">
		<tr>
			<td rowspan="3" width="120px">

				<?php
					$url_imagen = url('/') . '/assets/img/box.png';
					if ( $registro->imagen != '') {
	                    $url_imagen = config('configuracion.url_instancia_cliente')."/storage/app/inventarios/".$registro->imagen;
	                }
	                $imagen = '<img alt="imagen.jpg" src="'.asset($url_imagen).'" style="width: 100px; height: 100px;" />';
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