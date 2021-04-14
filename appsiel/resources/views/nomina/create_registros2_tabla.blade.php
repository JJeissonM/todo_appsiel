

<table class="table table-responsive" id="myTable2">
	<?php 
		$lbl_encabezado = 'Valor concepto';
		if ( (float)$concepto->porcentaje_sobre_basico != 0 )
		{
			$lbl_encabezado = 'Cantidad horas';
		}
	?>
	<thead>
		<tr>
			<th>Empleado</th>
			<th>{{ $lbl_encabezado }}</th>
		</tr>
	</thead>
	<tbody>
		@foreach($empleados as $empleado)
			<tr> 
				<td style="font-size:12px">
					<b>{{ $empleado->tercero->descripcion }}</b>
					
					{{ Form::hidden('core_tercero_id[]', $empleado->core_tercero_id, []) }}

				</td>

				<td>
					@if ( (float)$concepto->porcentaje_sobre_basico != 0 )
						<input type="text" name="cantidad_horas[]" class="form-control" placeholder="Cantidad horas">
					@else
						<input type="text" name="valor[]" class="form-control" placeholder="Valor">
					@endif
				</td>
            </tr>
		@endforeach
	</tbody>
</table>