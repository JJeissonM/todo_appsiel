@extends('layouts.principal')

<?php
	$form_create = [
						'titulo' => 'Cambio de contraseña',
						'subtitulo' => 'Cambio de contraseña',
						'url' => '/core/usuario/cambiarpasswd',
						'campos' => [
										[
											'tipo' => 'password',
											'descripcion' => 'Nueva Contraseña',
											'name' => 'password_new',
											'value' => null,
                                            'atributos' => ['required'=>'required'],
                                            'requerido' => true
										],
										[
											'tipo' => 'password',
											'descripcion' => 'Repertir nueva Contraseña',
											'name' => 'password_retype',
											'value' => null,
                                            'atributos' => ['required'=>'required'],
                                            'requerido' => true
										],
										[
											'tipo' => 'hidden',
											'descripcion' => ' ',
											'name' => 'user_id',
											'value' => $registro->id,
                                            'atributos' => ['required'=>'required'],
                                            'requerido' => true
										]
									]
					];
?>
@section('content')
	@include('layouts.create',compact('miga_pan','form_create'))
@endsection
