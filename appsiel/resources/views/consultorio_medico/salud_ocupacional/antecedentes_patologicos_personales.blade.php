<br>
<b>3. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$url_imagen = asset('assets/images/icono-check.png');
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
				<td width="25px"> {{ explode( " ", $campos[0 + $i]['descripcion'] )[0] }} </td>
				<td> {{ substr( $campos[0 + $i]['descripcion'], 3) }} </td>
				<?php echo get_celdas_p_f( $datos[0+$i]->valor, $url_imagen ); ?>

				<td width="25px"> {{ explode( " ", $campos[6 + $i]['descripcion'] )[0] }} </td>
				<td> {{ substr( $campos[6 + $i]['descripcion'], 3) }} </td>
				<?php echo get_celdas_p_f( $datos[6+$i]->valor, $url_imagen ); ?>
				
				<td width="25px"> {{ explode( " ", $campos[12 + $i]['descripcion'] )[0] }} </td>
				<td> {{ substr( $campos[12 + $i]['descripcion'], 3) }} </td>
				<?php echo get_celdas_p_f( $datos[12+$i]->valor, $url_imagen ); ?>
			</tr>
		@endfor
		<tr>
			<td colspan="12">
				{{ $campos[18]['descripcion'] }}:
				<br>
				{{ $datos[18]->valor }}
			</td>
		</tr>
	</tbody>
</table>

<?php 
	
	function get_celdas_p_f( $valor, $url_imagen )
	{
		$celdas = '<td>--</td><td>--</td>';
		if ( $valor == 'Personal' )
		{
			$celdas = '<td align="center"><img src="' . $url_imagen . '"  height="15" style="margin-left:-15px;"></td><td></td>';
		}
		if ( $valor == 'Familiar' )
		{
			$celdas = '<td></td><td align="center"><img src="' . $url_imagen . '"  height="15" style="margin-left:-15px;"></td>';
		}
		return $celdas;
	}
?>