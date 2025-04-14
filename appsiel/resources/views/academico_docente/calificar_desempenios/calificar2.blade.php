@extends('academico_docente.calificar_desempenios.calificaciones_form_v2', ['titulo'=>'calificaciones'])

@section('tabla')
	
	<div class="table-responsive">
		<table class="table table-bordered" id="tabla_registros">
			<thead>
				<tr>
					<th>Estudiantes</th>
					<th>
						Logros
					</th>
					<th>
						Desempeño
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$cant_logros = $logros->count();
					$linea = 1;
				?>

				@for( $k = 0; $k < $cantidad_estudiantes; $k++)

					<?php
						$is_the_first = true;
					?>

					@foreach ( $logros as $logro )
						<tr data-matricula_id="{{ $vec_estudiantes[$k]['matricula_id'] }}" data-logro_id="{{ $logro->id }}">
							@if($is_the_first)
								<td rowspan="{{ $cant_logros }}" style="font-size:12px; vertical-align: middle;" width="250px" >
									<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
								</td>
								<?php
									$is_the_first = false;
									$linea++;
								?>						
							@endif
							<td> 
								{{ $logro->descripcion }} 
							</td>

							<?php
								$valor_desempenio = $todas_las_calificaciones->where('matricula_id', $vec_estudiantes[$k]['matricula_id'])->where('logro_id', $logro->id)->first();

								$escala_valoracion_id = null;
								if ( $valor_desempenio != null )
								{	
									$escala_valoracion_id = $valor_desempenio->escala_valoracion_id;
								}
							?>
							<td> 
								{{ Form::select('escala_valoracion_id[]', $escalas_valoracion, $escala_valoracion_id, [ 'class' => 'select_escala_valoracion']) }}
							</td>
						</tr>
					@endforeach
				@endfor
				
				@if($cant_logros == 0)
					<tr>
						<td colspan="3" style="text-align: center; font-size: 12px;">
							No hay logros registrados en este periodo.
						</td>
					</tr>
				@endif
			</tbody>
		</table>
		
	</div>
@endsection