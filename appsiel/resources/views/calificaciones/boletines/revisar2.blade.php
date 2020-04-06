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

<!-- JQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>


<div class="contenido1 container-fluid">

	<h3 align="center">Revisión de boletines</h3>

	<h4 align="center">Se hallaron <?php echo count($estudiantes);?> estudiantes matriculados</h4>
	<?php 

		$estilo_advertencia = 'style="background-color:#F08282; color:white;"';
	
		// Se recorre cada estudiante matriculado
		foreach($estudiantes as $estudiante) {
			
				?>
				<table  class="encabezado">
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
				
				<table class="contenido">
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
								
								// Se llama a la calificacion de cada asignatura (en la colección de calificaciones) 
								$obj_calificacion = $calificaciones->where('estudiante_id',$estudiante->id_estudiante)->where('asignatura_id',$asignatura->id)->first();
								
								$calificacion = 0;
								$escala = (object) array('id' => 0, 'nombre_escala' => '');
								
								// Se calcula el texto de la calificación
								if ( !is_null($obj_calificacion) ) 
								{
									$calificacion = $obj_calificacion->calificacion;
									$escala = App\Calificaciones\EscalaValoracion::where('calificacion_minima','<=',$calificacion)
													->where('calificacion_maxima','>=',$calificacion)
													->where('periodo_lectivo_id','=',$periodo->periodo_lectivo_id)->first();									
								}

								$tbody.='<tr>
										<td width="350px">'.$asignatura->descripcion.'</td>';

								if( $calificacion == 0)
								{
									$tbody.='<td '.$estilo_advertencia.'>&nbsp;</td>';
								}else{
									$tbody.='<td>'.$calificacion.'( '.$escala->nombre_escala.')</td>';
								}/**/

								if ( !is_null($escala) ) 
								{
									$logros = App\Calificaciones\Logro::where('escala_valoracion_id',$escala->id)->where('periodo_id',$periodo->id)->where('curso_id',$estudiante->curso_id)->where('asignatura_id',$asignatura->id)->where('estado','Activo')->get();

									$n_nom_logros = count($logros);
								}else{
									$logros = (object) array('descripcion' => '');
									$n_nom_logros = 0;
								}
								
								$tbody.='<td ';if($n_nom_logros==0){ $tbody.=$estilo_advertencia;}
								$tbody.='>
										<ul>';
										foreach($logros as $un_logro)
										{
											$tbody.='<li>'.$un_logro->descripcion.'</li>';
										}		
								$tbody.='</ul>
										</td>
											</tr>';

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