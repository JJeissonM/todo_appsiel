
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
    CONSOLIDADO DE PRESTACIONES SOCIALES
</h3>
<p style="width: 100%; text-align: center;">
    Desde: {{ $fecha_desde }} | Hasta: {{ $fecha_hasta }}
</p>
<p style="width: 100%; text-align: center;">
    Cantidad de meses promediados: {{ $cantidad_meses_a_promediar }}
</p>
<hr>

<div class="table-responsive">

	<table class="table">
		<tbody>
			<tr>
				<td>
					@include('nomina.reportes.consolidado_prestaciones_sociales_tabla',['movimiento' => $datos])
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
						' . number_format( $registro->dias_laborados, 2,',','.') . '
					</td>
					<td>
						' . number_format( $registro->dias_derecho, 2,',','.') . '
					</td>
					<td style="text-align:right;">
						$' . number_format( $registro->base_diaria, 2,',','.') . '
					</td>
					<td style="text-align:right;">
						$' . number_format( $registro->valor_provision, 2,',','.') . '
					</td>
				</tr>';
	}

	function dibujar_totales( $total )
	{
		return '<tr>
					<td colspan="6"> </td>
					<td style="text-align:right;"> $' . number_format( $total, 2,',','.') . ' </td> 
				</tr>
				<tr><td colspan="7"></td></tr>';
	}

?>