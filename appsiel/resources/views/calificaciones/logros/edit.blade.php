@extends('layouts.principal')

<?php
	$form_create = [
						'campos' => [
										[
											'tipo' => 'bsLabel',
											'value' => $nom_asignatura,
											'name' => 'asignatura_id',
											'atributos' => [],
											'requerido' => true,
											'descripcion' =>' Asignatura'
										],
										[
											'tipo' => 'bsTextArea',
											'descripcion' => 'Descripción',
											'name' => 'descripcion',
											'value' => null,
											'atributos' => [],
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
								'tipo' => 'hidden',
								'name' => 'id',
								'descripcion' => '',
								'value' => $registro->id,
								'atributos' => [],
								'requerido' => true
							],										
									]
					];

	$url_cancelar = 'calificaciones_logros?id='.Input::get('id');
	$route_update = 'calificaciones_logros';

?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	@include('core.vistas.form_edit',compact('form_create','url_cancelar'))

@endsection