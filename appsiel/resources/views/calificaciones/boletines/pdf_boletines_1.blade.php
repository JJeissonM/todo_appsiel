@extends( 'calificaciones.boletines.formatos.layout' )

@section('contenido_formato')

	<?php
	    if ( $mostrar_areas == 'Si')
		{
			$lbl_asigatura = 'Área / Asignaturas';
		}else{

			$lbl_asigatura = 'Asignaturas';
		}		
	?>

	@foreach($datos as $registro)
		
	    @include('calificaciones.boletines.banner_2')

		<?php 

			$lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

			$area_anterior = '';
			$cant_columnas = 3;
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido table-bordered">
			<thead>
				<tr>
					<th>{{ $lbl_asigatura }}</th>
					<th>I.H.</th>
					@if($curso->maneja_calificacion==1)
						<th>Calificación</th>
						<?php $cant_columnas++;  ?>
					@endif
					@if( $mostrar_fallas )
						<th>Fallas</th>
						<?php $cant_columnas++;  ?>
					@endif
					<th>Logros</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $lineas_cuerpo_boletin as $linea )

					@include('calificaciones.boletines.fila_area')

					<tr>

						<td> {{ $linea->asignacion_asignatura->asignatura->descripcion }} </td>
						
						<td align="center">
						    @if( $linea->asignacion_asignatura->intensidad_horaria != 0 )
								{{ $linea->asignacion_asignatura->intensidad_horaria }} &nbsp;
							@endif
						</td>

						@if( $curso->maneja_calificacion == 1)
							<td align="center"> 
								@if( !is_null( $linea->calificacion ) )
									@if( $linea->calificacion->calificacion > 0)
										@include('calificaciones.boletines.lbl_descripcion_calificacion')
									@endif
								@endif
							</td>
						@endif

						@if( $mostrar_fallas )
							<td align="center">
								@include('calificaciones.boletines.lbl_fallas')
							</td>
						@endif

						<td>
							@include('calificaciones.boletines.proposito')
							
							@include('calificaciones.boletines.lista_logros')

							@include('calificaciones.boletines.formatos.etiqueta_nombre_docente')
						</td>
					</tr>

					<?php 
						$area_anterior = $linea->asignacion_asignatura->asignatura->area->descripcion;
					?>

				@endforeach {{--  Asignaturas --}}

				@include('calificaciones.boletines.formatos.fila_observaciones')

				@include('calificaciones.boletines.formatos.fila_etiqueta_final')

			</tbody>

		</table>
		
		@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		
		@include('calificaciones.boletines.seccion_firmas')
		
		<div class="page-break"></div>
		
	@endforeach
@endsection