<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	@if($url_crear!='')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate($url_crear) }}
	@endif

	@if($url_edit!='')
		{{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
	@endif

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev('consultorio_medico/profesionales/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext('consultorio_medico/profesionales/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
		@endif
	</div>

	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
			
			<h3 style="text-align: center;">   </h3>

			<table class="table table-bordered">
				<tr>
					<td colspan="4">
						<h4 align="center">Profesional de la Salud</h4>
					</td>
				</tr>
				<tr>
					<td rowspan="5" width="160px">
						{!! $lista_campos['imagen'] !!}
					</td>
				</tr>
				<tr>
					<td>
						<b>Nombres:</b> {{ $lista_campos['nombre1'] }} {{ $lista_campos['otros_nombres'] }}
					</td>
					<td>
						<b>Apellidos:</b> {{ $lista_campos['apellido1'] }} {{ $lista_campos['apellido2'] }}
					</td>
					<td>
						<b>Identificación:</b> {{ number_format($lista_campos['numero_identificacion'],0,',','.') }}
					</td>
				</tr>
				<tr>
					<td>
						<b>Dirección:</b> {{ $lista_campos['direccion1'] }}
					</td>
					<td>
						<b>Teléfono:</b> {{ $lista_campos['telefono1'] }}
					</td>
					<td>
						<b>Email:</b> {{ $lista_campos['email'] }}
					</td>
				</tr>
				<tr>
					<td>
						<b>Especialidad: </b>{{ $lista_campos['especialidad'] }}
					</td>
					<td>
						<b>Carnet/Licencia:</b> {{ $lista_campos['numero_carnet_licencia'] }}
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>
			<br/><br/>
		</div> <!-- Marco -->
	</div>
	<br/><br/>

@endsection