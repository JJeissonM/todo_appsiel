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

				<h4> Parámetros Generales  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$numero_territorial = '';
								if( isset($parametros['numero_territorial'] ) )
								{
									$numero_territorial = $parametros['numero_territorial'];
								}
							?>
							{{ Form::bsText('numero_territorial', $numero_territorial, 'Número territorial', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$resolucion_habilitacion = '';
								if( isset($parametros['resolucion_habilitacion'] ) )
								{
									$resolucion_habilitacion = $parametros['resolucion_habilitacion'];
								}
							?>
							{{ Form::bsText('resolucion_habilitacion', $resolucion_habilitacion, 'Resolución habilitación', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$anio_creacion_empresa = '';
								if( isset($parametros['anio_creacion_empresa'] ) )
								{
									$anio_creacion_empresa = $parametros['anio_creacion_empresa'];
								}
							?>
							{{ Form::bsText('anio_creacion_empresa', $anio_creacion_empresa, 'Año creación empresa (AA)', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$bloqueado_x_contratos = 4;
								if( isset($parametros['bloqueado_x_contratos'] ) )
								{
									$bloqueado_x_contratos = $parametros['bloqueado_x_contratos'];
								}
							?>
							{{ Form::bsText('bloqueado_x_contratos', $bloqueado_x_contratos, 'Cant. contratos mensuales para bloqueo', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$permitir_ingreso_contrato_en_mes_distinto_al_actual = 0;
								if( isset($parametros['permitir_ingreso_contrato_en_mes_distinto_al_actual'] ) )
								{
									$permitir_ingreso_contrato_en_mes_distinto_al_actual = $parametros['permitir_ingreso_contrato_en_mes_distinto_al_actual'];
								}
							?>
							{{ Form::bsSelect('permitir_ingreso_contrato_en_mes_distinto_al_actual', $permitir_ingreso_contrato_en_mes_distinto_al_actual, 'Permitir ingreso de contratos en un mes distinto al actual', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$color_emp_label = '';
								if( isset($parametros['color_emp_label'] ) )
								{
									$color_emp_label = $parametros['color_emp_label'];
								}
							?>
							{{ Form::bsText('color_emp_label', $color_emp_label, 'Color para la etiqueta empresa', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$slogan = '';
								if( isset($parametros['slogan'] ) )
								{
									$slogan = $parametros['slogan'];
								}
							?>
							{{ Form::bsText('slogan', $slogan, 'Slogan', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$color_slogan = '';
								if( isset($parametros['color_slogan'] ) )
								{
									$color_slogan = $parametros['color_slogan'];
								}
							?>
							{{ Form::bsText('color_slogan', $color_slogan, 'Color para la etiqueta Slogan', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$texto_en_representacion_de = 'PARA UN GRUPO ESPECIFICO DE USUARIOS DE TRANSPORTE DE PERSONAL (TRANSPORTE PARTICULAR)';
								if( isset($parametros['texto_en_representacion_de'] ) )
								{
									$texto_en_representacion_de = $parametros['texto_en_representacion_de'];
								}
							?>
                            {{ Form::bsTextArea('texto_en_representacion_de', $texto_en_representacion_de, 'Texto por defecto en el párrafo "En representacion de"', ['class'=>'form-control']) }}
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
								$logo_min_transporte = '';
								if( isset($parametros['logo_min_transporte'] ) )
								{
									$logo_min_transporte = $parametros['logo_min_transporte'];
								}
							?>
							{{ Form::bsSelect('logo_min_transporte', $logo_min_transporte, 'Logo Min. Transporte', ['super_transporte' => 'Súper Transporte','solo_transporte' => 'Solo Transporte'], ['class'=>'form-control']) }}
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
								$url_imagen_sello_empresa = '';
								if( isset($parametros['url_imagen_sello_empresa'] ) )
								{
									$url_imagen_sello_empresa = $parametros['url_imagen_sello_empresa'];
								}
							?>
							{{ Form::bsText('url_imagen_sello_empresa', $url_imagen_sello_empresa, 'URL imágen Sello empresa', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$url_imagen_firma_rep_legal = '';
								if( isset($parametros['url_imagen_firma_rep_legal'] ) )
								{
									$url_imagen_firma_rep_legal = $parametros['url_imagen_firma_rep_legal'];
								}
							?>
							{{ Form::bsText('url_imagen_firma_rep_legal', $url_imagen_firma_rep_legal, 'URL imágen firma Rep. Legal', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$url_imagen_sello_icontec = '';
								if( isset($parametros['url_imagen_sello_icontec'] ) )
								{
									$url_imagen_sello_icontec = $parametros['url_imagen_sello_icontec'];
								}
							?>
							{{ Form::bsText('url_imagen_sello_icontec', $url_imagen_sello_icontec, 'URL imágen Sello ICONTEC', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$url_imagen_firma_y_sello_empresa = '';
								if( isset($parametros['url_imagen_firma_y_sello_empresa'] ) )
								{
									$url_imagen_firma_y_sello_empresa = $parametros['url_imagen_firma_y_sello_empresa'];
								}
							?>
							{{ Form::bsText('url_imagen_firma_y_sello_empresa', $url_imagen_firma_y_sello_empresa, 'URL imágen Firma Y Sello empresa', ['class'=>'form-control']) }}
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