<br><br>
<div class="marco_formulario">
	<div class="container-fluid">		
		<h4 style="width: 100%;text-align: center;">
			<strong> Consolidado de prestaciones sociales </strong>
		</h4>

		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th colspan="10">
						
					</th>
				</tr>
				<tr style="font-weight: bold;">
					<th> Empleado / CC </th>
					<th> Días laborados </th>
					<th> Prestación </th>
					<th> Fecha final promedios </th>
					<th> Acum. mes anterior </th>
					<th> Vlr. pagado mes </th>
					<th> Vlr. consol. mes </th>
					<th> Días consol. </th>
					<th> Vlr. acumulado </th>
					<th> Estado </th>
				</tr>
			</thead>
			<tbody>
				@foreach( $lista_consolidados as $fila )
					<?php

						$clase_color = '';
						if ( $fila->estado != 'Activo' )
						{
							$clase_color = 'warning';
						}

						if ( is_null( $fila->tercero ) )
						{
							$tercero_descripcion = $fila->contrato->tercero->descripcion;
							$tercero_numero_identificacion = $fila->contrato->tercero->numero_identificacion;
						}else{
							$tercero_descripcion = $fila->tercero->descripcion;
							$tercero_numero_identificacion = $fila->tercero->numero_identificacion;
						}
					?>
					<tr class="{{$clase_color}}">
						<td> {{ $tercero_descripcion }} / {{ number_format( $tercero_numero_identificacion, 0, ',', '.' ) }} </td>
						<td> {{ number_format( $fila->dias_totales_laborados, 0, ',', '.' ) }} </td>
						<td> {{ $fila->tipo_prestacion }} </td>
						<td> {{ $fila->fecha_fin_mes }} </td>
						<td> ${{ number_format( $fila->valor_acumulado_mes_anterior, 0, ',', '.' ) }} </td>
						<td> ${{ number_format( $fila->valor_pagado_mes, 0, ',', '.' ) }} </td>
						<td> ${{ number_format( $fila->valor_consolidado_mes, 0, ',', '.' ) }} </td>
						<td> {{ number_format( $fila->dias_consolidado_mes, 2, ',', '.' ) }} </td>
						<td> ${{ number_format( $fila->valor_acumulado, 0, ',', '.' ) }} </td>
						<td> {{ $fila->estado }} </td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>