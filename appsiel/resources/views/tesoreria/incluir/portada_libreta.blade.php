<table style="border: 1px solid gray; border-radius: 6px; margin-left: 5px;">
	<tr> 
		<td align="center"> 
			<?php
				$url = '../storage/app/logos_empresas/'.$empresa->imagen;
			?>
			<img alt="escudo.jpg" src="{{ $url.'?'.rand(1,1000) }}" style="width: 100px; height: 110px;" /> 
		</td> 
		<td align="center"> 
			<h3>
			{{ $empresa->descripcion }} <br/>
			NIT: {{ number_format($empresa->numero_identificacion, 0, ',', '.') }} - {{ $empresa->digito_verificacion }}<br/>
			{{ $colegio->slogan }} <br/>
			Aprobado según resolución No. {{ $colegio->resolucion }} <br/>
			{{ $colegio->direccion }} {{ $colegio->telefonos }} {{ $colegio->ciudad }}
			</h3>
		</td>
	</tr>
	<tr>
		<td style="border: 1px solid gray; border-radius: 6px; height: 25px;">
			&nbsp;<b>MATRÍCULA No.</b> &nbsp; {{$codigo_matricula}} 
		</td>
		<td style="border: 1px solid gray; border-radius: 6px; height: 25px;">
			<b>CURSO:</b>&nbsp;&nbsp;{{ strtoupper($nom_curso) }}
		</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid gray; border-radius: 6px; height: 25px;">
			<b>ALUMNO:</b>&nbsp;&nbsp;{{$nombre_completo}}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table> 
				<tr> 
					<td align="center" style="border: 1px solid gray; border-radius: 6px; height: 25px;"> 
						<b>Valor Matricula</b>
						<br/>
						{{ $valor_matricula }} {{ $valor_matricula_letras }}
					</td> 
					<td align="center" style="border: 1px solid gray; border-radius: 6px; height: 25px;"> 
						<b>Valor pensión</b>
						<br/>
						{{ $valor_pension_mensual }} {{ $valor_pension_mensual_letras }}
					</td> 
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center" style="border: 1px solid gray; border-radius: 6px; height: 60px;">
			Consigne el valor de su pensión los 5 (cinco) primeros días de cada mes.
			<br/> 
			Cuenta {{ $tipo_cuenta }} {{ $entidad_financiera }} No. {{ $numero_cuenta }} 
		</td>
	</tr>
</table>