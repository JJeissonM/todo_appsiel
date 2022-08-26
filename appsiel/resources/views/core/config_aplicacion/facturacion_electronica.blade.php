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
								$proveedor_tecnologico_default = 'DATAICO';
								if( isset($parametros['proveedor_tecnologico_default'] ) )
								{
									$proveedor_tecnologico_default = $parametros['proveedor_tecnologico_default'];
								}
							?>
							{{ Form::bsSelect('proveedor_tecnologico_default', $proveedor_tecnologico_default, 'Proveedor tecnológico', ['DATAICO' => 'DATAICO', 'TFHKA' => 'The Fatory HKA'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$fe_ambiente = 'PRUEBAS';
								if( isset($parametros['fe_ambiente'] ) )
								{
									$fe_ambiente = $parametros['fe_ambiente'];
								}
							?>
							{{ Form::bsSelect('fe_ambiente', $fe_ambiente, 'Ambiente', ['PRUEBAS' => 'PRUEBAS', 'PRODUCCION' => 'PRODUCCION'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$enviar_email_clientes = 'false';
								if( isset($parametros['enviar_email_clientes'] ) )
								{
									$enviar_email_clientes = $parametros['enviar_email_clientes'];
								}
							?>
							{{ Form::bsSelect('enviar_email_clientes', $enviar_email_clientes, 'Enviar facturas por email al cliente', ['false' => 'No', 'true' => 'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$email_copia_factura = '';
								if( isset($parametros['email_copia_factura'] ) )
								{
									$email_copia_factura = $parametros['email_copia_factura'];
								}
							?>
							{{ Form::bsText('email_copia_factura', $email_copia_factura, 'Enviar copia de la factura del cliente a este E-mail', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>


				<div class="row">
					<div class="col-md-6">
						&nbsp;
					</div>
					<div class="col-md-6">
						&nbsp;
					</div>
				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$WSDL = '';
								if( isset($parametros['WSDL'] ) )
								{
									$WSDL = $parametros['WSDL'];
								}
							?>
							{{ Form::bsText('WSDL', $WSDL, 'URL Servicio Emisión facturas', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_notas_credito = '';
								if( isset($parametros['url_notas_credito'] ) )
								{
									$url_notas_credito = $parametros['url_notas_credito'];
								}
							?>
							{{ Form::bsText('url_notas_credito', $url_notas_credito, 'URL Servicio Emisión Notas crédito', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>


				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_notas_debito = '';
								if( isset($parametros['url_notas_debito'] ) )
								{
									$url_notas_debito = $parametros['url_notas_debito'];
								}
							?>
							{{ Form::bsText('url_notas_debito', $url_notas_debito, 'URL Servicio Emisión Notas débito', ['class'=>'form-control']) }}
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
								$documento_soporte_activo = '0';
								if( isset($parametros['documento_soporte_activo'] ) )
								{
									$documento_soporte_activo = $parametros['documento_soporte_activo'];
								}
							?>							
							{{ Form::bsSelect('documento_soporte_activo', $documento_soporte_activo, 'Activar emisión Doc. Soporte Compras', ['0' => 'No', '1' => 'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_documento_soporte = '';
								if( isset($parametros['url_documento_soporte'] ) )
								{
									$url_documento_soporte = $parametros['url_documento_soporte'];
								}
							?>
							{{ Form::bsText('url_documento_soporte', $url_documento_soporte, 'URL Servicio Emisión Doc. Soporte Compras', ['class'=>'form-control']) }}
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
							{{ Form::bsText('tokenEmpresa', $tokenEmpresa, 'Token Empresa (Account Id)', ['class'=>'form-control']) }}
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
							{{ Form::bsText('tokenPassword', $tokenPassword, 'Token Password (Auth Token)', ['class'=>'form-control']) }}
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

				<h4> Documento de Factura por defecto </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$transaction_type_id_default = 52;
								if( isset($parametros['transaction_type_id_default'] ) )
								{
									$transaction_type_id_default = $parametros['transaction_type_id_default'];
								}
							?>
							{{ Form::bsSelect('transaction_type_id_default', $transaction_type_id_default, 'Tipo de transacción', \App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$document_type_id_default = 47;

								if( isset($parametros['document_type_id_default'] ) )
								{
									$document_type_id_default = $parametros['document_type_id_default'];
								}
							?>
							{{ Form::bsSelect('document_type_id_default', $document_type_id_default, 'Tipo documento', \App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'combobox']) }}
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