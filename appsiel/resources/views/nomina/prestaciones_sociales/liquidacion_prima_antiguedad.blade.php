<div class="marco_formulario">
	<div class="container-fluid">
		
		<h4 style="width: 100%;text-align: center;">
			<strong>Liquidación Prima de Antigüedad</strong>
			<br>
			{{ $tabla_resumen['descripcion_prestacion'] }}
		</h4>

		@include( 'nomina.incluir.tabla_datos_empleado', compact( 'empleado' ) )
		<?php
			
			$agrupacion = '';

			$base_total = $tabla_resumen['valor_acumulado_salario'] + $tabla_resumen['valor_acumulado_agrupacion'];
			$cantidad_dias = number_format( $tabla_resumen['cantidad_dias_salario'] + $tabla_resumen['cantidad_dias'],2,',','.' );
			$base_diaria = $tabla_resumen['valor_salario_x_dia'] + $tabla_resumen['valor_agrupacion_x_dia'];

			if ( $tabla_resumen['base_liquidacion'] == 'sueldo_mas_promedio_agrupacion' )
			{
				$cantidad_dias = '--';
			}
		?>

		<table class="table">
			<tbody>
				<tr>
					<td colspan="3" style="text-align: center; background-color: #ddd;"><strong>Valores acumulados</strong></td>
				</tr>
				<tr>
					<td><strong>Base de liquidación: </strong> {{ $tabla_resumen['base_liquidacion'] }} </td>
					<td colspan="2">{!! $agrupacion !!} </td>
				</tr>
				@if( $tabla_resumen['base_liquidacion'] != 'promedio_agrupacion' )
					<tr>
						<td><strong>Base sueldo: </strong> ${{ number_format( $tabla_resumen['valor_acumulado_salario'],'0',',','.' ) }} </td>
						<td><strong>Cant. días: </strong>{{ number_format( $tabla_resumen['cantidad_dias_salario'],2,',','.' ) }}</td>
						<td><strong>Base diaria: </strong> ${{ number_format( $tabla_resumen['valor_salario_x_dia'],'0',',','.' ) }} </td>
					</tr>
				@endif
				<tr>
					<td colspan="3" style="text-align: center; background-color: #ddd;"><strong>Datos de liquidación</strong></td>
				</tr>
				<tr>
					<td><strong>Fecha liquidación: </strong> {{ $tabla_resumen['fecha_liquidacion'] }} </td>
					<td><strong>Días de servicio: </strong> {{ number_format( $tabla_resumen['dias_totales_laborados'],0,',','.' ) }} </td>
					<td><strong>Días liquidados: </strong> {{ number_format( $tabla_resumen['dias_totales_liquidacion'],2,',','.' ) }} </td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3"><strong>Total liquidación: </strong> ${{ number_format( $tabla_resumen['valor_total_liquidacion'],'0',',','.' ) }} </td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="page-break"></div>