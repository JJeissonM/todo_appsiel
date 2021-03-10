<br>
<b>3. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
?>

<table class="table table-bordered">
	<tbody>
		<tr>
			<td> 19 </td>
			<td> {{ $campos[0]['descripcion'] }} </td>
			<td> SÍ </td>
			<td> </td>
			<td> NO </td>
			<td> </td>
			<td> {{ $campos[1]['descripcion'] }} </td>
			<td> </td>
			<td> {{ $campos[2]['descripcion'] }} </td>
			<td> </td>
			<td> </td>
			<td> </td>
		</tr>
		<tr>
			<td> 20 </td>
			<td> {{ $campos[3]['descripcion'] }} </td>
			<td> SÍ </td>
			<td> </td>
			<td> NO </td>
			<td> </td>
			<td> {{ $campos[4]['descripcion'] }} </td>
			<td> </td>
			<td> {{ $campos[5]['descripcion'] }} </td>
			<td> </td>
			<td> {{ $campos[6]['descripcion'] }} </td>
			<td> </td>
		</tr>
		<tr>
			<td> 21 </td>
			<td> {{ $campos[7]['descripcion'] }} </td>
			<td> SÍ </td>
			<td> </td>
			<td> NO </td>
			<td> </td>
			<td> {{ $campos[8]['descripcion'] }} </td>
			<td> </td>
			<td> {{ $campos[9]['descripcion'] }} </td>
			<td> </td>
			<td> {{ $campos[10]['descripcion'] }} </td>
			<td> </td>
		</tr>
		<tr>
			<td colspan="12">
				{{ $campos[11]['descripcion'] }}:
				<br><br>
			</td>
		</tr>
	</tbody>
</table>