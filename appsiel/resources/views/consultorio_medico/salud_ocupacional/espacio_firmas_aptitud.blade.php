<br><br><br><br>
<table style="width: 100%;">
	<tbody>
		<tr style="text-align: center;">
			<td>
				_________________________________________________
				<br>
				Firma del médico de Salud Ocupacional
				<br>
				Registro Médico No. {{ $consulta->profesional_salud->numero_carnet_licencia }}
				<br>
				Licencia de salud Ocupacional No. {{ $consulta->profesional_salud->licencia_salud_ocupacional }}
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