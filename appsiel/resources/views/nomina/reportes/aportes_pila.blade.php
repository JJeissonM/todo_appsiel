
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
    RESUMEN DE APORTES A SEGURIDAD SOCIAL Y PARAFISCALES
</h3>
<p style="width: 100%; text-align: center;">
    Fecha: {{ $fecha_final_mes }}
</p>
<hr>

<div class="table-responsive">

	<table class="table">
		<tbody>
			<tr>
				<td>
					<div class="accordion"> Aportes a Salud </div>
					@include('nomina.reportes.pila_tabla_resumen_entidades',['movimiento' => $coleccion_movimientos_salud])
				</td>
			</tr>
			<tr>
				<td>
					<div class="accordion"> Aportes a Pensión </div>
					@include('nomina.reportes.pila_tabla_resumen_entidades',['movimiento' => $coleccion_movimientos_pension])
				</td>
			</tr>
			<tr>
				<td>
					<div class="accordion"> Aportes a Riesgos laborales </div>
					@include('nomina.reportes.pila_tabla_resumen_entidades',['movimiento' => $coleccion_movimientos_riesgos_laborales])
				</td>
			</tr>
			<tr>
				<td>
					<div class="accordion"> Aportes a Parafiscales </div>
					@include('nomina.reportes.pila_tabla_resumen_entidades',['movimiento' => $coleccion_movimientos_parafiscales])
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>
					<div class="accordion">Total Aportes</div>

					<table class="table table-bordered table-striped">
						<tfoot>
							<tr>
								<td>  &nbsp; </td>
								<td>  &nbsp; </td>
								<td>  &nbsp; </td>
								<td> {{ Form::TextoMoneda( $gran_total_general ) }} </td>
							</tr>
						</tfoot>
					</table>
				</td>
			</tr>
		</tfoot>
	</table>

	<div style="width: 100%; position: fixed; bottom: 0;">
		<hr>
		{!! generado_por_appsiel() !!}
	</div>	

</div>