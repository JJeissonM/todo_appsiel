<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> {{ config("configuracion.tipo_identificador") }}  </th>
			<th> Entidad </th>
			<th> Cod. Nacional </th>
			<th> Valor aportado </th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td>
			<td> CCF (Caja de compensación Familiar) </td>
			<td></td>
			<td> {{ Form::TextoMoneda( $movimiento->sum('cotizacion_ccf') ) }} </td>
		</tr>
		<tr>
			<td></td>
			<td> SENA </td>
			<td></td>
			<td> {{ Form::TextoMoneda( $movimiento->sum('cotizacion_sena') ) }} </td>
		</tr>
		<tr>
			<td></td>
			<td> ICBF </td>
			<td></td>
			<td> {{ Form::TextoMoneda( $movimiento->sum('cotizacion_icbf') ) }} </td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td> {{ Form::TextoMoneda( $movimiento->sum('total_cotizacion') ) }} </td>
		</tr>
	</tfoot>
</table>