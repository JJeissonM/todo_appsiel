<div class="row">
	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped" id="myTable">
			<thead>
				<tr>
					<th>Cód.</th>
					<th>Producto (U.M.)</th>
					<th>Costo Prom.</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $productos as $item)
					<tr>
						<td class="table-text">
							<button onclick="mandar_codigo({{ $item->id }});" class="btn btn-info btn-sm">
								{{ $item->id }}
							</button>
						</td>
						<td class="table-text"><div>{{ $item->descripcion }}</div></td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->costo_promedio, 0, ',', '.' ) }}</div></td>
						<td class="table-text" style="text-align: right;"><div>${{ number_format( $item->costo_promedio, 0, ',', '.' ) }}</div></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>