<?php

	$escala = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->get();

	$tbody = '<table style="border: 1px solid; border-collapse: collapse; width:170px; font-size: 3.5mm;">
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
			@include('calificaciones.boletines.tabla_firmas')
		</td>
	</tr>
</table>