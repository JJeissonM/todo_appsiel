@extends( 'calificaciones.boletines.formatos.layout' )

@section('contenido_formato')

	<?php
	    if ( $mostrar_areas == 'Si')
		{
			$lbl_asigatura = 'Área / Asignaturas';
		}else{

			$lbl_asigatura = 'Asignaturas';
		}		

		$mostrar_calificacion_requerida = true;

		$label_columna = 'Prom.';
		if ( $mostrar_calificacion_requerida ) {
			$label_columna = 'Requerida';
		}
	?>

	@foreach($datos as $registro)
		
	    @include('calificaciones.boletines.banner_2')

		<?php 

			$lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

			$area_anterior = '';
			$cant_columnas = 1;
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido table-bordered">
			<thead>
				<tr>
					<th style="width:200px;">{{ $lbl_asigatura }}</th>
                    
					@if($mostrar_intensidad_horaria)
						<th>I.H.</th>
						<?php $cant_columnas++;  ?>
					@endif

					@if($curso->maneja_calificacion==1)
				        @foreach($periodos as $periodo_lista)
				            <th style="text-align: center; width: 28px;"> P{{$periodo_lista->numero}} </th>
							<?php $cant_columnas++;  ?>
				        @endforeach
				        <th style="text-align: center; width: 28px;"> {{ $label_columna }} </th>
						<?php $cant_columnas++; ?>
					@endif

					@if( $mostrar_fallas )
						<th style="width:35px;">Fll.</th>
						<?php $cant_columnas++;  ?>
					@endif
					
                    @if($mostrar_logros)
						<th>Logros</th>
						<?php $cant_columnas++;  ?>
					@endif
				</tr>
			</thead>
			<tbody>
				@foreach( $lineas_cuerpo_boletin as $linea )
					<?php
						if ($linea->asignatura_id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
							continue;
						}
					?>

					@include('calificaciones.boletines.fila_area_por_periodos')

					<tr>

						<td style="width:150px;">
							<b>{{ $linea->asignatura_descripcion }} </b>
						</td>
						
						@if($mostrar_intensidad_horaria)
							<td style="text-align: center; width: 28px;">
								@if($linea->intensidad_horaria != 0) 
									{{ $linea->intensidad_horaria }}
								@endif
							</td>
						@endif

						@if( $curso->maneja_calificacion == 1)
							@include('calificaciones.boletines.lbls_descripciones_calificaciones_periodos')
						@endif

						@if( $mostrar_fallas )
							<td style="text-align: center; width: 28px;">
								@include('calificaciones.boletines.lbl_fallas')
							</td>
						@endif
						
						@if($mostrar_logros)
							<td style="text-align: justify;">								
								@include('calificaciones.boletines.proposito')
								
								@include('calificaciones.boletines.lista_logros')

								@include('calificaciones.boletines.formatos.etiqueta_nombre_docente')
							</td>
						@endif
					</tr>

					<?php
						$area_anterior = $linea->area_descripcion;
					?>

				@endforeach {{--  Asignaturas --}}

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
		
	@endforeach
@endsection