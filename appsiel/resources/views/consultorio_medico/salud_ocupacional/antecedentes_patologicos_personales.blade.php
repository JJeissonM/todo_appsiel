<br>
<b>3. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th> COD. </th>
			<th> ALTERACIONES </th>
			<th> P </th>
			<th> F </th>
			<th> COD. </th>
			<th> ALTERACIONES </th>
			<th> P </th>
			<th> F </th>
			<th> COD. </th>
			<th> ALTERACIONES </th>
			<th> P </th>
			<th> F </th>
		</tr>
	</thead>
	<tbody>
		@for($i=0;$i<6;$i++)
			<tr>
				<td> {{ explode( " ", $campos[0 + $i]['descripcion'] )[0] }} </td>
				<td> {{ substr( $campos[0 + $i]['descripcion'], 3) }} </td>
				<td> </td>
				<td> </td>
				<td> {{ explode( " ", $campos[6 + $i]['descripcion'] )[0] }} </td>
				<td> {{ substr( $campos[6 + $i]['descripcion'], 3) }} </td>
				<td> </td>
				<td> </td>
				<td> {{ explode( " ", $campos[12 + $i]['descripcion'] )[0] }} </td>
				<td> {{ substr( $campos[12 + $i]['descripcion'], 3) }} </td>
				<td> </td>
				<td> </td>
			</tr>
		@endfor
		<tr>
			<td colspan="12">
				{{ $campos[18]['descripcion'] }}:
				<br><br>
			</td>
		</tr>
	</tbody>
</table>