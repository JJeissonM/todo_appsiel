<table class="table table-bordered">
	<tbody>
		@foreach($movimiento AS $registro_entidad)
		
			<tr>
				<td> {!! $registro_entidad->entidad !!} </td>

				<td> {{ Form::TextoMoneda( $registro_entidad->total_deduccion_entidad ) }} </td>
			</tr>

			<tr>
				<td colspan="2"> 
					@include('nomina.reportes.tabla_detalle_empleados',['movimiento_entidad'=>$registro_entidad->movimiento,'entidad_id'=>$registro_entidad->entidad_id])
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
			<td>{{ $label }}</td>
			<td> {{ Form::TextoMoneda( $gran_total ) }} </td>
		</tr>
	</tfoot>
</table>