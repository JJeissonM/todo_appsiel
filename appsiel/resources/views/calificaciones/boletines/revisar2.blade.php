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

					@if( config('calificaciones.manejar_calificaciones_por_niveles_de_desempenios') == 'Si' )
						@include('calificaciones.boletines.revisar2_desempenios_encabezado_y_filas_asignaturas')
					@else
						@include('calificaciones.boletines.revisar2_encabezado_y_filas_asignaturas')
					@endif
					
						<tr> 
							@if(!is_null($observacion))
								<td <?php if($observacion->observacion==""){echo $estilo_advertencia;}?>>
									
									Observaciones:
									<br><br><br><br>

								</td>
								<td colspan="2" <?php if($observacion->observacion==""){echo $estilo_advertencia;}?>>
									
									{{ Form::textarea("observacion_".$estudiante->id, $observacion->observacion, ['class' => 'form-control textarea_observacion', 'rows' => '4', 'readonly'=>'readonly','title'=>'Doble click para editar.'] ) }}


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
									
									{{ Form::textarea("observacion_".$estudiante->id, null, ['class' => 'form-control textarea_observacion', 'rows' => '4', 'readonly'=>'readonly','title'=>'Doble click para editar.'] ) }}

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