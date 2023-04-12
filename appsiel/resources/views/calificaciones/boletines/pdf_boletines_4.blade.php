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
					<th style="width:200px;">{{ $lbl_asigatura }}</th>
					<th style="width:40px;">I.H.</th>
					@if($curso->maneja_calificacion==1)
				        @foreach($periodos as $periodo_lista)
				            <th style="width:35px;"> P{{$periodo_lista->numero}} </th>
							<?php $cant_columnas++;  ?>
				        @endforeach
				        <th style="width:35px;"> Prom. </th>
						<?php $cant_columnas++; ?>
					@endif
					@if( $mostrar_fallas )
						<th style="width:35px;">Fll.</th>
						<?php $cant_columnas++;  ?>
					@endif
					<th>Logros</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $lineas_cuerpo_boletin as $linea )
					<?php
						if ($linea->asignacion_asignatura->asignatura->id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
							continue;
						}
					?>

					@include('calificaciones.boletines.fila_area')

					<tr>

						<td style="width:150px;"> {{ $linea->asignacion_asignatura->asignatura->descripcion }} </td>
						
						<td align="center">
						    @if( $linea->asignacion_asignatura->intensidad_horaria != 0 )
								{{ $linea->asignacion_asignatura->intensidad_horaria }} &nbsp;
							@endif
						</td>

						@if( $curso->maneja_calificacion == 1)
							@include('calificaciones.boletines.lbls_descripciones_calificaciones_periodos')
						@endif

						@if( $mostrar_fallas )
							<td align="center" style="width:50px;">
								@include('calificaciones.boletines.lbl_fallas')
							</td>
						@endif

						<td style="text-align: justify;">
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
		
		{!! generado_por_appsiel() !!}
		
		<div class="page-break"></div>
		
	@endforeach
@endsection