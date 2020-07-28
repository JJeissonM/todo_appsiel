<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

	table.encabezado{
		padding:5px;
		border: 1px solid;
	}

	table.banner{
		font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
		font-style: italic;
		font-size: 17px;
		border: 1px solid;
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

		<table class="banner">
            <tr style="height: 120px;">
                <td width="250px">
                    <img src="{{ $url.'?'.rand(1,1000) }}" width="150px" height="140px" />
                </td>
        
                <td align="center">
                    <b style="font-size: 1.2em;">{{ $colegio->descripcion }}</b>
                    <br/>
                    <b style="font-size: 1.1em;">{{ $colegio->ciudad }}</b>
                    <br/>
                    Resolución No. {{ $colegio->resolucion }}<br/>
                    {{ $colegio->direccion }}<br/>
                    Teléfonos: {{ $colegio->telefonos }}<br/>
                </td>
            </tr>
        </table>
				
		<table class="encabezado">
			<tr>
				<?php 
					$observacion = App\Calificaciones\ObservacionesBoletin::get_x_estudiante( $periodo->id, $curso->id, $estudiante->id);
				?>
													
				@if($colegio->maneja_puesto=="Si")

						@if( !is_null($observacion) )
							
							<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombre_completo }}</td>
							
							@if($observacion->puesto=="")
								<td> <b> ¡¡Puesto No calculado!! </b> </td>
							@else
								<td><span class="etiqueta"> Puesto:  </span> {{ $observacion->puesto }} </td>
							@endif

						@else
							<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombre_completo }}</td>
						@endif

				@else
					<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $estudiante->nombre_completo }}</td>
				@endif
				
			</tr>
			<tr>
				<td><span class="etiqueta">Periodo/Año:</span> {{ $periodo->descripcion }} &#47;  {{ $anio }}</td>
				<td><span class="etiqueta">Curso:</span> {{ $curso->descripcion }}</td>
				<td><span class="etiqueta">Ciudad:</span> {{ $colegio->ciudad }}</td>
			</tr>
		</table>
				
		<table class="contenido">
			<thead>
				<tr>
					<th>Asignaturas</th>
					<th>I.H.</th>
					@if( $curso->maneja_calificacion == 1 )
						<th>Calificación</th>
					@endif
					<th>Logros</th>
				</tr>
			</thead>
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id, $asignatura->id);

					?>
					<tr style="font-size: {{$tam_letra}}mm;">

						<td> {{ $asignatura->descripcion }}</td>
						
						<td align="center"> 
						    @if($asignatura->intensidad_horaria!=0) 
						        {{ $asignatura->intensidad_horaria }}
						    @endif
						</td>

						@if( $curso->maneja_calificacion == 1)
							<!-- se imprime la celda -->
							<td align="center"> 
								@if( $asignatura->maneja_calificacion == 1)
									<!-- se imprimie la calificación -->
									@include('calificaciones.boletines.lbl_descripcion_calificacion')
								@endif
							</td>
						@endif
						
						
						<td>
							@include('calificaciones.boletines.proposito')
							
							@include('calificaciones.boletines.lista_logros')
						</td>
					</tr>
				@endforeach {{--  Asignaturas --}}
				<tr style="font-size: {{$tam_letra}}mm;"> 
					@if($curso->maneja_calificacion)
						<td colspan="4">
					@else
						<td colspan="3">
					@endif
						<b> Observaciones: </b>
						<br/>&nbsp;&nbsp;
						@if( !is_null($observacion) )
							{{ $observacion->observacion }}
						@endif
						</td>
				</tr>
			</tbody>
		</table>

		@if( $mostrar_usuarios_estudiantes == 'Si') 
			@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		@endif

		@include('calificaciones.boletines.seccion_firmas')
		
		<div class="page-break"></div>
		
	@endforeach {{-- Estudiante --}}
@else
	No hay resgitros de estudiantes matriculados.
@endif