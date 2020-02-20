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
		<td colspan="2" style="border: 1px solid #ccc; border-radius: 6px; height: 25px; color: red; text-align: center; vertical-align: middle;">
			RECIBO DE PAGO DE MATRÍCULA
		</td>
	</tr>
	<tr>
		<td colspan="2"><div style="border: 1px solid #ccc; border-radius: 6px; height: 25px;"><b>ALUMNO:</b>&nbsp;&nbsp;{{ substr($nombre_completo,0,28) }}</div></td>
	</tr>
	<tr>
		<td colspan="2"><div style="border: 1px solid #ccc; border-radius: 6px; height: 25px;"><b>CURSO:</b>&nbsp;&nbsp;{{ strtoupper($nom_curso) }}</div></td>
	</tr>
	<tr>
		<td colspan="2"><div style="border: 1px solid #ccc; border-radius: 6px;"><b>TOTAL A PAGAR:</b>&nbsp;&nbsp;{{ $valor_matricula }} {{ $valor_matricula_letras }} </div></td>
	</tr>
	<tr>
		<td colspan="2"><div style="border: 1px solid #ccc; border-radius: 6px;" align="center">Cuenta {{ $tipo_cuenta }} {{ $entidad_financiera }} No. {{ $numero_cuenta }} </div></td>
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