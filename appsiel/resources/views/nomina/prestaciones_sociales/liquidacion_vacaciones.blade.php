<div class="marco_formulario">
	<div class="container-fluid">
		
		<h4 style="width: 100%;text-align: center;">
			<strong>Liquidación de Vacaciones</strong>
		</h4>

		@include( 'nomina.incluir.tabla_datos_empleado', compact( 'empleado' ) )
		<?php
			
			$agrupacion = '';
			if ( $tabla_resumen['descripcion_agrupacion'] != '' )
			{
				$agrupacion = '<strong>Agrupación: </strong>' . $tabla_resumen['descripcion_agrupacion'];
			}

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
				<tr>
					<td><strong>Desde: </strong> {{ $tabla_resumen['fecha_inicial_promedios'] }} </td>
					<td><strong>Hasta: </strong> {{ $tabla_resumen['fecha_final_promedios'] }} </td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Base sueldo: </strong> ${{ number_format( $tabla_resumen['valor_acumulado_salario'],'0',',','.' ) }} </td>
					<td><strong>Cant. días: </strong>{{ number_format( $tabla_resumen['cantidad_dias_salario'],2,',','.' ) }}</td>
					<td><strong>Base diaria: </strong> ${{ number_format( $tabla_resumen['valor_salario_x_dia'],'0',',','.' ) }} </td>
				</tr>
				@if( $tabla_resumen['base_liquidacion'] != 'sueldo' )
					<tr>
						<td><strong>Base agrupación: </strong> ${{ number_format( $tabla_resumen['valor_acumulado_agrupacion'],'0',',','.' ) }} </td>
						<td><strong>Cant. días: </strong>{{ number_format( $tabla_resumen['cantidad_dias'],2,',','.' ) }}</td>
						<td><strong>Base diaria: </strong> ${{ number_format( $tabla_resumen['valor_agrupacion_x_dia'],'0',',','.' ) }} </td>
					</tr>
					<tr>
						<td><strong>Base total: </strong> ${{ number_format( $base_total,'0',',','.' ) }} </td>
						<td><strong>Cant. días: </strong>{{ $cantidad_dias }}</td>
						<td><strong>Base diaria: </strong> ${{ number_format( $base_diaria,'0',',','.' ) }} </td>
					</tr>
				@endif
				<tr>
					<td colspan="3" style="text-align: center; background-color: #ddd;"><strong>Datos de liquidación (Causación)</strong></td>
				</tr>
				<tr>
					<td rowspan="2">
						<strong>Periodo causado: </strong> 
						<br>
						<b>Desde: </b> {{ $tabla_resumen['periodo_pagado_desde'] }}
						<br>
						<b>Hasta: </b> {{ $tabla_resumen['periodo_pagado_hasta'] }}
					</td>
					<td><strong>Días totales vacac.: </strong> {{ number_format( $tabla_resumen['dias_totales_vacaciones'],2,',','.' ) }} </td>
					<td><strong>Días pagados: </strong> {{ number_format( $tabla_resumen['dias_pagados_vacaciones'],2,',','.' ) }} </td>
				</tr>
				<tr>
					<td><strong>Días Laborados / No Laborados: </strong> {{ number_format( $tabla_resumen['dias_totales_laborados'],0,',','.' ) }} / {{ number_format( $tabla_resumen['dias_totales_no_laborados'],0,',','.' ) }} </td>
					<td><strong>Días vacac. pendientes: </strong> {{ number_format( $tabla_resumen['dias_pendientes'],2,',','.' ) }} </td>
				</tr>
				<tr>
					<td colspan="3" style="text-align: center; background-color: #ddd;"><strong>Disfrute de vacaciones</strong></td>
				</tr>
				<tr>
					<td><strong>Fecha: </strong> {{ $tabla_resumen['fecha_inicial_disfrute'] }} </td>
					<td><strong>Hasta: </strong> {{ $tabla_resumen['fecha_final_disfrute'] }} </td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Días hábiles: </strong> {{ number_format( $tabla_resumen['dias_habiles'],0,',','.' ) }} </td>
					<td><strong>Días No hábiles: </strong> {{ number_format( $tabla_resumen['dias_no_habiles'],0,',','.' ) }} </td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Vlr. días hábiles: </strong> ${{ number_format( $tabla_resumen['vlr_dias_habiles'],'0',',','.' ) }} </td>
					<td><strong>Vlr. días No hábiles: </strong>{{ number_format( $tabla_resumen['vlr_dias_no_habiles'],'0',',','.' ) }}</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3"><strong>Total vacaciones: </strong> ${{ number_format( $tabla_resumen['valor_total_vacaciones'],'0',',','.' ) }} </td>
				</tr>
			</tbody>
		</table>
	</div>
</div>