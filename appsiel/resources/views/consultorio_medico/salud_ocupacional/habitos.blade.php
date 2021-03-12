<br>
<b>4. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$url_imagen = asset('assets/images/icono-check.png');
	//dd($campos->toArray());
?>

<table class="table table-bordered">
	<tbody>
		<tr>
			<td> 19 </td>
			<td> {{ $campos[0]['descripcion'] }} </td>
			<?php echo h_get_celdas_si_no( $datos[0]->valor, $url_imagen ); ?>
			<td> {{ $campos[1]['descripcion'] }} </td>
			<td> {{$datos[1]->valor}} </td>
			<td> {{ $campos[2]['descripcion'] }} </td>
			<td> {{ $datos[2]->valor }} </td>
			<td colspan="2"> </td>
		</tr>
		<tr>
			<td> 20 </td>
			<td> {{ $campos[3]['descripcion'] }} </td>
			<?php echo h_get_celdas_si_no( $datos[3]->valor, $url_imagen ); ?>
			<td> {{ $campos[4]['descripcion'] }} </td>
			<td> {{$datos[4]->valor}} </td>
			<td> {{ $campos[5]['descripcion'] }} </td>
			<td> {{$datos[5]->valor}} </td>
			<td> {{ $campos[6]['descripcion'] }} </td>
			<td> {{$datos[6]->valor}} </td>
		</tr>
		<tr>
			<td> 21 </td>
			<td> {{ $campos[7]['descripcion'] }} </td>
			<?php echo h_get_celdas_si_no( $datos[7]->valor, $url_imagen ); ?>
			<td> {{ $campos[8]['descripcion'] }} </td>
			<td> {{$datos[8]->valor}} </td>
			<td> {{ $campos[9]['descripcion'] }} </td>
			<td> {{$datos[9]->valor}} </td>
			<td> {{ $campos[10]['descripcion'] }} </td>
			<td> {{$datos[10]->valor}} </td>
		</tr>
		<tr>
			<td colspan="12">
				{{ $campos[11]['descripcion'] }}:
				<br>
				{{$datos[11]->valor}}
			</td>
		</tr>
	</tbody>
</table>

<?php
	function h_get_celdas_si_no( $valor, $url_imagen )
	{
		$celdas = '<td width="25px">SÍ</td><td width="25px">&nbsp;</td><td width="25px">NO</td><td align="center" width="25px"><img src="' . $url_imagen . '"  height="15" style="margin-left:-25px;"></td>';
		if ( $valor == 'No' )
		{
			$celdas = '<td width="25px">SÍ</td><td align="center" width="25px"><img src="' . $url_imagen . '"  height="15" style="margin-left:-25px;"></td><td width="25px">NO</td><td width="25px">&nbsp;</td>';
		}
		return $celdas;
	}
?>