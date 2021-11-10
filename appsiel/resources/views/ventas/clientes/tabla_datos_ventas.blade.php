<div class="table-responsive">
	<h5 style="width: 100%; text-align: center;">Datos de ventas</h5>
	<table class="table table-bordered">
		<tr>
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
	