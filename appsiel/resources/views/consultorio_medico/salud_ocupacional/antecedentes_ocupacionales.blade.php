<b>2. {{ $modelo_sys->descripcion }}</b>

<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
?>
@if( !is_null($datos) )
	<table class="table table-bordered">
		<thead>
			<tr>
				@for($i=0;$i<5;$i++)
					<th> {{ $datos[$i]->descripcion }} </th>
				@endfor
			</tr>
		</thead>
		<tbody>
			<tr>
				@for($i=0;$i<5;$i++)
					<td> {{ $datos[$i]->valor }} </td>
				@endfor
			</tr>
			<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
			<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
		</tbody>
	</table>
@endif