
<table style="width: 100%;">
	<tbody>
		<tr style="text-align: center;">
			<td>
				_________________________________________________
				<br>
				Firma Odontólogo
				<br>
				Registro Médico No. {{ $consulta->profesional_salud->numero_carnet_licencia }}
			</td>
			<td>
				__________________________________ 
				<br>
				Firma del trabajador
				<br>
				C.C. No. {{ number_format( $consulta->paciente->tercero->numero_identificacion,0, ',','.') }}
				<br>
			</td>
		</tr>
	</tbody>
</table>