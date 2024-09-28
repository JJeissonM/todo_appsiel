<div class="row">
	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped" id="myTable">
			<thead>
				<tr>
					<th>Cód.</th>
					<th>Ref.</th>
					<th>Producto (U.M.)</th>
					<th>Precio Vta. <br> (IVA incluido)</th>
					<th>Costo Prom.</th>
					<th>Costo Prom. <br> (IVA incluido)</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $productos as $item)
					<?php 
						if ( $item->estado != 'Activo' ) {
							continue;
						}
					?>
					<tr>
						<td class="table-text">
							@if((int)config('ventas_pos.cerrar_modal_al_seleccionar_producto'))
								<button onclick="mandar_codigo3({{ $item->id }});" class="btn btn-info btn-sm">
									{{ $item->id }}
								</button>
							@else 
								<button onclick="mandar_codigo({{ $item->id }});" class="btn btn-info btn-sm">
									{{ $item->id }}
								</button>
							@endif
						</td>
						<td class="table-text"><div>{{ $item->referencia }}</div></td>
						<td class="table-text"><div>{{ $item->descripcion }}</div></td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->precio_venta, 0, ',', '.' ) }}</div></td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->costo_promedio, 0, ',', '.' ) }}</div></td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->costo_promedio_mas_iva, 0, ',', '.' ) }}</div></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>