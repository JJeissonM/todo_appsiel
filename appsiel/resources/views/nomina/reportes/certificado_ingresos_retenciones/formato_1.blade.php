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


<table class="table table-bordered">
	<tr>
		<td style="width: 350px; text-align: center;">
			Período de la Certificación
			<br>
			30. DE: {{ explode('-', $fecha_inicio_periodo)[0] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_inicio_periodo)[1] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_inicio_periodo)[2] }} &nbsp;&nbsp;&nbsp;
			31. A: {{ explode('-', $fecha_fin_periodo)[0] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_fin_periodo)[1] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_fin_periodo)[2] }} &nbsp;&nbsp;&nbsp;
		</td>
		<td>
			32. Fecha de expedición
			<br>
			{{ explode('-', $fecha_expedicion)[0] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_expedicion)[1] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_expedicion)[2] }} &nbsp;&nbsp;&nbsp;
		</td>
		<td>
			33. Lugar donde se practicó la retención
			<br>
			{{ $ciudad->descripcion }}
		</td>
		<td style="width: 40px;">
			34. Cód. Dpto.
			<br>
			{{ $ciudad->core_departamento_id }}
		</td>
		<td style="width: 50px;">
			35. Cód. Ciudad/ Municipio
			<br>
			{{ substr( $ciudad->id, 5 ) }}
		</td>
	</tr>
</table>