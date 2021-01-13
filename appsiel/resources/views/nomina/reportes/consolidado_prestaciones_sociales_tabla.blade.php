<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> CC </th>
			<th> EMPLEADO </th>
			<th> Prestación </th>
			<th> Días laborados </th>
			<th> Días de derecho </th>
			<th> Base diaria </th>
			<th> Total provisión </th>
		</tr>
	</thead>
	<tbody>
		<?php
			$total_valor_provision = 0;
			$cc_empleado_anterior = '';
			$es_primer_registro = true;
			$hay_mas_registros = true;
			$iteracion = 1;
			$cantidad_registros = count( $movimiento );
			foreach($movimiento AS $registro)
			{
				$cc_empleado_actual = $registro->empleado_numero_identificacion;

				if( $cc_empleado_anterior != $cc_empleado_actual )
				{

					if ( !$es_primer_registro ) 
					{
						echo dibujar_totales( $total_valor_provision );
						$total_valor_provision = 0;
					}
					
					$es_primer_registro = false;
					$cc_empleado_anterior = $cc_empleado_actual;
					
					//if ( $hay_mas_registros )
					//{
						echo dibujar_etiquetas( $registro );
					//}
					
					$total_valor_provision += (float)$registro->valor_provision;

				}else{
					echo dibujar_etiquetas( $registro );
					$total_valor_provision += (float)$registro->valor_provision;
					$cc_empleado_anterior = $cc_empleado_actual;
					$es_primer_registro = false;
				}

				if ( $iteracion == $cantidad_registros )
				{
					$hay_mas_registros = false;
				}

				$iteracion++;

			}

			echo dibujar_totales( $total_valor_provision );
		?>
	</tbody>
</table>