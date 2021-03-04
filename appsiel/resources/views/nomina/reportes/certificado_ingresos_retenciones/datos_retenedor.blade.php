<table class="table table-bordered">
	<tr>
		<td style="width: 10px;" rowspan="2">
			<div style="vertical-align: center;">
				<span style="writing-mode: vertical-lr;transform: rotate(270deg);"> Retenedor </span>
			</div>
		</td>
		<td style="width: 230px;">
			5. Número de Identificación Tributaria (NIT)
			<br>
			{{ number_format( $empresa->numero_identificacion, 0, ',', '.' ) }}
		</td>
		<td style="width: 40px;">
			6. DV
			<br>
			{{ $empresa->digito_verificacion }}
		</td>
		<td>
			7. Primer apellido
			<br>
			{{ $empresa->apellido1 }}
		</td>
		<td>
			8. Segundo apellido
			<br>
			{{ $empresa->apellido2 }}
		</td>
		<td>
			9. Primer nombre 
			<br>
			{{ $empresa->nombre1 }}
		</td>
		<td>
			10. Otros nombres
			<br>
			{{ $empresa->otros_nombres }}
		</td>
	</tr>
	<tr>		
		<td colspan="6">
			11. Razón social
			<br>
			{{ $empresa->razon_social }}
		</td>
	</tr>
</table>