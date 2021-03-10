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

<table class="table">
	<tbody>
		<tr>
			<td>
				<table class="table">
					<tr>
						<td> {{ $campos[0]['descripcion'] }}: ____________ </td>
						<td> {{ substr( $campos[1]['descripcion'], 0, 1) }} ___ </td>
						<td> {{ substr( $campos[2]['descripcion'], 0, 1) }} ___</td>
						<td> {{ substr( $campos[3]['descripcion'], 0, 1) }} ___</td>
						<td> {{ substr( $campos[4]['descripcion'], 0, 1) }} ___</td>
						<td> {{ $campos[5]['descripcion'] }}: ____________</td>
						<td> {{ $campos[6]['descripcion'] }}: ____________</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table class="table">
					<tr>
						<td> {{ $campos[7]['descripcion'] }}: ____________ </td>
						<td> {{ $campos[8]['descripcion'] }}: &nbsp;&nbsp; SI ____ &nbsp;&nbsp; NO ____ </td>
						<td> {{ $campos[9]['descripcion'] }}: ____________ </td>
						<td> {{ $campos[10]['descripcion'] }}: ____________ </td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>