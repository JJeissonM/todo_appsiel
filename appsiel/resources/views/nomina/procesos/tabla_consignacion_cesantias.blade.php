<br><br>
<div class="marco_formulario">
	<div class="container-fluid">		
		<h4 style="width: 100%;text-align: center;">
			<strong> Resumen de Cesantías Liquidadas </strong>
		</h4>

		<table class="table table-striped">
			<thead>
				<tr style="font-weight: bold;">
					<th> Tipo documeto </th>
					<th> Número documento </th>
					<th> Primer apellido </th>
					<th> Segundo apellido </th>
					<th> Primer nombre </th>
					<th> Segundo nombre </th>
					<th> Código fondo cesantías destino </th>
					<th> Número días trabajados </th>
					<th> Salario básico </th>
					<th> Valor cesantías </th>
				</tr>
			</thead>
		</table>

		<table class="table table-striped table_registros">
			<tbody>
				@foreach( $lineas_consignacion as $fila )
					<tr>
						<td> {{ $fila->tipo_documeto }} </td>
						<td> {{ $fila->numero_documento }} </td>
						<td> {{ $fila->apellido1 }} </td>
						<td> {{ $fila->apellido2 }} </td>
						<td> {{ $fila->nombre1 }} </td>
						<td> {{ $fila->otros_nombres }} </td>
						<td> ="{{ $fila->codigo_fondo_cesantias_destino }}" </td>
						<td> {{ $fila->numero_dias_trabajados }} </td>
						<td> {{ $fila->salario_basico }} </td>
						<td> {{ $fila->valor_cesantias }} </td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>