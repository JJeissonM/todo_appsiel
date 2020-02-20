@extends('layouts.principal')


<?php
	$personalizado_1 = '<div class="form-group">							
							'.Form::label('id_asignatura', 'Asignatura', ['class' => 'col-md-3']).Form::select('id_asignatura', [], null, array_merge(['class' => 'col-md-9','style'=>'border: none;border-color: transparent;border-bottom: 1px solid gray;'], [])).'
						</div>';

	$personalizado_2 = '<div id="guardar_y_nuevo">
							<label for="guardar_y_nuevo" class="col-sm-3 control-label">&nbsp;</label>
							<div class="checkbox">
								<label>
									<input type="checkbox" checked name="guardar_y_nuevo" id="guardar_y_nuevo"> Guardar y agregar nuevo
								</label>
							</div>
						</div>';

	$form_create = [
		'titulo' => 'Creación de un nuevo registro',
		'subtitulo' => '',
		'url' => '/calificaciones_logros',
		'campos' => [
						
						[
							'tipo' => 'bsText',
							'descripcion' => 'Código',
							'name' => 'codigo_aux',
							'value' => $consecutivo,
							'atributos' => ['disabled'=>'disabled'],
							'requerido' => true
						],
						[
							'tipo' => 'hidden',
							'name' => 'codigo',
							'descripcion' => '',
							'value' => $consecutivo,
							'atributos' => [],
							'requerido' => true
						],
						[
							'tipo' => 'personalizado',
							'descripcion' => '',
							'name' => '',
							'value' => '&nbsp;',
							'opciones' => '',
							'atributos' => [],
							'requerido' => true
						],
						[
							'tipo' => 'select',
							'descripcion' => 'Nivel académico',
							'name' => 'nivel',
							'value' => null,
							'opciones' => $niveles,
							'atributos' => ['id'=>'nivel','required'=>'required'],
							'requerido' => true
						],
						[
							'tipo' => 'personalizado',
							'value' => $personalizado_1,
							'descripcion' => '',
							'atributos' => [],
							'requerido' => true
						],
						[
							'tipo' => 'bsTextArea',
							'descripcion' => 'Descripción',
							'name' => 'descripcion',
							'value' => null,
							'atributos' => ['required'=>'required'],
							'requerido' => true
						],
						[
							'tipo' => 'select',
							'opciones' => $escala_valoracion,
							'name' => 'escala_valoracion_id',
							'descripcion' => 'Escala de valoración',
							'value' => null,
							'atributos' => ['required'=>'required'],
							'requerido' => true
						],
						[
							'tipo' => 'select',
							'opciones' => ['Activo'=>'Activo','Inactivo'=>'Inactivo'],
							'name' => 'estado',
							'descripcion' => 'Estado',
							'value' => null,
							'atributos' => [],
							'requerido' => true
						],
						[
							'tipo' => 'personalizado',
							'value' => $personalizado_2,
							'descripcion' => '',
							'atributos' => [],
							'requerido' => true
						],
						[
							'tipo' => 'hidden',
							'descripcion' => '',
							'name' => 'id_colegio',
							'value' => $id_colegio,
							'atributos' => [],
							'requerido' => true
						],
						
					]
					];

	$url_cancelar = 'calificaciones_logros?id='.Input::get('id');

?>


@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	{{Form::open(array('route'=>array('calificaciones.logros.update','buscar'),'method'=>'PUT','id'=>'form-buscar'))}}
		{!! Form::hidden('id_nivel','Ada', array('id' => 'id_nivel')) !!}
	{!! Form::close() !!}

	@include('core.vistas.form_create',compact('form_create','url_cancelar'))
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){
			$("#nivel").val('');
			$("#descripcion").val('');
			$("#nivel").val('');

			$("#nivel").on('change',function(){

				$("#id_asignatura").html('<option></option>');
			    $('#div_cargando').show();
				//alert("cambio");
				var nivel = $(this).val();
				var form = $('#form-buscar');
				var url = form.attr('action');
				$("#id_nivel").val(nivel);
				data = form.serialize();
				//alert(data);
				$.post(url,data,function(datos){
			    	$('#div_cargando').hide();
					$("#id_asignatura").html(datos);
				});
			});
		});
	</script>
@endsection