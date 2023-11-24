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
				<?php $observacion = DB::table('observaciones_boletines')
											->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
													'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
													'id_estudiante'=>$estudiante->id_estudiante])
											->get();
					//dd($observacion);
					$nombre_completo = $estudiante->apellido1.' '.$estudiante->apellido2.' '.$estudiante->nombres;
				?>
													
				@if($colegio->maneja_puesto=="Si")
					@if($curso->maneja_calificacion==1)
						@if( !empty($observacion) )
							@if($observacion[0]->puesto=="")
								<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
								<td><span class="etiqueta"> ¡¡Puesto </span> No calculado!! </td>
							@else
								<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
								<td><span class="etiqueta"> Puesto:  </span> {{ $observacion[0]->puesto }} </td>
							@endif
						@else
							<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
						@endif
					@else
						<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
					@endif
				@else
					<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
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
					@if($curso->maneja_calificacion==1)
						<th>Calificación</th>
					@endif
					<th>Logros</th>
				</tr>
			</thead>
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::get_promedio_periodos($periodos_promediar, $curso->id, $estudiante->id_estudiante, $asignatura->id);

					?>
					<tr style="font-size: {{$tam_letra}}mm;">
						<td> {{ $asignatura->descripcion }}</td>
						<td align="center"> 
						    @if($asignatura->intensidad_horaria!=0) 
						        {{ $asignatura->intensidad_horaria }}
						    @endif
						</td>

						<td align="center"> {{ $calificacion->valor }} ({{ $calificacion->escala_descripcion }}) </td>
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
						@if( !empty($observacion) )
							{{ $observacion[0]->observacion }}
						@endif
						</td>
				</tr>
			</tbody>
		</table>

		@include('calificaciones.boletines.resultado_anio_escolar')

		@include('calificaciones.boletines.seccion_firmas')
		
		@if($with_page_breaks)
			<div class="page-break"></div>	
		@endif
	@endforeach {{-- Estudiante --}}
@else
	No hay resgitros de estudiantes matriculados.
@endif