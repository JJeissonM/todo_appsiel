<div style="padding: 2px; display: inline-block;">
	
	<div style="text-justify: auto; width: 100%;">
		PRE-INFORME DE RENDIMIENTO ACADÉMICO CORRESPONDIENTE AL PERIODO  <b>{{ $periodo->descripcion }}</b> de {{ $anio }}
		<br>
		<b>Estudiante:</b> {{ $estudiante->nombre_completo }} &nbsp;&nbsp; | &nbsp;&nbsp; <b>Curso:</b> {{ $curso->descripcion }}
	</div>


	<?php
		$area_anterior = '';
		$fila = 1;
	?>

	<div>
		<table class="table table-striped" width="100%">
			<thead>
				<tr>
					<th>Asignatura</th>
					<th>Cal.</th>
					<th>Anotación</th>
				</tr> 
			</thead>
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id, $asignatura->id);
						if( $fila % 2 == 0)
						{
							$class_fila = 'even';
						}else{
							$class_fila = 'odd';
						}			
					?>

					<tr class="{{$class_fila}}">
						
						<td> 
							{{ $asignatura->descripcion }} 
						</td>
						
						<td> 
							{{ $calificacion->valor }} ({{ $calificacion->escala_descripcion }})
						</td>

						<td>
		                    <?php 

								$anotacion = App\Calificaciones\PreinformeAcademico::where('id_periodo',$periodo->id)
								->where('curso_id', $curso->id)
								->where('id_asignatura', $asignatura->id)
								->where('id_estudiante', $estudiante->id)
								->get()
								->first();

								//dd( [ $periodo->id, $curso->id, $asignatura->id, $estudiante->id ] );

								if ( !is_null($anotacion) ) 
								{
									$anotacion = $anotacion->anotacion;
								}else{
									$anotacion = '';
								}
		                    ?>
		                    {{ $anotacion }}
						</td>

					</tr>

					<?php 
						$area_anterior = $asignatura->area;
						$fila++;
					?>

				@endforeach {{--  Asignaturas --}}
			</tbody>
		</table>

	</div>
</div>