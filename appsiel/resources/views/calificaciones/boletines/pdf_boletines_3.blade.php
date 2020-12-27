@extends( 'calificaciones.boletines.formatos.layout' )

@section('contenido_formato')

	@foreach($datos as $registro)
		
	    @include('calificaciones.boletines.banner_2')

		<?php 

			$lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido table-bordered">
			<tbody>
				@foreach( $lineas_cuerpo_boletin as $linea )
					<?php
						$cant_columnas = 1;	
					?>

					@include('calificaciones.boletines.fila_area')

					<tr style="background-color: #E8E8E8;">
						<td> 
							<table width="100%" style="border: 0px;">
								<tr>
									<td style="border: 0px;">
										{{ $linea->asignacion_asignatura->asignatura->descripcion }}
									</td>
									<td style="border: 0px;" width="35%">
										@if( $linea->asignacion_asignatura->intensidad_horaria != 0 )
											<b>IH: </b>{{ $linea->asignacion_asignatura->intensidad_horaria }} &nbsp;
										@endif
										
										@if( !is_null( $linea->calificacion ) )
											@if( $linea->calificacion->calificacion > 0)
												<b>Cal: </b> @include('calificaciones.boletines.lbl_descripcion_calificacion')
											@endif
										@endif
									</td>
								</tr>
							</table>					
						</td>
					</tr>

					<tr style="font-size: {{$tam_letra}}mm;">
						<td>

							@include('calificaciones.boletines.proposito')
                            
                            <b>Logro: </b>
							@include('calificaciones.boletines.lista_logros')

							@include('calificaciones.boletines.formatos.etiqueta_nombre_docente')

						</td>
					</tr>

					<?php 
						$area_anterior = $linea->asignacion_asignatura->asignatura->area->descripcion;
					?>

				@endforeach

				@include('calificaciones.boletines.formatos.fila_observaciones')

				@include('calificaciones.boletines.formatos.fila_etiqueta_final')
				
			</tbody>
		</table>

		@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		
		@include('calificaciones.boletines.seccion_firmas')

		<div class="page-break"></div>
	@endforeach {{-- Estudiante --}}
@endsection