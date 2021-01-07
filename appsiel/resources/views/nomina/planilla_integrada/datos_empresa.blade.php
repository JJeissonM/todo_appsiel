<?php 
	$datos_empresa = $planilla_generada->datos_empresa;
?>
<table class="table">
	<tr>
		<td>
			<b>Empresa</b> <br>{{ $datos_empresa->empresa->descripcion }}
		</td>
		<td>
			<b>Tipo aportante</b> <br>{{ $datos_empresa->tipo_aportante }}
		</td>
		<td>
			<b>Clase aportante</b> <br>{{ $datos_empresa->clase_aportante }}
		</td>
		<td>
			<b>Forma presentacion</b> <br>{{ $datos_empresa->forma_presentacion }}
		</td>
	</tr>
	<tr>
		<td>
			<b>tipo_persona</b> <br>{{ $datos_empresa->tipo_persona }}
		</td>
		<td>
			<b>naturaleza_juridica</b> <br>{{ $datos_empresa->naturaleza_juridica }}
		</td>
		<td>
			<b>actividad_economica_ciiu</b> <br>{{ $datos_empresa->actividad_economica_ciiu }}
		</td>
		<td>
			<b>representante_legal</b> <br>{{ $datos_empresa->representante_legal->descripcion }}
		</td>
	</tr>
	<tr>
		<td>
			<b>A.R.L.</b> <br>{{ $datos_empresa->entidad_arl->tercero->descripcion }}
		</td>
		<td>
			<b>Porcentaje SENA</b> <br>{{ $datos_empresa->porcentaje_sena }}%
		</td>
		<td>
			<b>Porcentaje ICBF</b> <br>{{ $datos_empresa->porcentaje_icbf }}%
		</td>
		<td>
			<b>Porcentaje CCF</b> <br>{{ $datos_empresa->porcentaje_caja_compensacion }}%
		</td>
	</tr>
</table>

