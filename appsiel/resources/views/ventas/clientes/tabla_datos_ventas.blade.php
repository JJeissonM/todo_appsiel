<div class="table-responsive">
	<table class="table table-bordered">
		<tr>
			<td colspan="4">
				<p align="center">
					<span style="font-size: 1.6em; font-weight: bold;">Datos de ventas</span>
				</p>
			</td>
		</tr>
		<tr>

			<?php 
				//dd( $registro, $registro->clase_cliente, $registro->vendedor, $registro->zona, $registro->lista_precios, $registro->lista_descuentos );
		?>
			<td>
				<b>Clase cliente:</b> {{ $registro->clase_cliente->descripcion }}
			</td>
			<td>
				<b>Vendedor:</b> {{ $registro->vendedor->tercero->descripcion }}
			</td>
			<td>
				<b>Zona:</b> {{ $registro->zona->descripcion }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Lista de precios:</b> {{ $registro->lista_precios->descripcion }}
			</td>
			<td>
				<b>Lista de descuentos:</b> {{ $registro->lista_descuentos->descripcion }}
			</td>
			<td>
				<b>Condición de pago:</b> {{ $registro->condicion_pago->descripcion }}
			</td>
		</tr>
	</table>
</div>
	