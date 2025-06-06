@extends( 'calificaciones.boletines.formatos.layout' )

<!-- MODERNO -->
@section('contenido_formato')

	@foreach($datos as $registro)
		
	    @include('calificaciones.boletines.banner_2_con_foto')

		<?php 

			$lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')
	<table class="table-bordered">
			<tbody> 	
		@foreach( $lineas_cuerpo_boletin as $linea )
				
					<?php
						$cant_columnas = 2;
						if ($linea->asignatura_id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
							continue;
						}

						$porcentaje_asignatura = '';
						if( $mostrar_calificacion_media_areas )
						{
							$porcentaje_asignatura = ' (' . $linea->peso_asignatura . '% )';
						}
					?>

					@include('calificaciones.boletines.fila_area')

					<tr style="background-color: {{config('configuracion.color_principal_empresa')}}50;">
						<td style="border: 0px;">
							<b>{{ $linea->asignatura_descripcion }} </b> {{ $porcentaje_asignatura }}
						</td>
						<td style="border: 0px;" width="45%">
							@if( $linea->intensidad_horaria != 0 )
								<b>IH: </b>{{ $linea->intensidad_horaria }} &nbsp;
							@endif

							@if( !is_null( $linea->calificacion ) )
								@if( $linea->calificacion->calificacion > 0 && $curso->maneja_calificacion)
									<b>Cal: </b> @include('calificaciones.boletines.lbl_descripcion_calificacion')
								@endif
							@endif

							@if( $mostrar_fallas )
								<b>Fll: </b> @include('calificaciones.boletines.lbl_fallas')
							@endif
						</td>
					</tr>

					<tr style="font-size: {{$tam_letra}}mm;">
						<td colspan="{{$cant_columnas}}" style=" text-align: justify;">

							@include('calificaciones.boletines.proposito')
                            
							@include('calificaciones.boletines.lista_logros')

							@include('calificaciones.boletines.formatos.etiqueta_nombre_docente')

						</td>
					</tr>

					<?php 
						$area_anterior = $linea->area_descripcion;
					?>
						
					
				@endforeach
	
			@include('calificaciones.boletines.formatos.fila_observaciones')

			@include('calificaciones.boletines.formatos.fila_etiqueta_final')

	</tbody>
</table>			
				
			

		@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		
		@include('calificaciones.boletines.seccion_firmas')
		
		{!! generado_por_appsiel() !!}

		@if($with_page_breaks)
			<div class="page-break"></div>	
		@endif
	@endforeach {{-- Estudiante --}}
@endsection