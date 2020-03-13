<div style="padding: 2px; display: inline-block;">
	<table class="table table-bordered" width="100%">
	    <tr>
	        <td width="100px">
	            <img src="{{ $url }}" height="50px"/>
	        </td>

	        <td align="center">
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->descripcion }}</b>
	            <br/>
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->slogan }}</b>
	            <br/>
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->ciudad }}</b>
	        </td>
	    </tr>
	    <tr>
	    	<td colspan="2">
	    		<div style="text-justify: auto; width: 100%; font-size: {{$tam_letra}}mm;">
					PRE-INFORME DE RENDIMIENTO ACADÉMICO CORRESPONDIENTE AL PERIODO  <b>{{ $periodo->descripcion }}</b> de {{ $anio }}
					<br>
					<b>Estudiante:</b> {{ $estudiante->nombre_completo }} &nbsp;&nbsp; | &nbsp;&nbsp; <b>Curso:</b> {{ $curso->descripcion }}
				</div>
	    	</td>
	    </tr>
	</table>

	<?php
		$area_anterior = '';
		$fila = 1;
	?>

	<div style="font-size: {{$tam_letra}}mm;">
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
					$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id_estudiante, $asignatura->id);
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
								->where('id_estudiante', $estudiante->id_estudiante)
								->get()
								->first();
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


		@if( $mostrar_usuarios_estudiantes == 'Si') 
			@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		@endif

	</div>
</div>