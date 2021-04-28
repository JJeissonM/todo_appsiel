@extends( 'calificaciones.boletines.formatos.layout' )

<!-- MODERNO -->
@section('contenido_formato')

	@foreach($datos as $registro)
		
	    @include('calificaciones.boletines.banner_2')

		<?php 

			$lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')

		@foreach( $lineas_cuerpo_boletin as $linea )		
		<table class=" table-bordered">
			<tbody> 
				
					<?php
						$cant_columnas = 2;	
					?>

					@include('calificaciones.boletines.fila_area')

					<tr style="background-color: #E8E8E8;">						
						<!--<td colspan="{{$cant_columnas}}">
							<table width="100%" style="border: 0px;">
								<tr>-->
									<td style="border: 0px;">
										{{ $linea->asignacion_asignatura->asignatura->descripcion }}
									</td>
									<td style="border: 0px;" width="45%">
										@if( $linea->asignacion_asignatura->intensidad_horaria != 0 )
											<b>IH: </b>{{ $linea->asignacion_asignatura->intensidad_horaria }} &nbsp;
										@endif
										
										@if( !is_null( $linea->calificacion ) )
											@if( $linea->calificacion->calificacion > 0)
												<b>Cal: </b> @include('calificaciones.boletines.lbl_descripcion_calificacion')
											@endif
										@endif

										@if( $mostrar_fallas )
											<b>Fallas: </b> @include('calificaciones.boletines.lbl_fallas')
										@endif
									</td>
								<!--</tr>
							</table>					
						</td>-->
					</tr>

					<tr style="font-size: {{$tam_letra}}mm;">
						<td colspan="{{$cant_columnas}}">

							@include('calificaciones.boletines.proposito')
                            
                            <b>Logro: </b>
							@include('calificaciones.boletines.lista_logros')

							@include('calificaciones.boletines.formatos.etiqueta_nombre_docente')

						</td>
					</tr>

					<?php 
						$area_anterior = $linea->asignacion_asignatura->asignatura->area->descripcion;
					?>
						</tbody>
					</table>
				@endforeach
				
<table>
	@include('calificaciones.boletines.formatos.fila_observaciones')

	@include('calificaciones.boletines.formatos.fila_etiqueta_final')
</table>
				
				
			

		@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		
		@include('calificaciones.boletines.seccion_firmas')

		<div class="page-break"></div>
	@endforeach {{-- Estudiante --}}
@endsection