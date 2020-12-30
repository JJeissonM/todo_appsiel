<br><br>
<div class="marco_formulario">
	<div class="container-fluid">
		
		<h4 style="width: 100%;text-align: center;">
			<strong>Liquidación Retefuente. <br>Procedimiento 2 (artículo 386 del estatuto tributario)</strong>
		</h4>

		<table class="table" style="border: 1px solid; border-collapse: collapse; width:100%; font-size: 12px;">
            <tr>
                <td style="border: 1px solid;"> <b> Empleado: </b> {{ $empleado->tercero->descripcion }}</td>
                <td style="border: 1px solid;"> <b> Cargo: </b> {{ $empleado->cargo->descripcion }}</td>
                <td style="border: 1px solid;"> {{ Form::TextoMoneda( $empleado->sueldo, 'Sueldo: ') }} </td>
            </tr>
            <tr>
                <td style="border: 1px solid;"><b> Fecha ingreso: </b> {{ $empleado->fecha_ingreso }}</td>
                <td style="border: 1px solid;" colspan="2">
                    <b> E.P.S.: </b> {{ $empleado->entidad_salud->descripcion }}
                    &nbsp;&nbsp; | &nbsp;&nbsp;
                    <b> A.F.P.: </b> {{ $empleado->entidad_pension->descripcion }}
                    &nbsp;&nbsp; | &nbsp;&nbsp;
                    <b> A.R.L.: </b> {{ $empleado->entidad_arl->descripcion }}
                </td>
            </tr>
        </table>

		<table class="table table-striped">
			<tbody>
				<tr>
					<td colspan="2">
						
					</td>
				</tr>
				<tr>
					<td><strong>Conceptos</strong></td>
					<td><strong>Valores</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center;"></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center; background-color: #ddd;"><strong>PAGOS EFECTUADOS</strong></td>
				</tr>
				<tr>
					<td>Sumatoria de los salarios básicos</td>
					<td>${{ number_format( $tabla_resumen['salario_basico'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Horas extras, bonificaciones y comisiones pagadas</td>
					<td>${{ number_format( $tabla_resumen['otros_devengos'], '0', ',', '.') }}</td>
				</tr>
				<!-- <tr>
				<td>{ { number_format( $tabla_resumen[''], '0', ',', '.') }}</td>
				<td>Auxilios y subsidios pagados durante los 12 meses anteriores (directo o indirecto)</td>
				</tr> -->
				<tr>
					<td>Prestaciones sociales</td>
					<td>${{ number_format( $tabla_resumen['prestaciones_sociales'], '0', ',', '.') }}</td>
				</tr>
				<!-- <tr>
					<td>{ { number_format( $tabla_resumen[''], '0', ',', '.') }}</td>
					<td>Demás pagos ordinario o extraordinario realizados durante los 12 meses anteriores</td>
				</tr>-->
				<tr>
					<td><strong>Total pagos efectuados</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['total_pagos'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center;"></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center; background-color: #ddd;"><strong>DEDUCCIONES</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center;"><strong>Deducciones por ingresos no constitutivos de renta</strong></td>
				</tr>
				<!-- <tr>
					<td>{ { number_format( $tabla_resumen[''], '0', ',', '.') }}</td>
					<td>Pagos a terceros por concepto de alimentación (limitado según artículo 387-1 ET)</td>
				</tr>
				<tr>
					<td>{ { number_format( $tabla_resumen[''], '0', ',', '.') }}</td>
					<td>Viáticos ocasionales que constituyen reembolso de gastos soportados</td>
				</tr>
				<tr>
					<td>{ { number_format( $tabla_resumen[''], '0', ',', '.') }}</td>
					<td>Medios de transporte distintos del subsidio de transporte</td>
				</tr> -->
				<tr>
					<td>Aportes obligatorios a salud</td>
					<td>${{ number_format( $tabla_resumen['aportes_salud_obligatoria'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Aportes obligatorios a fondos de pensiones (incluye fondo de soliradidad pensional)</td>
					<td>${{ number_format( $tabla_resumen['aportes_pension_obligatoria'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Cesantía e intereses sobre cesantía. Pagos expresamente excluidos por el artículo 386 estatuto tributario.</td>
					<td>${{ number_format( $tabla_resumen['pagos_cesantias_e_intereses'], '0', ',', '.') }}</td>
				</tr>
				<!-- <tr>
					<td>{ { number_format( $tabla_resumen[''], '0', ',', '.') }}</td>
					<td>Demás pagos que no incrementan el patrimonio del trabajador</td>
				</tr>-->
				<tr>
					<td><strong>Total ingresos no constitutivos de renta</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['total_ingresos_no_constitutivos_renta'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center;"><strong>Deducciones por Rentas exentas</strong></td>
				</tr>
				<tr>
					<td>Aportes voluntarios a fondos de pensiones</td>
					<td>${{ number_format( $tabla_resumen['aportes_pension_voluntaria'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Ahorros cuentas AFC</td>
					<td>${{ number_format( $tabla_resumen['ahorros_cuentas_afc'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Rentas de trabajo exentas (Numerales del 1 al 9 artículo 206 ET)</td>
					<td>${{ number_format( $tabla_resumen['rentas_trabajo_exentas'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td><strong>Total por rentas exentas</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['total_rentas_exentas'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center;"><strong>Deducciones particulares</strong></td>
				</tr>
				<tr>
					<td>Intereses o corrección monetaria en préstamos para adquisición de vivienda</td>
					<td>${{ number_format( $tabla_resumen['intereses_vivienda'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Pagos a salud (medicina prepagada y pólizas de seguros)</td>
					<td>${{ number_format( $tabla_resumen['salud_prepagada'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td>Deducción por dependientes (Art. 387-1 ET)</td>
					<td>${{ number_format( $tabla_resumen['deduccion_por_dependientes'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td><strong>Total deducciones particulares</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['total_deducciones_particulares'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td><strong>Total Deducciones</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['total_deducciones'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Subtotal</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['subtotal'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td>Renta de trabajo exenta del 25% (Numeral 10 del artículo 206 ET)</td>
					<td>${{ number_format( $tabla_resumen['renta_trabajo_exenta'], '0', ',', '.') }}</td>
				</tr>
				<tr>
					<td><strong>Base de retención</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['base_retencion'], '0', ',', '.') }}</strong></td>
				</tr>
				<tr>
					<td><strong>Porcentaje fijo de retención </strong></td>
					<td><strong>{{ number_format( $tabla_resumen['porcentaje_aplicado'], '2', ',', '.') }}%</strong></td>
				</tr>
				<tr class="success">
					<td><strong>Valor total retención</strong></td>
					<td><strong>${{ number_format( $tabla_resumen['valor_liquidacion'], '0', ',', '.') }}</strong></td>
				</tr>
			</tbody>
		</table>
</div>
</div>