<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> CC </th>
			<th> EMPLEADO </th>
			<th> Concepto </th>
			<th> Horas </th>
			<th> Valor devengo </th>
			<th> Valor deducción </th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$total_horas = 0;
			$total_devengos = 0;
			$total_deducciones = 0;
			$cc_empleado_anterior = '';
			$es_primer_registro = true;
			$hay_mas_registros = true;
			$iteracion = 1;
			$cantidad_registros = count( $movimiento );
			foreach( $movimiento AS $registro)
			{
				$cc_empleado_actual = $registro->empleado_numero_identificacion;

				if( $cc_empleado_anterior != $cc_empleado_actual )
				{

					if ( !$es_primer_registro ) 
					{
						echo dibujar_totales( $total_horas, $total_devengos, $total_deducciones );
						$total_devengos = 0;
						$total_deducciones = 0;
					}
					
					$es_primer_registro = false;
					$cc_empleado_anterior = $cc_empleado_actual;
					
					//if ( $hay_mas_registros )
					//{
						echo dibujar_etiquetas( $registro );
					//}
					
					$total_devengos += (float)$registro->valor_devengo;
					$total_deducciones += (float)$registro->valor_deduccion;

				}else{
					echo dibujar_etiquetas( $registro );
					$total_devengos += (float)$registro->valor_devengo;
					$total_deducciones += (float)$registro->valor_deduccion;
					$cc_empleado_anterior = $cc_empleado_actual;
					$es_primer_registro = false;
				}

				if ( $iteracion == $cantidad_registros )
				{
					$hay_mas_registros = false;
				}

				$iteracion++;

				if ( $iteracion % 30 == 0 )
				{
					echo '<div class="page-break"></div>';
				}

			}

			echo dibujar_totales( $total_horas, $total_devengos, $total_deducciones );
		?>
	</tbody>
</table>