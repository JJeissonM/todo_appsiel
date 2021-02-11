<br><br>
<div class="marco_formulario">
	<div class="container-fluid">		
		<h4 style="width: 100%;text-align: center;">
			<strong> Cálculo del porcentaje fijo para Procedimiento 2 de ReteFuente </strong>
		</h4>

		<table class="table table-striped">
			<thead>
				<tr>
					<th colspan="10">
						
					</th>
				</tr>
				<tr style="font-weight: bold;">
					<th> Empleado / CC </th>
					<th> Fecha final promedios </th>
					<th> Vlr. base depurada </th>
					<th> Renta trabajo exenta (25%) </th>
					<th> Subtotal </th>
					<th> Base retención ($) </th>
					<th> Base retención (UVT)</th>
					<th> Rango tabla retenciones (Art. 383)</th>
					<th> Vlr. retención UVT </th>
					<th> Porcentaje fijo </th>
				</tr>
			</thead>
			<tbody>
				@foreach( $empleados_con_retefuente as $fila )
					<tr>
						<td> {{ $fila->empleado->tercero->descripcion }} / {{ number_format( $fila->empleado->tercero->numero_identificacion, 0, ',', '.' ) }} </td>
						<td> {{ $fila->fecha_final_promedios }} </td>
						<td> ${{ number_format( $fila->valor_base_depurada, 0, ',', '.' ) }} </td>
						<td> ${{ number_format( $fila->renta_trabajo_exenta, 0, ',', '.' ) }} </td>
						<td> ${{ number_format( $fila->sub_total, 0, ',', '.' ) }} </td>
						<td> ${{ number_format( $fila->base_retencion_pesos, 0, ',', '.' ) }} </td>
						<td> {{ number_format( $fila->base_retencion_uvts, 0, ',', '.' ) }} </td>
						<td> {{ $fila->rango_tabla }} </td>
						<td> {{ number_format( $fila->valor_retencion_uvts, 0, ',', '.' ) }} </td>
						<td> {{ number_format( $fila->porcentaje_fijo, 2, ',', '.' ) }}% </td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>