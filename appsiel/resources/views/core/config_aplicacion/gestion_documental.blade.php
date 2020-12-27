@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    {!! $parametros['titulo'] !!}
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

		    	<!--
					// NOTA: La variable que no sea enviada en el request (a través de un input) será borrada del archivo de configuración
        			// Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo a este formulario
		    	-->

				{{ Form::hidden('titulo', $parametros['titulo'] ) }}

				<h4> Parámetros de encabezado informes y listados gestión Académica  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['banner_colegio_mostrar_slogan'] ) )
								{
									$banner_colegio_mostrar_slogan = $parametros['banner_colegio_mostrar_slogan'];
								}else{
									$banner_colegio_mostrar_slogan = 1;
								}
							?>
							{{ Form::bsSelect('banner_colegio_mostrar_slogan', $banner_colegio_mostrar_slogan, 'Mostrar Slogan', ['1'=>'Si','0'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['banner_colegio_mostrar_direccion'] ) )
								{
									$banner_colegio_mostrar_direccion = $parametros['banner_colegio_mostrar_direccion'];
								}else{
									$banner_colegio_mostrar_direccion = 1;
								}
							?>
							{{ Form::bsSelect('banner_colegio_mostrar_direccion', $banner_colegio_mostrar_direccion, 'Mostrar Dirección', ['1'=>'Si','0'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['banner_colegio_mostrar_telefono'] ) )
								{
									$banner_colegio_mostrar_telefono = $parametros['banner_colegio_mostrar_telefono'];
								}else{
									$banner_colegio_mostrar_telefono = 1;
								}
							?>
							{{ Form::bsSelect('banner_colegio_mostrar_telefono', $banner_colegio_mostrar_telefono, 'Mostrar Teléfono', ['1'=>'Si','0'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['banner_colegio_mostrar_ciudad'] ) )
								{
									$banner_colegio_mostrar_ciudad = $parametros['banner_colegio_mostrar_ciudad'];
								}else{
									$banner_colegio_mostrar_ciudad = 1;
								}
							?>
							{{ Form::bsSelect('banner_colegio_mostrar_ciudad', $banner_colegio_mostrar_ciudad, 'Mostrar Ciudad', ['1'=>'Si','0'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['banner_colegio_mostrar_nit'] ) )
								{
									$banner_colegio_mostrar_nit = $parametros['banner_colegio_mostrar_nit'];
								}else{
									$banner_colegio_mostrar_nit = 1;
								}
							?>
							{{ Form::bsSelect('banner_colegio_mostrar_nit', $banner_colegio_mostrar_nit, 'Mostrar NIT', ['1'=>'Si','0'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br><br>

				<div style="width: 100%; text-align: center;">
					<div class="row" style="margin: 5px;"> {{ Form::bsButtonsForm( url()->previous() ) }} </div>

					{{ Form::hidden('url_id',Input::get('id')) }}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				</div>

			{{ Form::close() }}
		</div>
	</div>
	<br/><br/>




	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});

		});
	</script>
	
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection