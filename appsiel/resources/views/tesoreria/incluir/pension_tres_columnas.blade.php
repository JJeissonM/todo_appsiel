<table style="border: 1px solid gray; margin-left: 7px; font-size: 12px; border-collapse: collapse;">
	<tr> 
		<td align="center"> 
			<?php
				$url = '../storage/app/logos_empresas/'.$empresa->imagen;
			?>
			<img alt="escudo.jpg" src="{{ $url.'?'.rand(1,1000) }}" style="width: 60px; height: 60px;" />
		</td> 
		<td align="center"> 
			{{ $empresa->descripcion }} 
			<br>
			{{ $empresa->direccion1 }}, {{ $empresa->telefono1 }} 
		</td>
	</tr>
	<tr> 
		<td colspan="2" style="border: 1px solid gray; text-align: center;"> 
			<span style="font-weight: bold; font-size: 0.9em;"> Fecha de pago </span> 
			<br>
			Día &nbsp; | Mes &nbsp; | Año
			<br>
			&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray; height: 25px;">
			<b>Estudiante:</b>&nbsp;&nbsp;{{ substr($nombre_completo,0,28) }}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table> 
				<tr> 
					<td align="center" style="border: 1px solid gray;"> <b>CURSO</b> </td> 
					<td align="center" style="border: 1px solid gray;"> <b>MES</b> </td> 
				</tr>	
				<tr> 
					<td align="center" style="border: 1px solid gray;"> {{ strtoupper($nom_curso) }} </td> 
					<td align="center" style="border: 1px solid gray;"> {{ strtoupper($mes) }} </td> 
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray;">
			<b>CONCEPTO:</b>&nbsp;&nbsp;PENSIÓN DE ESTUDIOS
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray;">
			<b>TOTAL A PAGAR:</b>&nbsp;&nbsp;{{ $valor_pension_mensual }} {{ $valor_pension_mensual_letras }} 
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray;">
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