<div class="table-responsive">
	<?php
		$claseCliente = $registro->clase_cliente;
		$vendedor = $registro->vendedor;
		$zona = $registro->zona;
		$listaPrecios = $registro->lista_precios;
		$listaDescuentos = $registro->lista_descuentos;
		$condicionPago = $registro->condicion_pago;
	?>
	<h5 style="width: 100%; text-align: center;">Datos de ventas</h5>
	<table class="table table-bordered">
		<tr>
			<td>
				<b>Clase cliente:</b> {{ !is_null($claseCliente) ? $claseCliente->descripcion : 'Sin clase' }}
			</td>
			<td>
				<b>Vendedor:</b> {{ !is_null($vendedor) && !is_null($vendedor->tercero) ? $vendedor->tercero->descripcion : 'Sin vendedor' }}
			</td>
			<td>
				<b>Zona:</b> {{ !is_null($zona) ? $zona->descripcion : 'Sin zona' }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Lista de precios:</b> {{ !is_null($listaPrecios) ? $listaPrecios->descripcion : 'Sin lista' }}
			</td>
			<td>
				<b>Lista de descuentos:</b> {{ !is_null($listaDescuentos) ? $listaDescuentos->descripcion : 'Sin lista' }}
			</td>
			<td>
				<b>Condición de pago:</b> {{ !is_null($condicionPago) ? $condicionPago->descripcion : 'Sin condicion' }}
			</td>
		</tr>
	</table>
</div>
