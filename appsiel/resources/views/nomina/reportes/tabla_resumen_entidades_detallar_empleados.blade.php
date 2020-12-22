<table class="table table-bordered">
	<tbody>
		<?php $gran_total = 0; ?>
		@foreach($movimiento AS $registro)
			<?php
				$total_deducciones_entidad = 0;
			?>
			<tr>
				<td> {!! $registro->entidad !!} </td>

				<?php 
					foreach ($registro->movimiento as $key => $value)
					{
						$total_deducciones_entidad += $value['valor_deduccion'];
						$gran_total += $value['valor_deduccion'];
					}
				?>
				<td> {{ Form::TextoMoneda( $total_deducciones_entidad ) }} </td>
			</tr>



			<tr>
				<td colspan="2"> 
					@include('nomina.reportes.tabla_detalle_empleados',['movimiento_entidad'=>$registro->movimiento])
				</td>
			</tr>

			<tr>
				<td colspan="2"> 
					&nbsp;
				</td>
			</tr>

		@endforeach
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<td> {{ Form::TextoMoneda( $gran_total ) }} </td>
		</tr>
	</tfoot>
</table>