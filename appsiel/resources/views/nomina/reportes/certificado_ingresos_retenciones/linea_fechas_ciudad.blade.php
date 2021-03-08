<table class="table table-bordered">
	<tr>
		<td width="28%">
			<div style="text-align: center;">
				Período de la Certificación
			</div>
			30. DE: {{ explode('-', $fecha_inicio_periodo)[0] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_inicio_periodo)[1] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_inicio_periodo)[2] }} &nbsp;&nbsp;&nbsp;
			31. A: {{ explode('-', $fecha_fin_periodo)[0] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_fin_periodo)[1] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_fin_periodo)[2] }} &nbsp;&nbsp;&nbsp;
		</td>
		<td width="20%">
			32. Fecha de expedición
			<br>
			{{ explode('-', $fecha_expedicion)[0] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_expedicion)[1] }} &nbsp;&nbsp;&nbsp;{{ explode('-', $fecha_expedicion)[2] }} &nbsp;&nbsp;&nbsp;
		</td>
		<td width="25%">
			33. Lugar donde se practicó la retención
			<br>
			{{ $ciudad->descripcion }}
		</td>
		<td width="10%">
			34. Cód. Dpto.
			<br>
			{{ $ciudad->core_departamento_id }}
		</td>
		<td width="17%">
			35. Cód. Ciudad/Municipio
			<br>
			{{ substr( $ciudad->id, 5 ) }}
		</td>
	</tr>
</table>