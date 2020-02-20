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
		font-size: larger;
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

@if(count($estudiantes)>0)
	@foreach($estudiantes as $estudiante)

		{!! $banner !!}
				
		<table class="encabezado">
			<tr>
				<?php $observacion = DB::table('observaciones_boletines')
											->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
													'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
													'id_estudiante'=>$estudiante->id_estudiante])
											->get(); 
					$nombre_completo = $estudiante->apellido1.' '.$estudiante->apellido2.' '.$estudiante->nombres;
				?>
													
				@if($colegio->maneja_puesto=="Si")
					@if($curso->maneja_calificacion==1)
						@if(count($observacion)!=0)
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
					$calificacion = App\Calificaciones\Calificacion::where(['id_colegio'=>$colegio->id,'anio'=>$anio,
						'id_periodo'=>$periodo->id,'curso_id'=>$curso->id,
						'id_estudiante'=>$estudiante->id_estudiante,'id_asignatura'=>$asignatura->id])
						->get()->first();
					

					if ( count($calificacion) > 0 ) 
					{
						$escala = DB::table('sga_escala_valoracion')
						->where('calificacion_minima','<=',$calificacion->calificacion)
						->where('calificacion_maxima','>=',$calificacion->calificacion)->get();

						if ( count($escala) > 0) 
						{
							$escala = $escala[0];
						}else{
							$escala = (object) array('id' => 0, 'nombre_escala' => '<span style="color:red;">No hay escala de valoración para esta calificación.</span>');
						}

					}else{
						$calificacion = (object) array('calificacion' => 0);
						$escala = (object) array('id' => 0, 'nombre_escala' => '');
						//$escala = 'NO';
					}
					
					$desempeno = $escala->nombre_escala;					
					
					?>
					<tr style="font-size: {{$tam_letra}}mm;">
						<td> {{ $asignatura->descripcion }}</td>
						<td align="center"> {{ $asignatura->intensidad_horaria }}</td>
						@if($curso->maneja_calificacion==1)
							@if($asignatura->maneja_calificacion==1)
								@if(count($calificacion)!=0)
									<td align="center"> {{ $calificacion->calificacion }} ({{ $desempeno }}) </td>
								@else
									<td align="center"> &nbsp; </td>
								@endif
							@else
								<td align="center"> &nbsp; </td>
							@endif
						@endif							
						<td>
							<ul>
							<?php 

								if ( count($escala) > 0 ) 
								{
									$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('periodo_id',$periodo->id)->where('curso_id',$curso->id)->where('asignatura_id',$asignatura->id)->where('estado','Activo')->get();

									$n_nom_logros = count($logros);
								}else{
									$logros = (object) array('descripcion' => '');
									$n_nom_logros = 0;
								}

								$tbody = '';
								foreach($logros as $un_logro)
								{
									switch ($convetir_logros_mayusculas) {
										case 'Si':
											$tbody.='<li>'.strtoupper($un_logro->descripcion).'</li>';
											break;
										case 'No':
											$tbody.='<li>'.$un_logro->descripcion.'</li>';
											break;
										
										default:
											# code...
											break;
									}
									
								}

								echo $tbody;
							?>
							</ul>
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
						@if(count($observacion)!=0)
							{{ $observacion[0]->observacion }}
						@endif
						</td>
				</tr>
			</tbody>
		</table>

		<br/><br/><br/>

		<table border="0">
			<tr>
				<td width="50px"> &nbsp; </td>
				<td align="center">	_____________________________ </td>
				<td align="center"> &nbsp;	</td>
				<td align="center">	_____________________________ </td>
				<td width="50px">&nbsp;</td>
			</tr>
			<tr>
				<td width="50px"> &nbsp; </td>
				<td align="center">	{{ $colegio->piefirma1 }} </td>
				<td align="center"> &nbsp;	</td>
				<td align="center">	{{ $colegio->piefirma2 }} </td>
				<td width="50px">&nbsp;</td>
			</tr>
		</table>
		<div class="page-break"></div>
	@endforeach {{-- Estudiante --}}
@else
	No existen datos.
@endif