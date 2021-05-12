<h5>
	Resumen de operaciones 
	<small>
		<button style="border: 0; background: transparent; display: none;" title="Mostrar" id="btn_mostrar_resumen_operaciones">
			<i class="fa fa-eye"></i>
		</button>
		<button style="border: 0; background: transparent;" title="Ocultar" id="btn_ocultar_resumen_operaciones">
			<i class="fa fa-eye-slash"></i>
		</button>
	</small>
</h5>
<div id="div_resumen_operaciones">
	<hr>
	<table class="table table-bordered table-striped">
		<tbody>
			<tr>
				<td><b>Efectivo:</b></td>
				<td id="valor_total_efectivo2" align="right" width="200px;">$ 0</td>
				<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
				<td><b>Documentos de cartera:</b></td>
				<td id="total_valor_documentos_cartera" align="right" width="200px;">$ 0</td>
			</tr>
			<tr>
				<td><b>Ctas. Bancarias:</b></td>
				<td id="valor_total_cuentas_bancarias" align="right">
					$ 0
				</td>
					<input type="hidden" name="input_valor_total_cuentas_bancarias" id="input_valor_total_cuentas_bancarias" value="0">
				<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
				<td><b>Acreditaciones:</b></td>
				<td id="valor_total_acreditaciones" align="right">$ 0</td>
			</tr>
			<tr>
				<td><b>Cheques:</b></td>
				<td id="valor_total_cheques2" align="right">$ 0</td>
				<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
				<td><b>Otras operaciones:</b></td>
				<td id="valor_total_otras_operaciones" align="right">
					$ 0
				</td>
					<input type="hidden" name="input_valor_total_otras_operaciones" id="input_valor_total_otras_operaciones" value="0">
			</tr>
			<tr>
				<td><b>Retenciones:</b></td>
				<td id="valor_total_retencion2" align="right">$ 0</td>
				<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
				<td colspan="2" style="background-color: #ddd;"> &nbsp; </td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td id="valor_total_resumen_medios_pagos" align="right">$ 0</td>
				<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
				<td>&nbsp;</td>
				<td id="valor_total_resumen_operaciones" align="right">
					$ 0
				</td>
					<input type="hidden" name="input_valor_total_resumen_medios_pagos" id="input_valor_total_resumen_medios_pagos" value="0">
					<input type="hidden" name="input_valor_total_resumen_operaciones" id="input_valor_total_resumen_operaciones" value="0">
			</tr>
		</tfoot>
	</table>

	<table class="table table-bordered">
		<tr>
			<td align="right" colspan=""><b>Diferencia:</b></td>
			<td id="valor_diferencia" align="right" width="200px;">$ 0</td>
			<input type="hidden" name="input_valor_diferencia" id="input_valor_diferencia" value="0">
		</tr>
	</table>
</div>