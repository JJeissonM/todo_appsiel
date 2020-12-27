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

				<h4> Parámetros por defecto creación de desarmes automáticos  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['core_tipo_transaccion_id'] ) )
								{
									$core_tipo_transaccion_id = $parametros['core_tipo_transaccion_id'];
								}else{
									$core_tipo_transaccion_id = 4;
								}
							?>
							{{ Form::bsSelect('core_tipo_transaccion_id', $core_tipo_transaccion_id, 'Tipo de transacción Default', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['core_tipo_doc_app_id'] ) )
								{
									$core_tipo_doc_app_id = $parametros['core_tipo_doc_app_id'];
								}else{
									$core_tipo_doc_app_id = 9;
								}
							?>
							{{ Form::bsSelect('core_tipo_doc_app_id', $core_tipo_doc_app_id, 'Tipo Doc. Default', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['core_tercero_id'] ) )
								{
									$core_tercero_id = $parametros['core_tercero_id'];
								}else{
									$core_tercero_id = 1;
								}
							?>
							{{ Form::bsSelect('core_tercero_id', $core_tercero_id, 'Tercero default', App\Core\Tercero::opciones_campo_select(), ['class'=>'combobox']) }}
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
								if( isset($parametros['motivo_salida_id'] ) )
								{
									$motivo_salida_id = $parametros['motivo_salida_id'];
								}else{
									$motivo_salida_id = 1;
								}
							?>
							{{ Form::bsSelect('motivo_salida_id', $motivo_salida_id, 'Motivo salida default', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['motivo_entrada_id'] ) )
								{
									$motivo_entrada_id = $parametros['motivo_entrada_id'];
								}else{
									$motivo_entrada_id = 1;
								}
							?>
							{{ Form::bsSelect('motivo_entrada_id', $motivo_entrada_id, 'Motivo entrada default', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
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