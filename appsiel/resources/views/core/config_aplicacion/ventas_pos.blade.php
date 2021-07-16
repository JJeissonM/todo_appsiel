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

				<h4> Parámetros por defecto creación de clientes  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$plantilla_factura_pos_default = 'plantilla_factura';
								if( isset($parametros['plantilla_factura_pos_default'] ) )
								{
									$plantilla_factura_pos_default = $parametros['plantilla_factura_pos_default'];
								}
							?>
							{{ Form::bsSelect('plantilla_factura_pos_default', $plantilla_factura_pos_default, 'Formato factura default', ['plantilla_factura' => 'Básico','plantilla_factura_2' => 'Visual','plantilla_factura_3' => 'Logo ancho'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tamanio_fuente_factura = '12';
								if( isset($parametros['tamanio_fuente_factura'] ) )
								{
									$tamanio_fuente_factura = $parametros['tamanio_fuente_factura'];
								}
							?>
							{{ Form::bsText('tamanio_fuente_factura', $tamanio_fuente_factura, 'Tamaño letra', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['redondear_centena'] ) )
								{
									$redondear_centena = $parametros['redondear_centena'];
								}else{
									$redondear_centena = 1;
								}
							?>
							{{ Form::bsSelect('redondear_centena', $redondear_centena, 'Redondear el precio total de la factura a la centena más cercana', [ '1' => 'Si', '0' => 'No' ], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['permite_facturacion_con_archivo_plano'] ) )
								{
									$permite_facturacion_con_archivo_plano = $parametros['permite_facturacion_con_archivo_plano'];
								}else{
									$permite_facturacion_con_archivo_plano = 0;
								}
							?>
							{{ Form::bsSelect('permite_facturacion_con_archivo_plano', $permite_facturacion_con_archivo_plano, 'Permite facturación con archivo plano', [ '0' => 'No', '1' => 'Si' ], ['class'=>'form-control']) }}
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