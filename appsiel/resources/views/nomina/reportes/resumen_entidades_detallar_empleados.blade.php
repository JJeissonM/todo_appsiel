
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
    RESUMÉN POR ENTIDADES
</h3>

<p style="width: 100%; text-align: center;">
    Desde: {{ $fecha_desde }} - Hasta: {{ $fecha_hasta }}
</p>

<hr>

<div class="table-responsive">
	<table class="table">
		<tbody>
			<tr>
				<td>
					<div class="accordion"> Entidades de salud </div>
					@include('nomina.reportes.tabla_resumen_entidades_detallar_empleados',['movimiento' => $coleccion_movimientos_salud, 'gran_total' => $total_salud, 'label' => 'TOTAL SALUD'])
				</td>
			</tr>
			<tr>
				<td>
					<div class="accordion"> Entidades de pensión </div>
					@include('nomina.reportes.tabla_resumen_entidades_detallar_empleados',['movimiento' => $coleccion_movimientos_afp, 'gran_total' => $total_pension, 'label' => 'TOTAL PENSION'])
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td> GRAN TOTAL {{ Form::TextoMoneda( $gran_total ) }} </td>
			</tr>
		</tfoot>
			
	</table>

	<div style="width: 100%; position: fixed; bottom: 0;">
		<hr>
		{!! generado_por_appsiel() !!}
	</div>	

</div>