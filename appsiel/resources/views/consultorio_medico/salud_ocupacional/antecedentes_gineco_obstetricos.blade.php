<br>
<b>5. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
	//dd($datos);
?>

<table class="table">
	<tbody>
		<tr>
			<td>
				<table class="table">
					<tr>
						<td> {{ $campos[0]['descripcion'] }}: {{ $datos[0]->valor }} </td>
						<td> {{ substr( $campos[1]['descripcion'], 0, 1) }}: {{ $datos[1]->valor }} </td>
						<td> {{ substr( $campos[2]['descripcion'], 0, 1) }}: {{ $datos[2]->valor }} </td>
						<td> {{ substr( $campos[3]['descripcion'], 0, 1) }}: {{ $datos[3]->valor }} </td>
						<td> {{ substr( $campos[4]['descripcion'], 0, 1) }}: {{ $datos[4]->valor }} </td>
						<td> {{ $campos[5]['descripcion'] }}: {{ $datos[5]->valor }} </td>
						<td> {{ $campos[6]['descripcion'] }}: {{ $datos[6]->valor }} </td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class="table">
					<tr>
						<td> {{ $campos[7]['descripcion'] }}: {{ $datos[7]->valor }}  </td>
						<td> {{ $campos[8]['descripcion'] }}: {{ $datos[8]->valor }} </td>
						<td> {{ $campos[9]['descripcion'] }}: {{ $datos[9]->valor }}  </td>
						<td> {{ $campos[10]['descripcion'] }}: {{ $datos[10]->valor }}  </td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>