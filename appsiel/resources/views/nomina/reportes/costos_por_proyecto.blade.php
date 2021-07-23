<h3 style="width: 100%; text-align: center;">
    COSTOS POR PROYECTO
</h3>
<p style="width: 100%; text-align: center;">
    Proyecto: {{ $documento_nomina->descripcion }}
    <br>
    Fecha: {{ $documento_nomina->fecha }}
</p>
<hr>


<?php 
	$lbl_encabezado = '';
	if ( $detalla_empleados )
	{
		$lbl_encabezado = 'Empleado';
	}
?>

<table class="table">
	<tr>
		<td>
			<div class="table-responsive">
				<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Gastos por conceptos de nónima</div>
			    <table class="table table-striped">
			        <thead>
			            <tr>
							<th> Concepto </th>
			            	@if ( $detalla_empleados )
				                <th> Empleado </th>
				            @endif
							<th> Cant. Horas </th>
							<th> Vlr. Prom. </th>
							<th> Vlr. Total </th>
			            </tr>
			        </thead>
			        <tbody>
			            <?php
			                $gran_total_conceptos = 0;
			                $gran_total_cantidades = 0;
			            	foreach( $conceptos as $concepto )
			            	{
				                $valor_total_concepto = 0;
				                $totales_cantidad_horas = 0;
			                    foreach( $empleados as $empleado )
			                    {
			                		$fila_empleado = '';
			                		$devengo = $movimiento->whereLoose( 'core_tercero_id', $empleado->core_tercero_id )->whereLoose( 'nom_concepto_id', $concepto->id )->sum('valor_devengo');
			                		$deduccion = $movimiento->whereLoose( 'core_tercero_id', $empleado->core_tercero_id )->whereLoose( 'nom_concepto_id', $concepto->id )->sum('valor_deduccion');
			                		$cantidad_horas = $movimiento->whereLoose( 'core_tercero_id', $empleado->core_tercero_id )->whereLoose( 'nom_concepto_id', $concepto->id )->sum('cantidad_horas');

					                $fila_empleado .= '<tr>';

					                $fila_empleado .= '<td> ' . $concepto->descripcion . '</td>';

				                	$fila_empleado .= '<td> ' . $empleado->tercero->numero_identificacion . ' - ' . $empleado->tercero->descripcion . '</td>';

				                	$fila_empleado .= '<td align="center"> ' . number_format( $cantidad_horas, 2, ',','.') . ' </td>';

				                	$valor_promedio = 0;
				                	$valor_total = $devengo - $deduccion;
				                	if ( $cantidad_horas != 0 )
				                	{
				                		$valor_promedio = $valor_total / $cantidad_horas;
				                	}

				                	$fila_empleado .= '<td align="right"> $ ' . number_format( $valor_promedio, 2, ',','.') . ' </td>';

				                	$fila_empleado .= '<td align="right"> $ ' . number_format( $valor_total, 2, ',','.') . ' </td>';
				                	$fila_empleado .= '</tr>';

					                $valor_total_concepto += $valor_total;
					                $totales_cantidad_horas += $cantidad_horas;
					                
					                if( $detalla_empleados )
					                {
						            	echo $fila_empleado;
					                }
				                }


				                $gran_total_conceptos += $valor_total_concepto;
				                $gran_total_cantidades += $totales_cantidad_horas;
								
								$total_pom = 0;
			                	if ( $totales_cantidad_horas != 0 )
			                	{
			                		$total_pom = $valor_total_concepto / $totales_cantidad_horas;
			                	}

				                if( $detalla_empleados )
				                {
					            	echo '<tr>
					            			<td colspan="2">Total x concepto</td>
					            			<td align="center">'.number_format( $totales_cantidad_horas, 2, ',','.').'</td>
					            			<td align="right">$ '.number_format( $total_pom, 2, ',','.').'</td>
					            			<td align="right">$ '.number_format( $valor_total_concepto, 2, ',','.').'</td>
					            			</tr>';
				                }else{
				                	echo '<tr>
					            			<td>'.$concepto->descripcion.'</td>
					            			<td align="center">'.number_format( $totales_cantidad_horas, 2, ',','.') .'</td>
					            			<td align="right">$ '.number_format( $total_pom, 2, ',','.') .'</td>
					            			<td align="right">$ '.number_format( $valor_total_concepto, 2, ',','.') .'</td>
					            			</tr>';
				                }
				            }
				        ?>
			        </tbody>
			        <tfoot>
			        	@if( $detalla_empleados )
							<tr style="background: #4a4a4a; color: white;">
								<td colspan="2"> Totales </td>
								<td align="center"> {{ number_format( $gran_total_cantidades, 2, ',','.')  }} </td> 
								<td> </td>
				        		<td align="right"> $ {{ number_format( $gran_total_conceptos, 2, ',','.')  }} </td>
				        	</tr>
				        @else
							<tr style="background: #4a4a4a; color: white;">
								<td> Totales </td>
								<td align="center"> {{ number_format( $gran_total_cantidades, 2, ',','.')  }} </td> 
								<td> </td>
				        		<td align="right"> $ {{ number_format( $gran_total_conceptos, 2, ',','.')  }} </td>
				        	</tr>
				        @endif
			        </tfoot>
			    </table>
			</div>

			<div class="table-responsive">
				<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Gastos de materiales</div>
			    <table class="table table-striped">
			        <thead>
			            <tr>
							<th> Ítem </th>
							<th> Cant. </th>
							@if( $sumar_iva )
								<th> Tasa IVA </th>
								<th> Costo Prom. (IVA incluido) </th>
							@else
								<th> Costo Prom. </th>
							@endif
							<th> Costo Total </th>
			            </tr>
			        </thead>
			        <tbody>
			            <?php
			                $gran_total_cantidades = 0;
			                $gran_total_costo = 0;
			            ?>
			            @foreach( $items as $item )
			            	<?php                
			            		$cantidad = $movimiento_inventario->whereLoose( 'inv_producto_id', $item->id )->sum('cantidad');
			            		$costo_total = $movimiento_inventario->whereLoose( 'inv_producto_id', $item->id )->sum('costo_total');
			            	?>
				            <tr>
				            	<td>{{ $item->descripcion }} ({{ $item->unidad_medida1 }})</td>
				            	<td align="center"> {{ number_format( abs($cantidad), 2, ',','.') }} </td>
				            	<?php 
				                	$valor_promedio = 0;
				                	if ( $cantidad != 0 )
				                	{
				                		$valor_promedio = $costo_total / $cantidad;
				                	}

				                	if ( $sumar_iva )
				                	{
				                		$valor_promedio = $valor_promedio * ( 1 + $item->tasa_impuesto() / 100 );
				                		$costo_total = $costo_total * ( 1 + $item->tasa_impuesto() / 100 );
				                	}
				            	?>
				            	@if( $sumar_iva )
				            		<td align="center"> {{ $item->tasa_impuesto() }}% </td>
				            	@endif
			                	<td align="right"> $ {{ number_format( abs($valor_promedio), 2, ',','.') }} </td>
			                	<td align="right"> $ {{ number_format( abs($costo_total), 2, ',','.') }} </td>
			                </tr>
			            	<?php 
				                $gran_total_cantidades += $cantidad;
				                $gran_total_costo += $costo_total;
				        	?>
				        @endforeach
			        </tbody>
			        <tfoot>
						<tr style="background: #4a4a4a; color: white;">
							<td> Totales </td>
							<td align="center"> {{ number_format( abs($gran_total_cantidades), 2, ',','.')  }} </td>
							@if( $sumar_iva )
			            		<td> </td>
			            	@endif
							<td> </td>
			        		<td align="right"> $ {{ number_format( abs($gran_total_costo), 2, ',','.')  }} </td>
			        	</tr>
			        </tfoot>
			    </table>
			</div>

			<div class="table-responsive">
				<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Gastos totales</div>
			    <table class="table table-striped">
			        <tbody>
						<tr style="background: #4a4a4a; color: white;">
							<td> Totales </td>
			        		<td align="right"> $ {{ number_format( abs($gran_total_conceptos) + abs($gran_total_costo), 2, ',','.') }} </td>
			        	</tr>
			        </tbody>
			    </table>
			</div>
		</td>
	</tr>
			
</table>