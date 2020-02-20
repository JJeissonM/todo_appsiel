<table style="border: 1px solid gray; border-radius: 6px; margin-left: 7px;">
	<tr> 
		<td rowspan="2" align="center"> 
			<?php
				$url = '../storage/app/logos_empresas/'.$empresa->imagen;
			?>
			<img alt="escudo.jpg" src="{{ $url.'?'.rand(1,1000) }}" style="width: 90px; height: 90px;" />
		</td> 
		<td align="center"> 
			{{ $empresa->descripcion }} 
		</td>
	</tr>
	<tr> 
		<td> 
			<table> 
				<tr> 
					<td colspan="3" style="border: 1px solid gray; border-radius: 6px; text-align: center;">FECHA DE PAGO </td> 
				</tr>	
				<tr> 
					<td style="border: 1px solid gray; border-radius: 6px;"> Día: </td> 
					<td style="border: 1px solid gray; border-radius: 6px;"> Mes: </td> 
					<td style="border: 1px solid gray; border-radius: 6px;"> Año: </td> 
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray; border-radius: 6px; height: 25px;">
			<b>ALUMNO:</b>&nbsp;&nbsp;{{ substr($nombre_completo,0,28) }}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table> 
				<tr> 
					<td align="center" style="border: 1px solid gray; border-radius: 6px;"> <b>CURSO</b> </td> 
					<td align="center" style="border: 1px solid gray; border-radius: 6px;"> <b>MES</b> </td> 
				</tr>	
				<tr> 
					<td align="center" style="border: 1px solid gray; border-radius: 6px;"> {{ strtoupper($nom_curso) }} </td> 
					<td align="center" style="border: 1px solid gray; border-radius: 6px;"> {{ strtoupper($mes) }} </td> 
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray; border-radius: 6px;">
			<b>CONCEPTO:</b>&nbsp;&nbsp;PENSIÓN DE ESTUDIOS
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray; border-radius: 6px;">
			<b>TOTAL A PAGAR:</b>&nbsp;&nbsp;{{ $valor_pension_mensual }} {{ $valor_pension_mensual_letras }} 
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray; border-radius: 6px;">
			Cuenta {{ $tipo_cuenta }} {{ $entidad_financiera }} No. {{ $numero_cuenta }} 
		</td>
	</tr>
</table>
<br/>
<div align="center"> 
	<?php
		if ( $codigo_matricula == ' ') {
			echo $etiqueta;
		}else{
			echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($codigo_matricula, "C128B") . '" alt="barcode"   /><br/>'.$etiqueta.' '.$codigo_matricula;
		} 
	?> 
</div>