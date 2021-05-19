<div class="container-fluid" style="border: 1px #ddd dashed; padding: 5px;">
	<h4 class="text-center">INGRESO HORAS DE TRABAJO</h4>
	<hr>
	<table class="table table-responsive table-striped" id="tabla_registros_empleados">
		<thead>
			<tr>
				<th style="display: none;">nom_contrato_id</th>
				<th>EMPLEADO</th>
				@if ( (float)$concepto->porcentaje_sobre_basico != 0 )
					<th data-override="cantidad_horas"> CANT. HORAS </th>
					<th data-override="valor_unitario"> VLR. UNITARIO </th>
					<th data-override="valor_total"> VLR. TOTAL </th>
				@else
					<th data-override="valor_total"> VLR. TOTAL </th>
				@endif
				
			</tr>
		</thead>
		<tbody>
			@foreach($empleados as $empleado)
				<tr>
					<td style="display: none;">{{$empleado->id}}</td>
					<td style="font-size:12px">
						<b>{{ $empleado->tercero->descripcion }}</b>
						
						{{ Form::hidden('core_tercero_id[]', $empleado->core_tercero_id, []) }}

					</td>
					@if ( (float)$concepto->porcentaje_sobre_basico != 0 )
						<td>
							<input type="text" name="cantidad_horas[]" class="form-control cantidad_horas" placeholder="Cant. horas">
							<span style="display: none;"></span>
						</td>
						<td>
							<input type="text" name="valor_unitario[]" class="form-control valor_unitario" placeholder="Vlr. unitario" value="{{ $concepto->valor_fijo }}">
							<span style="display: none;"></span>
						</td>
						<td>
							<input type="text" name="valor_total[]" class="form-control valor_total" placeholder="Vlr. total" readonly="readonly">
							<span style="display: none;"></span>
						</td>
					@else
						<td>
							<input type="text" name="valor_total[]" class="form-control valor_total" placeholder="Vlr. total" readonly="readonly">
							<span style="display: none;"></span>
						</td>
					@endif
	            </tr>
			@endforeach
		</tbody>
	</table>
</div>