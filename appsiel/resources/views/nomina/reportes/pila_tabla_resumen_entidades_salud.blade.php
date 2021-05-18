<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th> {{ config("configuracion.tipo_identificador") }} </th>
			<th> Entidad </th>
			<th> Cod. Nacional </th>
			<th> Valor aportado </th>
		</tr>
	</thead>
	<tbody>
		<?php 
			$gran_total = 0;
			$entidad_anterior = '';
			$total_entidad = 0;
			$es_siguiente_iteracion = false;
			$hay_mas_registros = true;
			$iteracion = 1;
			$cantidad_registros = count( $movimiento->toArray() );
			foreach($movimiento AS $registro)
			{
				$entidad_actual = $registro->entidad();

				$codigo_nacional = 'NNN';
				$nit = '';
				$descripcion_entidad = '';
				if ( !is_null($entidad_actual) )
				{
					$codigo_nacional = $entidad_actual->codigo_nacional;
					$nit = $entidad_actual->tercero->numero_identificacion;
					$descripcion_entidad = $entidad_actual->descripcion;
				}

				if( $entidad_anterior != $codigo_nacional )
				{
					if ( $es_siguiente_iteracion )
					{
						echo dibujar_valor( $total_entidad );
						$total_entidad = 0;
					}
					
					$es_siguiente_iteracion = false;
					$entidad_anterior = $codigo_nacional;
					
					if ( $hay_mas_registros )
					{
						echo dibujar_etiquetas( $descripcion_entidad, $nit, $codigo_nacional );
					}
					
					$total_entidad += (float)$registro->cotizacion_salud;

				}else{
					$total_entidad += (float)$registro->cotizacion_salud;
					$entidad_anterior = $codigo_nacional;
					$es_siguiente_iteracion = true;
				}

				if ( $iteracion == $cantidad_registros )
				{
					$hay_mas_registros = false;
				}

				$gran_total += (float)$registro->cotizacion_salud;
				$iteracion++;

			}

			echo dibujar_valor( $total_entidad );
		?>
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td> {{ Form::TextoMoneda( $gran_total ) }} </td>
		</tr>
	</tfoot>
</table>