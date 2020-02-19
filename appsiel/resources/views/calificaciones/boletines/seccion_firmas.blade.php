@if( $mostrar_escala_valoracion == 'Si') 
	@include('calificaciones.boletines.escala_valoracion')
@else
	<br/><br/><br/>

	<table border="0">
		<tr>
			<td width="50px"> &nbsp; </td>
			<td align="center">	
				@include('calificaciones.boletines.firmas_rector_profesor',[ 'i' => 0, 'nombre_archivo' => 'firma_rector'])
			</td>
			<td align="center"> &nbsp;	</td>
			<td align="center">
				@include('calificaciones.boletines.firmas_rector_profesor',[ 'i' => 1, 'nombre_archivo' => 'firma_profesor'])
			</td>
			<td width="50px">&nbsp;</td>
		</tr>
		<tr style="font-size: {{$tam_letra}}mm;">
			<td width="50px"> &nbsp; </td>
			<td align="center">	{{ $colegio->piefirma1 }} </td>
			<td align="center"> &nbsp;	</td>
			<td align="center">	{{ $colegio->piefirma2 }} </td>
			<td width="50px">&nbsp;</td>
		</tr>
	</table>
@endif