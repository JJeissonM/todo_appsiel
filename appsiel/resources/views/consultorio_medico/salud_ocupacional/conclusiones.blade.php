<br>
<b>7. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd($campos->toArray());
	//dd( $datos );
?>

<p>
	<!-- <b>{ { $campos[0]['descripcion'] }}</b>
	<hr> -->
	<table class="table">
		<tbody>
			<tr>
				<td width="20%">APTO SIN LIMITACIONES </td>
				<td width="5%"><div style="width: 25px; height: 25px; text-align: center;"> <?php echo c_get_marcacion( $datos[0]->valor, 'Apto Sin Limitaciones' ); ?>  </div> </td>
				<td width="20%">APTO CON LIMITACIONES</td>
				<td width="5%"><div style="width: 25px; height: 25px; text-align: center;"> <?php echo c_get_marcacion( $datos[0]->valor, 'Apto Con Limitaciones' ); ?>  </div> </td>
				<td width="8%">NO APTO</td>
				<td width="5%"><div style="width: 25px; height: 25px; text-align: center;"> <?php echo c_get_marcacion( $datos[0]->valor, 'No Apto' ); ?>  </div> </td>
				<td width="8%">APLAZADO</td>
				<td><div style="width: 25px; height: 25px; text-align: center;"> <?php echo c_get_marcacion( $datos[0]->valor, 'Aplazado' ); ?>  </div> </td>
			</tr>
		</tbody>
	</table>
</p>

<p style="border: 1px solid; padding: 5px;">
	<b>{{ $campos[1]['descripcion'] }}</b>
	<br>
	{{ $datos[1]->valor }}
</p>

<p style="border: 1px solid; padding: 5px;">
	<b>{{ $campos[2]['descripcion'] }}</b>
	<br>
	{{ $datos[2]->valor }}
</p>

<p style="justify-content: all;">
	Declaro que la información que he suministrado al médico para el cumplimiento correcto de este examen, es verídica y me hago responsable de cualquier inexactitud en el suministro de ella.
</p>

<?php 
	
	function c_get_marcacion( $valor, $texto_campo )
	{
		$marcacion = '<input type="checkbox" style="transform: scale(1.5);">';
		if ( $valor == $texto_campo )
		{
			//$marcacion = '&#10008;';
			$marcacion = '<input type="checkbox" style="transform: scale(1.5);" checked="checked">';
		}
		return $marcacion;
	}
?>