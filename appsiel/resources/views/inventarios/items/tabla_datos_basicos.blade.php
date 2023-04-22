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
				<b>Referencia:</b> {{ $registro->referencia }}
			</td>
			<td>
				<b>Descripción:</b> {{ $registro->descripcion }}
			</td>
			<td>
				<b>Categoría:</b> {{ $registro->grupo_inventario->descripcion }}
			</td>
			<td>
				<b>Tipo Prenda:</b> {{ $registro->tipo_prenda->descripcion }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Material:</b> {{ $registro->tipo_material->descripcion }}
			</td>
			<td>
				<b>Color:</b> {{ $registro->paleta_color->descripcion }}
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
	</table>
</div>