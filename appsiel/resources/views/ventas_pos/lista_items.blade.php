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

						if (!(int)$item->grupo_inventario->mostrar_en_pagina_web)
						{
							continue;
						}

						if ( $item->estado != 'Activo' ) {
							continue;
						}

						$talla = '';
						if ( $item->unidad_medida2 != '' ) {
							$talla = ' - ' . $item->unidad_medida2;
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
						<td class="table-text"><div>{{ $item->descripcion . $talla }}</div></td>
						<td class="table-text" style="text-align: right;">
							<div>
								<span class="lbl_precio_item" data-item_id="{{ $item->id }}">
									${{ number_format( $item->precio_venta, 0, ',', '.' ) }}
								</span>
							</div>
						</td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->costo_promedio, 0, ',', '.' ) }}</div></td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->costo_promedio_mas_iva, 0, ',', '.' ) }}</div></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>