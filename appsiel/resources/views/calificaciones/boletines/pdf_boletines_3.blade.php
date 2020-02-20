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
		font-size: 16px;
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

	@foreach($estudiantes as $estudiante)
	
	    <table class="banner">
            <tr>
                <td rowspan="2" width="200px">
                    <img src="{{ $url.'?'.rand(1,1000) }}" height="90px" style="padding-top: -25px;"/>
                </td>
        
                <td align="center">
                    <br/>
                    <b style="font-size: 1.1em;">{{ $colegio->descripcion }}</b>
                    <br/>
                    <b style="font-size: 0.9em;">{{ $colegio->ciudad }}</b>
                    <br/>
                    Resolución No. {{ $colegio->resolucion }}<br/>
                    {{ $colegio->direccion }},Teléfono: {{ $colegio->telefonos }}
                </td>
            </tr>
        </table>

		<?php 

			$observacion = App\Calificaciones\ObservacionesBoletin::get_x_estudiante( $periodo->id, $curso->id, $estudiante->id);

			$nombre_completo = $estudiante->nombre_completo;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido">
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::get_la_calificacion($periodo->id, $curso->id, $estudiante->id_estudiante, $asignatura->id);
					
					?>
					<?php 
						if ( $area_anterior != $asignatura->area  AND $mostrar_areas == 'Si')
						{
					?>
						<tr style="font-size: {{$tam_letra}}mm; background-color: #CCCBCB;">
							<td>
								<b> ÁREA: {{ strtoupper($asignatura->area) }}</b>
							</td>
						</tr>

					<?php
						}
					?>

					<tr style="font-size: {{$tam_letra}}mm; background-color: #E8E8E8;">
						<td> 
							<div style="width: 65%; height: {{$tam_letra-3}}px; display: inline-block;">
								{{ $asignatura->descripcion }}
							</div>
							<div style="width: 35%; height: {{$tam_letra-3}}px; display: inline-block;">
								
								@if($asignatura->intensidad_horaria != 0)
									<b>IH: </b>{{ $asignatura->intensidad_horaria }} &nbsp;
								@endif
								
								<!-- @ if($asignatura->maneja_calificacion != 0)
									<b>Cal: </b>{ { $calificacion->valor }} ({ { $calificacion->escala_descripcion }})
								@ endif
								-->
								<b>Cal: </b>{{ $calificacion->valor }} ({{ $calificacion->escala_descripcion }})
							</div>					
						</td>
					</tr>

					<tr style="font-size: {{$tam_letra}}mm;">
						<td>

							@include('calificaciones.boletines.proposito')
                            
                            <b>Logro: </b>
							@include('calificaciones.boletines.lista_logros')

							<?php
								if ( $mostrar_nombre_docentes == 'Si') 
								{
									
									$usuario = App\AcademicoDocente\AsignacionProfesor::get_user_segun_curso_asignatura( $curso->id, $asignatura->id, $periodo->periodo_lectivo_id);

									//dd($usuario->name);
									
									$nombre = '';

									if ( !is_null($usuario) ) 
									{
										$nombre = $usuario->name;
									}

							?>
								<span style="display: inline-block;text-align: right;">
									<b>docente: </b> {{ ucwords( ($nombre) ) }} <!-- strtolower -->
								</span>
							<?php } ?>
						</td>
					</tr>

					<?php 
						$area_anterior = $asignatura->area;
					?>

				@endforeach {{--  Asignaturas --}}

				<tr style="font-size: {{$tam_letra}}mm;">
					<td>
						<b> Observaciones: </b>
						<br/>&nbsp;&nbsp;
						@if( !is_null($observacion) )
							{{ $observacion->observacion }}
						@endif
					</td>
				</tr>
			</tbody>
		</table>

		@include('calificaciones.boletines.seccion_firmas')

		<div class="page-break"></div>
	@endforeach {{-- Estudiante --}}