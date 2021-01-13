
<style type="text/css">
		
	/* Style the buttons that are used to open and close the accordion panel */
	.accordion {
	  background-color: #50B794;
	  color: #444;
	  cursor: pointer;
	  padding: 18px;
	  width: 100%;
	  text-align: left;
	  border: none;
	  outline: none;
	  transition: 0.4s;
	  font-weight: bold;
	}

</style>

<h3 style="width: 100%; text-align: center;">
    RESUMEN DE LIQUIDACIONES DE NÓMINA
</h3>
<p style="width: 100%; text-align: center;">
    Desde: {{ $fecha_desde }} | Hasta: {{ $fecha_hasta }}
</p>
<hr>

<div class="table-responsive">

	<table class="table">
		<tbody>
			<tr>
				<td>
					@include('nomina.reportes.resumen_liquidaciones_tabla',['movimiento' => $datos])
				</td>
			</tr>
		</tbody>
	</table>

	<div style="width: 100%; position: fixed; bottom: 0;">
		<hr>
		{!! generado_por_appsiel() !!}
	</div>	

</div>

<?php 
	
	function dibujar_etiquetas( $registro )
	{
		return '<tr>
					<td>
						' . $registro->empleado_numero_identificacion . '
					</td>
					<td>
						' . $registro->empleado_descripcion . '
					</td>
					<td>
						' . $registro->concepto . '
					</td>
					<td>
						' . $registro->cantidad_horas . '
					</td>
					<td style="text-align:right;">
						$' . number_format( $registro->valor_devengo, 2,',','.') . '
					</td>
					<td style="text-align:right;">
						$' . number_format( $registro->valor_deduccion, 2,',','.') . '
					</td>
				</tr>';
	}

	function dibujar_totales( $total_horas, $total_devengos, $total_deducciones )
	{
		return '<tr>
					<td colspan="4"> </td>
					<td style="text-align:right;"> $' . number_format( $total_devengos, 2,',','.') . ' </td> 
					<td style="text-align:right;"> $' . number_format( $total_deducciones, 2,',','.') . ' </td> 
				</tr>
				<tr><td colspan="6"></td></tr>';
	}

?>