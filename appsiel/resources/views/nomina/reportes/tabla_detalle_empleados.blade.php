<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> Empleado </th>
			<th> Valor Deducción </th>
		</tr>
	</thead>
	<tbody>
		@foreach( $movimiento_entidad AS $tercero )
			<tr>
				<td> {!! $tercero->descripcion_tercero !!} </td>
				<td> {{ Form::TextoMoneda( $tercero->total_deduccion ) }} </td>
			</tr>
		@endforeach
	</tbody>
</table>