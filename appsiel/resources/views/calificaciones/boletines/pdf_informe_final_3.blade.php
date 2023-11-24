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

			$observacion = DB::table('observaciones_boletines')
					->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
							'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
							'id_estudiante'=>$estudiante->id_estudiante])
					->get(); 
			$nombre_completo = $estudiante->apellido1.' '.$estudiante->apellido2.' '.$estudiante->nombres;

			$area_anterior = '';
		?>
		
		@include('calificaciones.boletines.encabezado_2')
				
		<table class="contenido">
			<tbody>
				@foreach($asignaturas as $asignatura)
					<?php
					// Se llama a la calificacion de cada asignatura
					$calificacion = App\Calificaciones\Calificacion::get_promedio_periodos($periodos_promediar, $curso->id, $estudiante->id_estudiante, $asignatura->id);
					
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
									
									$asignacion = App\AcademicoDocente\AsignacionProfesor::where('curso_id',$curso->id)->where('id_asignatura',$asignatura->id)->get();

									if ( !is_null($asignacion) ) 
									{
										$usuario = App\User::find($asignacion[0]->id_user);
									}else{
										$usuario = (object)['name' => ''];
									}

							?>
								<span style="display: inline-block;text-align: right;">
									<b>docente: </b> {{ ucwords(strtolower($usuario->name)) }}
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