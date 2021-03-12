<div class="page-break"></div>
<br>
<b>6. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th width="25px">COD.</th>
			<th>Tipo</th>
			<th>Fecha</th>
			<th>Laboratorio</th>
			<th>Resultado</th>
			<th>Anotación</th>
		</tr>
	</thead>
	<tbody>
		<?php $j = 0; ?>
		@for($i=0;$i<13;$i++)
			<tr>
				<td> {{ 22 + $i }} </td>
				<td> {{ substr( $campos[$j]['descripcion'], 6 ) }} </td>
				<td> {{ $datos[$j]->valor }} </td>
				<td> {{ $datos[$j+1]->valor }} </td>
				<td> {{ $datos[$j+2]->valor }} </td>
				<td> {{ $datos[$j+3]->valor }} </td>
			</tr>
			<?php $j += 4; ?>
		@endfor
	</tbody>
</table>

<table class="table table-bordered">
	<tbody>
		<tr>
			<td> {{ $campos[52]['descripcion'] }} </td>
			<td width="60px"> {{ $datos[52]->valor }} </td>
			<td> {{ $campos[54]['descripcion'] }} </td>
			<td width="60px"> {{ $datos[54]->valor }} </td>
			<td> {{ $campos[56]['descripcion'] }} </td>
			<td width="60px"> {{ $datos[56]->valor }} </td>
			<td> {{ $campos[58]['descripcion'] }} </td>
			<td width="60px"> {{ $datos[58]->valor }} </td>
		</tr>
		<tr>
			<td colspan="2">Fecha: &nbsp; {{ $datos[53]->valor }} </td>
			<td colspan="2">Fecha: &nbsp; {{ $datos[55]->valor }} </td>
			<td colspan="2">Fecha: &nbsp; {{ $datos[57]->valor }} </td>
			<td colspan="2">Fecha: &nbsp; {{ $datos[59]->valor }} </td>
		</tr>
	</tbody>
</table>
