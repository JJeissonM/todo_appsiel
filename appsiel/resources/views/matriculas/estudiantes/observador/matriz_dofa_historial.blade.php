<?php 
	$periodos = \App\Calificaciones\Periodo::get_activos_periodo_lectivo_actual();
?>
<h4 style="width: 100%; text-align: center;">OBSERVACIONES DE DESEMPEÑO PERIODO A PERIODO</h4>
<hr>

@foreach( $periodos AS $periodo )

	<table class="table table-bordered">
		<tr>
			<td style="text-align: center;" colspan="4">
				<span><b> PERIODO {{ $periodo->descripcion }}</b></span>
			</td>
		</tr>
		<tr>
			<td>
				<span><b>FORTALEZAS</b></span>
			</td>
			<td>				
				<span> <b>DEBILIDADES</b> </span>
			</td>
			<td>
				<span><b>AREAS PERDIDAS</b></span>
			</td>
			<td>
				<span><b>RECOMENDACIONES</b></span>
			</td>
		</tr>
		<tr>
			<td>
				<?php
					$registros_analisis = App\Matriculas\FodaEstudiante::where([
																				['id_estudiante','=',$estudiante->id],					
																				])
																		->whereBetween('fecha_novedad',[ $periodo->fecha_desde, $periodo->fecha_hasta])
																		->get();
				?>
				@include('terceros.analisis_dofa.dibujar_lista_elementos', ['tipo_caracteristica' => 'Fortaleza', 'lista_items' => $registros_analisis])
			</td>
			<td>
				@include('terceros.analisis_dofa.dibujar_lista_elementos', ['tipo_caracteristica' => 'Debilidad', 'lista_items' => $registros_analisis])
			</td>
			<td>
				<?php
					$calificaciones_asignaturas_perdidas = $periodo->calificaciones_asignaturas_perdidas( $estudiante->id );
				?>
				<ul class="list-group">
					@foreach( $calificaciones_asignaturas_perdidas AS $calificacion )
						<li class="list-group-item"> {{ $calificacion->asignatura->descripcion }} / Cal. {{ $calificacion->calificacion }}</li>
					@endforeach
				</ul>
					
			</td>
			<td>
				<?php
					$observaciones_boletin = $periodo->observaciones_boletin( $estudiante->id );
				?>
				<ul class="list-group">
					@foreach( $observaciones_boletin AS $observacion )
						<li class="list-group-item"> {{ $observacion->observacion }} </li>
					@endforeach
				</ul>
			</td>
		</tr>
	</table>
@endforeach