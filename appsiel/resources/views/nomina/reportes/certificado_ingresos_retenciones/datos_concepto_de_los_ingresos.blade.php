
<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th width="70%">
				Concepto de los Ingresos
			</th>
			<th colspan="2">
				Valor
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="fila_concepto">
				Pagos por salarios o emolumentos eclesiásticos
			</td>
			<td class="celda_numero_indicador">
				36
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente->tabla_resumen['salario_basico'] + $retefuente->tabla_resumen['otros_devengos'],0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos realizados con bonos electrónicos o de papel de servicio, cheques, tarjetas, vales, etc.
			</td>
			<td class="celda_numero_indicador">
				37
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por honorarios
			</td>
			<td class="celda_numero_indicador">
				38
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por servicios
			</td>
			<td class="celda_numero_indicador">
				39
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por comisiones
			</td>
			<td class="celda_numero_indicador">
				40
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por prestaciones sociales
			</td>
			<td class="celda_numero_indicador">
				41
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente->tabla_resumen['prestaciones_sociales'] - $retefuente->tabla_resumen['pagos_cesantias_e_intereses'],0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por viáticos
			</td>
			<td class="celda_numero_indicador">
				42
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por gastos de representación
			</td>
			<td class="celda_numero_indicador">
				43
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pagos por compensaciones por el trabajo asociado cooperativo
			</td>
			<td class="celda_numero_indicador">
				44
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Otros pagos
			</td>
			<td class="celda_numero_indicador">
				45
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Cesantías e intereses de cesantías efectivamente pagadas en el periodo
			</td>
			<td class="celda_numero_indicador">
				46
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente->tabla_resumen['pagos_cesantias_e_intereses'],0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				Pensiones de jubilación, vejez o invalidez
			</td>
			<td class="celda_numero_indicador">
				47
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td class="fila_concepto">
				<b>Total de ingresos brutos</b> (Sume 36 a 47)
			</td>
			<td class="celda_numero_indicador">
				48
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente->tabla_resumen['salario_basico'] + $retefuente->tabla_resumen['otros_devengos'] + $retefuente->tabla_resumen['prestaciones_sociales'],0,',','.') }}
			</td>
		</tr>
	</tbody>
</table>