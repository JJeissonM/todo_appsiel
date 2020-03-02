<div style="padding: 2px;">
	<table style="border: 1px solid; font-size: {{$tam_letra-1}}mm;">
	    <tr>
	        <td width="100px">
	            <img src="{{ $url }}" height="60px"/>
	        </td>

	        <td align="center">
	            <br/>
	            <b style="font-size: {{$tam_letra+1}}mm;">{{ $colegio->descripcion }}</b>
	            <br/>
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->slogan }}</b>
	            <br/>
	            <b style="font-size: {{$tam_letra-1}}mm;">{{ $colegio->ciudad }}</b>
	        </td>
	    </tr>
	    <tr>
	    	<td colspan="2">
	    		<div style="text-justify: auto; width: 100%;">
					PRE-INFORME DE RENDIMIENTO ACADÉMICO CORRESPONDIENTE AL PERIODO  <b>{{ $periodo->descripcion }}</b> de {{ $anio }}
					<br>
					<b>Estudiante:</b> {{ $estudiante->nombre_completo }} &nbsp;&nbsp; | &nbsp;&nbsp; <b>Curso:</b> {{ $curso->descripcion }}
				</div>
	    	</td>
	    </tr>
	</table>

	<?php
		$area_anterior = '';
	?>
			
	<table style="border: 1px solid; font-size: {{$tam_letra-1}}mm; border-collapse: collapse;">
		<tbody>
			@foreach($asignaturas as $asignatura)
				<?php
				// Se llama a la calificacion de cada asignatura
				$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id_estudiante, $asignatura->id);
				
				?>
				<?php 
					if ( $area_anterior != $asignatura->area  AND $mostrar_areas == 'Si')
					{
				?>
					<tr style="border: 1px solid; background-color: #CCCBCB;">
						<td style="border: 1px solid; font-size: {{$tam_letra-1}}mm;">
							<b> ÁREA: {{ $asignatura->area }}</b>
						</td>
					</tr>

				<?php
					}
				?>

				<tr style="background-color: #E8E8E8;">
					<td style="border: 1px solid; font-size: {{$tam_letra-1}}mm;">
						{{ $asignatura->descripcion }}
						&nbsp;&nbsp;&nbsp;	|  &nbsp;&nbsp;&nbsp;
						@if($asignatura->intensidad_horaria != 0)
							<b>IH: </b>{{ $asignatura->intensidad_horaria }} &nbsp;
						@endif
						
						&nbsp;&nbsp;&nbsp;	|  &nbsp;&nbsp;&nbsp;

						<b>Cal: </b>{{ $calificacion->valor }} ({{ $calificacion->escala_descripcion }})
					</td>
				</tr>

				<tr>
					<td style="border: 1px solid; font-size: {{$tam_letra-1}}mm;">
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
				?>

			@endforeach {{--  Asignaturas --}}
		</tbody>
	</table>
</div>