<h4>Listado de asignaturas por áreas</h4>
<hr>
<div class="table-responsive">
	<?php 
		$area_anterior = '';
		$es_el_primero = 1;
		$area_id = 0;
	?>			
	
	{{ Form::open(['url'=>'sga_almacenar_pesos_asignaturas_areas','id'=>'form_create']) }}
		<input type="hidden" name="periodo_lectivo_id" value="{{$periodo_lectivo_id}}">
		<input type="hidden" name="grado_id" value="{{$grado_id}}">
	<table class="table table-bordered">
		<tbody>
			@foreach( $asignaturas as $linea )

				@if ( $area_anterior != $linea->area)

					@if( !$es_el_primero )
						<tr>
							<td> &nbsp; </td>
							
							<td align="center">
							    <div id="totalpesoarea_{{$linea->area_id}}"> </div>
							</td>
						</tr>
					@endif
					<tr style="background: #ddd; text-align: center;">
						<td colspan="2">
							&nbsp;
								<b> ÁREA: {{ $linea->area }}</b>
						</td>
					</tr>
					<tr style="background: #ddd;">
						<td>
							<b> Asignatura </b>
						</td>
						<td>
							<b> Peso (%)</b>
						</td>
					</tr>
					<?php
						$es_el_primero = 0;
					?>
				@endif

				<tr>
					<td> {{ $linea->descripcion }} </td>
					
					<td align="center">
					    <input type="hidden" name="asignatura_id[]" value="{{$linea->asignatura_id}}">
					    <input type="text" name="peso[]" class="form-control pesoarea_{{$linea->area_id}}" placeholder="Peso" value="{{ $linea->peso }}">
					</td>
				</tr>

				<?php 
					$area_anterior = $linea->area;
					$area_id = $linea->area_id;
				?>

			@endforeach {{--  Asignaturas --}}
			<tr>
				<td> &nbsp; </td>
				
				<td align="center">
				    <div id="totalpesoarea_{{$area_id}}"> </div>
				</td>
			</tr>
		</tbody>
	</table>
	@if( $area_id != 0 )
		<div class="row">
			<div class="col-md-12">
				<button class="btn btn-primary"> <i class="fa fa-save"></i> Guardar </button>
			</div>
		</div>
	@endif
	{{ Form::close() }}
</div>