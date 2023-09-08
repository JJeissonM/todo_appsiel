<div style="padding: 2px; display: inline-block;">
	<table class="table table-bordered" width="100%">
	    <tr>
	        <td width="100px">
	            <img src="{{ $url }}" height="50px"/>
	        </td>

	        <td align="center">
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->descripcion }}</b>
	            <br/>
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->slogan }}</b>
	            <br/>
	            <b style="font-size: {{$tam_letra}}mm;">{{ $colegio->ciudad }}</b>
	        </td>
	    </tr>
	    <tr>
	    	<td colspan="2">
	    		<div style="text-justify: auto; width: 100%; font-size: {{$tam_letra}}mm;">
					PRE-INFORME DE RENDIMIENTO ACADÉMICO CORRESPONDIENTE AL PERIODO  <b>{{ $periodo->descripcion }}</b> de {{ $anio }}
					<br>
					<b>Estudiante:</b> {{ $registro->estudiante->tercero->descripcion }} &nbsp;&nbsp; | &nbsp;&nbsp; <b>Curso:</b> {{ $curso->descripcion }}
				</div>
	    	</td>
	    </tr>
	</table>

	<?php
		$area_anterior = '';
		$fila = 1;
	?>

	<div style="font-size: {{$tam_letra}}mm;">
		<table class="table table-striped" width="100%">
			<thead>
				<tr>
					<th>Asignatura</th>
					<th>Cal.</th>
					<th>Anotación</th>
				</tr> 
			</thead>
			<tbody>
				@foreach( $lineas_cuerpo_boletin as $linea )

					<tr>
						<td style="border: 0px;">
							<b>{{ $linea->asignacion_asignatura->asignatura->descripcion }} </b>
						</td>
						<td style="border: 0px;" width="35%">
							@if( !is_null( $linea->calificacion ) )
								@if( $linea->calificacion->calificacion > 0)
									<b>Cal: </b> @include('calificaciones.boletines.lbl_descripcion_calificacion')
								@endif
							@endif
						</td>
						<td>
							{{ $linea->anotacion }}
						</td>
					</tr>

				@endforeach {{--  Asignaturas --}}
			</tbody>
		</table>


		@if( $mostrar_usuarios_estudiantes == 'Si') 
			@include('calificaciones.boletines.mostrar_usuarios_estudiantes')
		@endif

	</div>
</div>