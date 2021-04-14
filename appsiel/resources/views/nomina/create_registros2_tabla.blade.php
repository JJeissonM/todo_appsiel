<div class="container-fluid" style="border: 1px #ddd dashed; padding: 5px;">
	<h4>Ingreso de horas de trabajo</h4>
	<hr>
	<table class="table table-responsive table-striped" id="myTable2">
		<thead>
			<tr>
				<th>Empleado</th>
				@if ( (float)$concepto->porcentaje_sobre_basico != 0 )
					<th> Cant. horas </th>
					<th> Vlr. unitario </th>
					<th> Vlr. total </th>
				@else
					<th> Vlr. total </th>
				@endif
				
			</tr>
		</thead>
		<tbody>
			@foreach($empleados as $empleado)
				<tr> 
					<td style="font-size:12px">
						<b>{{ $empleado->tercero->descripcion }}</b>
						
						{{ Form::hidden('core_tercero_id[]', $empleado->core_tercero_id, []) }}

					</td>
					@if ( (float)$concepto->porcentaje_sobre_basico != 0 )
						<td>
							<input type="text" name="cantidad_horas[]" class="form-control cantidad_horas" placeholder="Cant. horas">
						</td>
						<td>
							<input type="text" name="valor_unitario[]" class="form-control valor_unitario" placeholder="Vlr. unitario" value="{{ $concepto->valor_fijo }}">
						</td>
						<td>
							<input type="text" name="valor_total[]" class="form-control valor_total" placeholder="Vlr. total" readonly="readonly">
						</td>
					@else
						<td>
							<input type="text" name="valor_total[]" class="form-control valor_total" placeholder="Vlr. total" readonly="readonly">
						</td>
					@endif
	            </tr>
			@endforeach
		</tbody>
	</table>
</div>