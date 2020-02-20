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

	@foreach($boletines as $boletin)

		@include('banner_colegio')
				
		<table class="encabezado">
			<tr>													
				@if( $colegio->maneja_puesto == "Si" )

					<td colspan="2"><span class="etiqueta">Estudiante:</span> {{ $boletin->estudiante_nombre_completo }}</td>
					
					@if( $boletin->puesto == "" )
						<td> <span class="etiqueta"> ¡¡Puesto No calculado!! </span> </td>
					@else
						<td><span class="etiqueta"> Puesto:  </span> {{ $boletin->puesto }} </td>
					@endif

				@else
					<td colspan="3"><span class="etiqueta">Estudiante:</span> {{ $nombre_completo }}</td>
				@endif
				
			</tr>
			<tr>
				<td><span class="etiqueta">Periodo/Año:</span> {{ $boletin->periodo_descripcion }} &#47;  {{ $anio }}</td>
				<td><span class="etiqueta">Curso:</span> {{ $boletin->curso_descripcion }}</td>
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
				@foreach($boletin->asignaturas as $asignatura)

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
						@if( !is_null($observacion) )
							{{ $observacion[0]->observacion }}
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