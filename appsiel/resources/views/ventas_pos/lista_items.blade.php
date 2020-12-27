<div class="row">
	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped" id="myTable">
			<thead>
				<tr>
					<th>Cód.</th>
					<th>Producto (U.M.)</th>
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
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>