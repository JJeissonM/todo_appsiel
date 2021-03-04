<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th width="70%">
				Concepto de los aportes
			</th>
			<th colspan="2">
				Valor
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				Aportes obligatorios por salud a cargo del trabajador
			</td>
			<td class="celda_numero_indicador">
				49
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente->tabla_resumen['aportes_salud_obligatoria'],0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Aportes obligatorios a fondos de pensiones y solidaridad pensional a cargo del trabajador
			</td>
			<td class="celda_numero_indicador">
				50
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente->tabla_resumen['aportes_pension_obligatoria'],0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Cotizaciones voluntarias al régimen de ahorro individual con solidaridad - RAIS
			</td>
			<td class="celda_numero_indicador">
				51
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Aportes voluntarios al impuesto solidario por COVID 19
			</td>
			<td class="celda_numero_indicador">
				52
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Aportes voluntarios a fondos de pensiones
			</td>
			<td class="celda_numero_indicador">
				53
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td>
				Aportes a cuentas AFC
			</td>
			<td class="celda_numero_indicador">
				54
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td style="background-color: #396395; color: white;">
				Valor de la retención en la fuente por ingresos laborales y de pensiones
			</td>
			<td class="celda_numero_indicador">
				55
			</td>
			<td class="celda_valor">
				${{ number_format( $retefuente_descontada, 0, ',', '.' ) }}
			</td>
		</tr>
		<tr>
			<td>
				Retenciones por aportes obligatorios al impuesto solidario por COVID 19
			</td>
			<td class="celda_numero_indicador">
				56
			</td>
			<td class="celda_valor">
				${{ number_format(0,0,',','.') }}
			</td>
		</tr>
		<tr>
			<td colspan="3">
				Nombre del pagador o agente retenedor
				<br>
				{{ $empresa->descripcion }}
			</td>
		</tr>
	</tbody>
</table>