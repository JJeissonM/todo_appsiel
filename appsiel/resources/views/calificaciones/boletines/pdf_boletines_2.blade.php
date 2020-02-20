<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

	table.encabezado{
		border: 1px solid;
		padding-top: -20px;
	}

	table.banner{
		font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
		font-style: italic;
		font-size: larger;
		border: 1px solid;
		padding-top: -20px;
	}

	table.contenido td {
		border: 1px solid;
	}

	th {
		background-color: #E0E0E0;
		border: 1px solid;
	}

	ul{
		padding:0px;
		margin:0px;
	}

	li{
		list-style-type: none;
	}

	span.etiqueta{
		font-weight: bold;
		display: inline-block;
		width: 100px;
		text-align:right;
	}

	.page-break {
		page-break-after: always;
	}
</style>

	<?php
	    $colegio = App\Core\Colegio::where('empresa_id','=', Auth::user()->empresa_id )
	                    ->get()[0];

	    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;


	?>

@if( !is_null($estudiantes) )
	@foreach($estudiantes as $estudiante)
		@include('calificaciones.boletines.banner_2')

		<?php 

			$observacion = App\Calificaciones\ObservacionesBoletin::get_x_estudiante( $periodo->id, $curso->id, $estudiante->id);

			$nombre_completo = $estudiante->nombre_completo;

			$area_anterior = '';

			if ( $mostrar_areas == 'Si')
			{
				$lbl_asigatura = 'Área / Asignaturas';
			}else{

				$lbl_asigatura = 'Asignaturas';
			}
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido">
			<thead>
				<tr>
					<?php $cant_columnas = 3;  ?>
					<th>{{ $lbl_asigatura }}</th>
					<th>I.H.</th>
					@if($curso->maneja_calificacion==1)
						<th>Calificación</th>
						<?php $cant_columnas++;  ?>
					@endif
					<th>Logros</th>
				</tr>
			</thead>
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id_estudiante, $asignatura->id);					
					?>
					<?php 
						if ( $area_anterior != $asignatura->area AND $mostrar_areas == 'Si')
						{
					?>
						<tr style="font-size: {{$tam_letra}}mm;">
							<td colspan="{{$cant_columnas}}">
								<b> ÁREA: {{ strtoupper($asignatura->area) }}</b>
							</td>
						</tr>

					<?php
						}
					?>
					<tr style="font-size: {{$tam_letra}}mm;">
						<td> 
							{{ $asignatura->descripcion }}
						</td>
						<td align="center">
						    @if($asignatura->intensidad_horaria!=0) 
						        {{ $asignatura->intensidad_horaria }}
						    @endif
						</td>
							@if( $calificacion->valor != 0 )
								<td align="center"> {{ $calificacion->valor }} ({{ $calificacion->escala_descripcion }}) </td>
							@else
								<td align="center"> &nbsp; </td>
							@endif
						<td>
							
							@include('calificaciones.boletines.proposito')
							
							@include('calificaciones.boletines.lista_logros')
						</td>
					</tr>

					<?php 
						$area_anterior = $asignatura->area;
					?>

				@endforeach {{--  Asignaturas --}}

				<tr style="font-size: {{$tam_letra}}mm;"> 
					@if($curso->maneja_calificacion)
						<td colspan="4">
					@else
						<td colspan="3">
					@endif
						<b> Observaciones: </b>
						<br/>&nbsp;&nbsp;
						@if( !is_null( $observacion ) )
							{{ $observacion->observacion }}
						@endif
						</td>
				</tr>
			</tbody>
		</table>

		@include('calificaciones.boletines.seccion_firmas')
		
		<div class="page-break"></div>
	@endforeach {{-- Estudiante --}}
@else
	No hay resgitros de estudiantes matriculados.
@endif