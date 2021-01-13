
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
    RESUMEN POR ENTIDADES
</h3>

<p style="width: 100%; text-align: center;">
    Desde: {{ $fecha_desde }} - Hasta: {{ $fecha_hasta }}
</p>

<hr>


<?php 
	$lbl_encabezado = '';
?>

<div class="table-responsive">

	<table class="table">
		<tbody>
			<tr>
				<td>
					<div class="accordion"> Entidades de salud </div>
					@include('nomina.reportes.tabla_resumen_entidades',['movimiento' => $coleccion_movimientos_salud])
				</td>
				<td>
					<div class="accordion"> Entidades de pensión </div>
					@include('nomina.reportes.tabla_resumen_entidades',['movimiento' => $coleccion_movimientos_afp])
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr> 
				<td> GRAN TOTAL </td>
				<td> {{ Form::TextoMoneda( $gran_total ) }} </td>
			</tr>
		</tfoot>
	</table>

	<div style="width: 100%; position: fixed; bottom: 0;">
		<hr>
		{!! generado_por_appsiel() !!}
	</div>	

</div>