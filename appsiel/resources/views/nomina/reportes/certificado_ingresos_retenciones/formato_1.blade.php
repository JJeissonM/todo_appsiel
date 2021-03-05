<style type="text/css">
	.table{
		margin-top: -2px;
	}
	.fila_concepto{
		/*line-height: 0;*/
		padding: 2px !important;
		vertical-align: middle !important;
	}

	.celda_valor{
		text-align: right;
	}

	.celda_numero_indicador{
		width: 30px;
		text-align: center;
	}
</style>

<br>
@include('nomina.reportes.certificado_ingresos_retenciones.encabezado')

<table class="table table-bordered">
	<tr>
		<td width="60%" style="text-align: center;">
			Antes de diligenciar este formulario lea cuidadosamente las instrucciones
		</td>
		<td width="40%">
			4. Número de formulario
		</td>
	</tr>
</table>
<?php

	//dd($empleado->tercero);
?>
@include('nomina.reportes.certificado_ingresos_retenciones.datos_retenedor')

@include('nomina.reportes.certificado_ingresos_retenciones.datos_trabajador')

@include('nomina.reportes.certificado_ingresos_retenciones.linea_fechas_ciudad')

@include('nomina.reportes.certificado_ingresos_retenciones.datos_concepto_de_los_ingresos')

@include('nomina.reportes.certificado_ingresos_retenciones.datos_concepto_de_los_aportes')

@include('nomina.reportes.certificado_ingresos_retenciones.datos_a_cargo_del_trabajador')

@include('nomina.reportes.certificado_ingresos_retenciones.datos_bienes_poseidos')

@include('nomina.reportes.certificado_ingresos_retenciones.datos_identificacion_dependiente_economico')

<table class="table table-bordered" style="font-size: 10px;">
	<tr>
		<td width="60%">
			Certifico que durante el año gravable 2020:
			<br>
			&nbsp;&nbsp;1. Mi patrimonio bruto no excedió de 4.500 UVT ($160.232.000).
			<br>
			&nbsp;&nbsp;2. Mis ingresos brutos fueron inferiores a 1.400 UVT ($49.850.000).
			<br>
			&nbsp;&nbsp;3. No fui responsable del impuesto sobre las ventas.
			<br>
			&nbsp;&nbsp;4. Mis consumos mediante tarjeta de crédito no excedieron la suma de 1.400 UVT ($49.850.000).
			<br>
			&nbsp;&nbsp;5. Que el total de mis compras y consumos no superaron la suma de 1.400 UVT ($49.850.000).
			<br>
			&nbsp;&nbsp;6. Que el valor total de mis consignaciones bancarias, depósitos o inversiones financieras no excedieron los 1.400 UVT ($49.850.000).
			<br>
			&nbsp;&nbsp;Por lo tanto, manifiesto que no estoy obligado a presentar declaración de renta y complementario por el año gravable 2020.
		</td>
		<td width="40%">
			Firma del Trabajador o Pensionado
		</td>
	</tr>
</table>

<p style="font-size: 10px;">
	<b>NOTA:</b> este certificado sustituye para todos los efectos legales la declaración de Renta y Complementario para el trabajador o pensionado que lo firme.
Para aquellos trabajadores independientes contribuyentes del impuesto unificado deberán presentar la declaración anual consolidada del Régimen Simple de Tributación (SIMPLE).
</p>
<!-- 
<iframe src="{ {asset('assets/nomina/anexo_certificado_ingresos_retencion_fmto_220_2020.pdf')}}" style="width: 100%;"></iframe>
-->