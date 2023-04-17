<style>
	div.contenido1 {
		text-align: center;
	}
	
	table {
		width:100%;
		border: 1px solid #ddd;
	}
	
	th {
		background-color: #CACACA;
	}
	
	table.encabezado {
		background-color: #98A8B5;
	}
	
	table.contenido td {
		border: 1px solid #ddd;
	}
	
	span.etiqueta{
		font-weight: bold;
		display: inline-block;
		width: 100px;
	}
	
	span.warning{
		font-weight: bold;
		display: inline-block;
		width: 100px;
		background-color:orange;
	}
	
	hr {
		border-color: orange;
	}
	
	
</style>

<div class="contenido1 container-fluid">

	<h3 align="center">Revisión de informes</h3>

	<h4 align="center">Se hallaron <?php echo count($estudiantes);?> estudiantes matriculados</h4>
	<?php 

		$decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');

		$estilo_advertencia = 'style="background-color:#F08282; color:white;"';
	
		// Se recorre cada estudiante matriculado
		foreach($estudiantes as $estudiante) {
			
				?>
				<table class="table table-striped">
					<tr>
						<td><span class="etiqueta">Estudiante</span> {{ $estudiante->nombre_completo }} </td>
						<td><span class="etiqueta">Periodo/Año</span> {{ $periodo->descripcion }} &#47;  {{ $anio }}</td>
					</tr>
					<tr>
						<td><span class="etiqueta">Curso</span> {{ $estudiante->curso_descripcion }}</td>
						<td><span class="etiqueta">Ciudad</span> {{ $colegio->ciudad }}</td>
					</tr>
					<?php 
						// La tabla observaciones_boletines guarda el puesto calculado para cada estudiante
						$observacion = $observaciones->where('id_estudiante',$estudiante->id_estudiante)->first(); 
					?>
					@if($colegio->maneja_puesto=="Si")
						@if(!is_null($observacion))
							<tr>
								@if($observacion->puesto == "" )
									<td {{$estilo_advertencia}}>
								@else
									<td>
								@endif
										<span class="etiqueta">Puesto:</span> {{ $observacion->puesto }}
									</td>
								<td><span class="etiqueta">&nbsp; </span> &nbsp; </td>
							</tr>
						@else
							<tr>					
								<td colspan="2"><span class="warning">Puesto:</span> No calculado. </td>
							</tr>
						@endif
					@endif
				</table>
				
				<table class="contenido table table-striped">
					<thead>
						<tr>
							<th>Asignaturas</th>
							<th>Calificación</th>
							<th>Logros</th>
						</tr>
					</thead>
					<tbody>
						<?php

							$tbody = '';
							foreach($asignaturas as $asignatura) 
							{
								if ($asignatura->id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
									continue;
								}
								
								// Se llama a la calificacion de cada asignatura (en la colección de calificaciones) 
								$obj_calificacion = $calificaciones->where('id_estudiante',$estudiante->id_estudiante)->where('id_asignatura',$asignatura->id)->first();
								
								$calificacion = 0;
								$escala = (object) array('id' => 0, 'nombre_escala' => '');
								
								$lbl_nivelacion = '';
								
								// Se calcula el texto de la calificación
								if ( !is_null($obj_calificacion) ) 
								{
									$calificacion = $obj_calificacion->calificacion;
									if ( !is_null( $obj_calificacion->nota_nivelacion() ) )
									{
										$calificacion = $obj_calificacion->nota_nivelacion()->calificacion;
										$lbl_nivelacion = 'n';
									}

									$escala = App\Calificaciones\EscalaValoracion::where('calificacion_minima','<=',$calificacion)
													->where('calificacion_maxima','>=',$calificacion)
													->where('periodo_lectivo_id','=',$periodo->periodo_lectivo_id)->first();									
								}

								$tbody.='<tr>
										<td width="350px" title="ID: '.$asignatura->id.'">'.$asignatura->descripcion .'</td>';

								if( $calificacion == 0)
								{
									$tbody.='<td '.$estilo_advertencia.'>&nbsp;</td>';
								}else{
									if ( is_null($escala) ) 
									{
										$escala = (object) array('id' => 0, 'nombre_escala' => '');
									}
									$tbody.='<td>' . number_format( (float)$calificacion, $decimales, ',', '.' ).'<sup>' . $lbl_nivelacion . '</sup> ('.$escala->nombre_escala.')</td>';
								}
								
								$tbody .=  \View::make('calificaciones.boletines.revisar2_incluir_celda_logros',[
												'escala'=>$escala,'periodo_id'=>$periodo->id,'curso_id'=>$estudiante->curso_id,'asignatura_id'=>$asignatura->id, 'obj_calificacion' => $obj_calificacion, 'id_estudiante' => $estudiante->id])->render();

								$tbody.='</tr>';

							} //fin recorrido de asignaturas del estudiante

							echo $tbody;						
						?>
						<tr> 
							@if(!is_null($observacion))
								<td <?php if($observacion->observacion==""){echo $estilo_advertencia;}?>>
									
									Observaciones:
									<br><br><br><br>

								</td>
								<td colspan="2" <?php if($observacion->observacion==""){echo $estilo_advertencia;}?>>
									
									{{ Form::textarea("observacion_".$estudiante->id, $observacion->observacion, ['class' => 'form-control', 'rows' => '4', 'disabled'=>'disabled','title'=>'Doble click para editar.'] ) }}


									<button class="btn btn-default pull-left btn-sm btn_guardar_observacion" title="Guardar observación" style="display: none;" data-codigo_matricula="{{$estudiante->codigo}}" data-id_colegio="{{ $colegio->id }}" data-id_periodo="{{ $periodo->id }}" data-curso_id="{{ $estudiante->curso_id }}" data-id_estudiante="{{$estudiante->id_estudiante}}" data-observacion_id="{{ $observacion->id }}">
										<i class="fa fa-btn fa-save"></i>
									</button>
									<code class="pull-right" style="display: none;">Guardando...</code>
								</td>

							@else

								<td>
									
									<span class="warning" >Observaciones:</span>
									<br><br><br><br>

								</td>
								<td colspan="2">
									
									{{ Form::textarea("observacion_".$estudiante->id, null, ['class' => 'form-control', 'rows' => '4', 'disabled'=>'disabled','title'=>'Doble click para editar.'] ) }}

									<button class="btn btn-default pull-left btn-sm btn_guardar_observacion" title="Guardar observación" style="display: none;" data-codigo_matricula="{{$estudiante->codigo}}" data-id_colegio="{{ $colegio->id }}" data-id_periodo="{{ $periodo->id }}" data-curso_id="{{ $estudiante->curso_id }}" data-id_estudiante="{{$estudiante->id_estudiante}}" data-observacion_id="no">
										<i class="fa fa-btn fa-save"></i>
									</button>
									<code class="pull-right" style="display: none;">Guardando...</code>
								</td>
							@endif
							
						</tr>
					</tbody>
				</table>


				<br/><br/>
				<?php
			
		} //fin foreach estudiantes
	?>	
				
	{{ Form::open(['url'=>'calificaciones/guardar_observacion','method'=>'POST','id'=>'form_auxiliar']) }}
			{{ Form::hidden('codigo_matricula', null, ['id' =>'codigo_matricula']) }}
			{{ Form::hidden('id_colegio', null, ['id' =>'id_colegio']) }}
			{{ Form::hidden('id_periodo', null, ['id' =>'id_periodo']) }}
			{{ Form::hidden('curso_id', null, ['id' =>'curso_id']) }}
			{{ Form::hidden('id_estudiante', null, ['id' =>'id_estudiante']) }}
			{{ Form::hidden('observacion', null, ['id' =>'observacion']) }}
			{{ Form::hidden('observacion_id', null, ['id' =>'observacion_id']) }}
	{{ Form::close() }}

</div>