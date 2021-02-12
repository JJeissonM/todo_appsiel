<?php 

	$datos_laborales = $datos_historia_clinica->datos_laborales();

	$cant_cols = 4;
	$i = $cant_cols;
?>

<h4> <i class="fa fa-wrench"></i> Datos Laborales </h4>
<table class="table table-bordered">
	<tbody>
		@foreach($datos_laborales as $registro_eav)
			
			@if($i % $cant_cols == 0)
				<tr>
			@endif

			<td>
				<b> {{ $registro_eav->campo->descripcion }}: </b> {{ $registro_eav->valor }}
			</td>

			<?php
				$i++;
			?>
			
			@if($i % $cant_cols == 0)
				</tr>
			@endif
		@endforeach
	</tbody>
</table>