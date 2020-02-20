@extends('layouts.principal')

<?php
	$form_create = [
						'titulo' => 'Cambio de contraseña',
						'subtitulo' => '',
						'url' => '/core/usuario/perfil/cambiar_mi_passwd',
						'campos' => [
										[
											'tipo' => 'password',
											'descripcion' => 'Contraseña actual',
											'name' => 'password',
											'value' => null,
                                            'atributos' => [],
                                            'requerido' => true
										],
										[
											'tipo' => 'personalizado',
											'descripcion' => '',
											'value' => '<br/><br/>',
                                            'atributos' => [],
                                            'requerido' => true
										],
										[
											'tipo' => 'password',
											'descripcion' => 'Nueva Contraseña',
											'name' => 'password_new',
											'value' => null,
                                            'atributos' => [],
                                            'requerido' => true
										],
										[
											'tipo' => 'personalizado',
											'descripcion' => '',
											'value' => '<br/><br/>',
                                            'atributos' => [],
                                            'requerido' => true
										],
										[
											'tipo' => 'password',
											'descripcion' => 'Repertir nueva Contraseña',
											'name' => 'password_retype',
											'value' => null,
                                            'atributos' => [],
                                            'requerido' => true
										],
										[
											'tipo' => 'hidden',
											'descripcion' => ' ',
											'name' => 'user_id',
											'value' => $usuario->id,
                                            'atributos' => [],
                                            'requerido' => true
										],
										[
											'tipo' => 'hidden',
											'descripcion' => ' ',
											'name' => 'ruta',
											'value' => Input::get('ruta'),
                                            'atributos' => [],
                                            'requerido' => true
										]
									]
					];
?>
@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	 
	@include('layouts.mensajes')

	@include('layouts.create',compact('form_create'))
@endsection