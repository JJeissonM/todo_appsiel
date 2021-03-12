<?php 
	$modelo_padre_id = 96; // Consulta Médica
	$registro_modelo_padre_id = $consulta->id;

	//$campos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );
	$campos = $modelo_sys->campos()->where('name','core_campo_id-ID')->orderBy('id')->get();

	$datos = $modelo_seccion_historia_clinica->get_pares_campos_valores( $modelo_sys, $modelo_padre_id, $registro_modelo_padre_id );

	//dd( $datos );
?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th colspan="6">EXAMENES PARACLINICOS</th>
		</tr>
	</thead>
	<tbody>
		<?php $j = 0; ?>
		@for($i=0;$i<7;$i++)
			<tr>
				<td width="150"> {{ substr( $campos[$j]['descripcion'], 6 ) }} </td>
				<td width="50"> {{ $datos[$j+2]->valor }} </td>
				<td> {{ $datos[$j+3]->valor }} </td>
				<?php $j += 4; ?>
				@if( $i == 6 )
					<td colspan="3"> &nbsp; </td>
				@endif	
				<?php 
					if( $i == 6 )
						continue
				?>
				<td width="150"> {{ substr( $campos[$j]['descripcion'], 6 ) }} </td>
				<td width="50"> {{ $datos[$j+2]->valor }} </td>
				<td> {{ $datos[$j+3]->valor }} </td>
				<?php $j += 4; ?>
			</tr>
		@endfor
		<tr>
			<td>{{ $campos[60]['descripcion'] }}</td>
			<td colspan="5"> {{ $datos[60]->valor }} </td>
		</tr>
	</tbody>
</table>