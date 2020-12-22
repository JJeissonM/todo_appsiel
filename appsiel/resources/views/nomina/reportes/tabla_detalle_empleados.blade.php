<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> Empleado </th>
			<th> Valor Deducción </th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$gran_total = 0; 
			//dd($movimiento);
			$id_anterior = 0;
			$valor_deduccion = 0;
			$fila_cerrada = true;
			$fila_abierta = false;
			$iteracion = 1;
		?>
		@foreach($movimiento_entidad AS $key => $linea_mov_entidad)
			<?php
				//dd($linea_mov_entidad);
				if ( $id_anterior == (int)$linea_mov_entidad['nom_contrato_id'] )
				{
					// ID iguales 
					$valor_deduccion += $linea_mov_entidad['valor_deduccion'];
				}else{

					// ID diferentes (Es el primer registro o cambió)
					if ( $fila_cerrada )
					{
						$valor_deduccion = $linea_mov_entidad['valor_deduccion'];
						$empleado = App\Nomina\NomContrato::find( (int)$linea_mov_entidad['nom_contrato_id'] );
						$imprime_descripcion = true;
						//dd($empleado->tercero->descripcion);
					}						
				}
			?>
				
			@if( $fila_abierta )
					<td> {{ Form::TextoMoneda( $valor_deduccion ) }} </td>
				</tr>
				<?php 
					$fila_cerrada = true;
					$fila_abierta = false;

					// Los datos del registro actual se imprimen en la siguiente iteración
					$empleado = App\Nomina\NomContrato::find( (int)$linea_mov_entidad['nom_contrato_id'] );
					$valor_deduccion = $linea_mov_entidad['valor_deduccion'];
				?>
			@endif

			@if($imprime_descripcion)
				<tr>
					<td> {!! $empleado->tercero->descripcion !!} </td>
					<?php 
						$fila_cerrada = false;
						$fila_abierta = true;
						$imprime_descripcion = false;
					?>
			@endif

			<?php 

				if ($iteracion == 2)
				{
					//dd($iteracion, $valor_deduccion, $id_anterior, (int)$linea_mov_entidad['nom_contrato_id']);
				}

				$iteracion++;
			?>

			<?php 
				$id_anterior = (int)$linea_mov_entidad['nom_contrato_id'];
			?>
		@endforeach
	</tbody>
</table>