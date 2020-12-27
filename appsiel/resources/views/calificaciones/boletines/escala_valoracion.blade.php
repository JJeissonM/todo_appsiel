<?php

	$escala = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->get();

	$tbody = '<table style="border: 1px solid; border_collapsed: collapsed; width:170px; font-size: 3.5mm;">
				<tr>
					<td colspan="3" style="text-align:center;border: 1px solid;">Escala de valoración
					</td>
				</tr>
				<tr>
					<td style="text-align:center;border: 1px solid;">Desempeño</td>
					<td style="text-align:center;border: 1px solid;">Mín.</td>
					<td style="text-align:center;border: 1px solid;">Máx.</td>
				</tr>';
	foreach($escala as $linea)
	{
		$tbody.='<tr>
					<td style="border: 1px solid;">'.$linea->nombre_escala.'</td>
					<td style="text-align:center;border: 1px solid;">'.$linea->calificacion_minima.'</td>
					<td style="text-align:center;border: 1px solid;">'.$linea->calificacion_maxima.'</td>
				</tr>';
	}

	$tbody.='</table>';
	//echo $tbody;
?>
<br>

<table border="0">
	<tr>
		<td>
			{!! $tbody !!}
		</td>
		<td>
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
		</td>
	</tr>
</table>