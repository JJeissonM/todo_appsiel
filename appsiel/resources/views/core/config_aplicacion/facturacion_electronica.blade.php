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

				<h4> Parámetros de conexión con el proveedor tecnológico  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['WSDL'] ) )
								{
									$WSDL = $parametros['WSDL'];
								}else{
									$WSDL = '';
								}
							?>
							{{ Form::bsText('WSDL', $WSDL, 'URL Servicio Emisión', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['WSANEXO'] ) )
								{
									$WSANEXO = $parametros['WSANEXO'];
								}else{
									$WSANEXO = '';
								}
							?>
							{{ Form::bsText('WSANEXO', $WSANEXO, 'URL Servicio Adjuntos', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['WSREPORTES'] ) )
								{
									$WSREPORTES = $parametros['WSREPORTES'];
								}else{
									$WSREPORTES = '';
								}
							?>
							{{ Form::bsText('WSREPORTES', $WSREPORTES, 'URL Servicio Reportes', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['tokenEmpresa'] ) )
								{
									$tokenEmpresa = $parametros['tokenEmpresa'];
								}else{
									$tokenEmpresa = '';
								}
							?>
							{{ Form::bsText('tokenEmpresa', $tokenEmpresa, 'Token Empresa', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['tokenPassword'] ) )
								{
									$tokenPassword = $parametros['tokenPassword'];
								}else{
									$tokenPassword = '';
								}
							?>
							{{ Form::bsText('tokenPassword', $tokenPassword, 'Token Password', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['modalidad_asignada'] ) )
								{
									$modalidad_asignada = $parametros['modalidad_asignada'];
								}else{
									$modalidad_asignada = '2';
								}
							?>
							{{ Form::bsSelect('modalidad_asignada', $modalidad_asignada, 'Modalidad asignada', ['1' => 'Automática', '2' => 'Manual Con Prefijo', '3' => 'Manual Sin Prefijo', '4' => 'Manual Contingencia'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br><br>

				<h4> Parámetros de configuración para los documentos electrónicos </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								if( isset($parametros['cantidadDecimales'] ) )
								{
									$cantidadDecimales = $parametros['cantidadDecimales'];
								}else{
									$cantidadDecimales = 4;
								}
							?>
							{{ Form::bsText('cantidadDecimales', $cantidadDecimales, 'Cantidad decimales en los valores', ['class'=>'form-control']) }}
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