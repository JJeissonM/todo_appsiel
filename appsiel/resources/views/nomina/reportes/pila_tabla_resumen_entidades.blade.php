<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> {{ config("configuracion.tipo_identificador") }} </th>
			<th> Entidad </th>
			<th> Cod. Nacional </th>
			<th> Valor aportado </th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$gran_total = 0;
		?>
		@foreach( $movimiento AS $registro )
			<tr>
				<td> {{ $registro->entidad->tercero->numero_identificacion }} </td>
				<td> {{ $registro->entidad->descripcion }} </td> 
				<td> {{ $registro->entidad->codigo_nacional }} </td>
				<td> {{ Form::TextoMoneda( $registro->total_cotizacion ) }} </td>
			</tr>
			<?php 
				$gran_total += $registro->total_cotizacion;
			?>
		@endforeach
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3"> &nbsp; </td>
			<td> {{ Form::TextoMoneda( $gran_total ) }} </td>
		</tr>
	</tfoot>
</table>