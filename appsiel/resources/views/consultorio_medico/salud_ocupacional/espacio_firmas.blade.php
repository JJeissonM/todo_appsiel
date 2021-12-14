
<table style="width: 100%;">
	<tbody>
		<tr style="text-align: center;">
			<td>
				
				<?php
					$firma = "_________________________________________________";
					if ( !is_null($firma_autorizada) ) 
					{
						if ( $firma_autorizada->imagen != "") 
						{
							$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada->imagen;	
							$firma = '<img src="'.$url_firma.'" width="250px" height="70px" style="margin-bottom: -20px;"/>';
						}						
					}
				?>
				{!! $firma !!} <br>
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