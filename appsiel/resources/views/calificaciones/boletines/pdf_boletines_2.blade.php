@extends( 'calificaciones.boletines.formatos.layout' )

<!-- PRESCOLAR -->
@section('contenido_formato')
	<style type="text/css">
		.boletin-preescolar .fila-desempenos {
			min-height: 86px;
		}

		.boletin-preescolar .celda-emoji {
			width: 118px;
			min-width: 118px;
			text-align: center;
			vertical-align: middle;
			padding: 2px 4px;
		}

		.boletin-preescolar .celda-desempenos {
			text-align: justify;
			vertical-align: middle;
			padding: 4px 14px 4px 8px;
			line-height: 1.15;
		}

		.boletin-preescolar .tabla-desempenos {
			table-layout: fixed;
		}
	</style>

	@foreach($datos as $registro)
		
	    @include('calificaciones.boletines.banner_2')

		<?php 

			$lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido table-bordered boletin-preescolar">
			<tbody>
				@foreach( $lineas_cuerpo_boletin as $linea )

					<?php
						$cant_columnas = 1;	
						if ($linea->asignatura_id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
							continue;
						}
					?>

					@include('calificaciones.boletines.fila_area')

					<tr style="background-color: #E8E8E8;">
						<td colspan="2"> 
							<table width="100%" style="border: 0px;">
								<tr>
									<td style="border: 0px;">
										<b>{{ $linea->asignatura_descripcion }} </b>
									</td>
									<td style="border: 0px;" width="35%">
										@if( $linea->intensidad_horaria != 0 )
											<b>IH: </b>{{ $linea->intensidad_horaria }} &nbsp;
										@endif
									</td>
								</tr>
							</table>					
						</td>
					</tr>

					<tr class="fila-desempenos" style="font-size: {{$tam_letra}}mm;">
						<td colspan="2">
							<table class="tabla-desempenos" width="100%" style="border: 0px;">
								<tr>
									<td class="celda-emoji" width="118" style="border: 0px;">
										@if( !is_null( $linea->calificacion ) )
											@if( $linea->calificacion->calificacion > 0)
												<img alt="emoji.jpg" src="{{ asset( config('configuracion.url_instancia_cliente') . "/storage/app/" . $linea->escala_valoracion->imagen ) }}" style="width: {{ 80 + $tam_letra * 3 }}px; height: {{ 80 + $tam_letra * 3 }}px ;" />
											@endif
										@endif
									</td>
									<td class="celda-desempenos" style="border: 0px;">
										@include('calificaciones.boletines.proposito')

										@include('calificaciones.boletines.lista_logros')

										@include('calificaciones.boletines.formatos.etiqueta_nombre_docente')
									</td>
								</tr>
							</table>
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
