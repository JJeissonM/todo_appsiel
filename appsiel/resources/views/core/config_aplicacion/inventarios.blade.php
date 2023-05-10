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


				<h4> Parámetros por defecto creación de Productos  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$item_impuesto_id = 1;
								if( isset($parametros['item_impuesto_id'] ) )
								{
									$item_impuesto_id = $parametros['item_impuesto_id'];
								}
							?>
							{{ Form::bsSelect('item_impuesto_id', $item_impuesto_id, 'Impuesto', App\Contabilidad\Impuesto::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$item_lista_precios_id = 0;
								if( isset($parametros['item_lista_precios_id'] ) )
								{
									$item_lista_precios_id = $parametros['item_lista_precios_id'];
								}
							?>
							{{ Form::bsSelect('item_lista_precios_id', $item_lista_precios_id, 'Lista de precios de venta', App\Ventas\ListaPrecioEncabezado::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$item_bodega_principal_id = 0;
								if( isset($parametros['item_bodega_principal_id'] ) )
								{
									$item_bodega_principal_id = $parametros['item_bodega_principal_id'];
								}
							?>
							{{ Form::bsSelect('item_bodega_principal_id', $item_bodega_principal_id, 'Bodega principal', App\Inventarios\InvBodega::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$codigo_principal_manejo_productos = 0;
								if( isset($parametros['codigo_principal_manejo_productos'] ) )
								{
									$codigo_principal_manejo_productos = $parametros['codigo_principal_manejo_productos'];
								}
							?>
							{{ Form::bsSelect('codigo_principal_manejo_productos', $codigo_principal_manejo_productos, 'Código principal manejo productos', ['item_id' => 'ID', 'referencia' => 'Referencia'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_talla_en_descripcion_items = 0;
								if( isset($parametros['mostrar_talla_en_descripcion_items'] ) )
								{
									$mostrar_talla_en_descripcion_items = $parametros['mostrar_talla_en_descripcion_items'];
								}
							?>
							{{ Form::bsSelect('mostrar_talla_en_descripcion_items', $mostrar_talla_en_descripcion_items, 'Mostrar talla (unidad_medida2) en descripción ítems', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_referencia_en_descripcion_items = 0;
								if( isset($parametros['mostrar_referencia_en_descripcion_items'] ) )
								{
									$mostrar_referencia_en_descripcion_items = $parametros['mostrar_referencia_en_descripcion_items'];
								}
							?>
							{{ Form::bsSelect('mostrar_referencia_en_descripcion_items', $mostrar_referencia_en_descripcion_items, 'Mostrar referencia en descripción ítems', [ 'No', 'Si' ], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros para costeo de productos  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$maneja_costo_promedio_por_bodegas = '';
								if( isset($parametros['maneja_costo_promedio_por_bodegas'] ) )
								{
									$maneja_costo_promedio_por_bodegas = $parametros['maneja_costo_promedio_por_bodegas'];
								}
							?>
							{{ Form::bsSelect('maneja_costo_promedio_por_bodegas', $maneja_costo_promedio_por_bodegas, 'Maneja costo promedio por bodegas', [''=>'', 'No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						&nbsp;
					</div>

				</div>

				<h4> Parámetros por defecto creación de desarmes automáticos  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$core_tipo_transaccion_id = 4;
								if( isset($parametros['core_tipo_transaccion_id'] ) )
								{
									$core_tipo_transaccion_id = $parametros['core_tipo_transaccion_id'];
								}
							?>
							{{ Form::bsSelect('core_tipo_transaccion_id', $core_tipo_transaccion_id, 'Tipo de transacción Default', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$core_tipo_doc_app_id = 9;
								if( isset($parametros['core_tipo_doc_app_id'] ) )
								{
									$core_tipo_doc_app_id = $parametros['core_tipo_doc_app_id'];
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
								$core_tercero_id = 0;
								if( isset($parametros['core_tercero_id'] ) )
								{
									$core_tercero_id = $parametros['core_tercero_id'];
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
								$motivo_salida_id = 0;
								if( isset($parametros['motivo_salida_id'] ) )
								{
									$motivo_salida_id = $parametros['motivo_salida_id'];
								}
							?>
							{{ Form::bsSelect('motivo_salida_id', $motivo_salida_id, 'Motivo salida default', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_entrada_id = 0;
								if( isset($parametros['motivo_entrada_id'] ) )
								{
									$motivo_entrada_id = $parametros['motivo_entrada_id'];
								}
							?>
							{{ Form::bsSelect('motivo_entrada_id', $motivo_entrada_id, 'Motivo entrada default', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros por defecto creación Entradas de almacén  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
									$ea_tipo_transaccion_id = 4;
								if( isset($parametros['ea_tipo_transaccion_id'] ) )
								{
									$ea_tipo_transaccion_id = $parametros['ea_tipo_transaccion_id'];
								}
							?>
							{{ Form::bsSelect('ea_tipo_transaccion_id', $ea_tipo_transaccion_id, 'Tipo de transacción', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
									$ea_tipo_doc_app_id = 9;
								if( isset($parametros['ea_tipo_doc_app_id'] ) )
								{
									$ea_tipo_doc_app_id = $parametros['ea_tipo_doc_app_id'];
								}
							?>
							{{ Form::bsSelect('ea_tipo_doc_app_id', $ea_tipo_doc_app_id, 'Tipo Documento', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
									$ea_tercero_id = 1;
								if( isset($parametros['ea_tercero_id'] ) )
								{
									$ea_tercero_id = $parametros['ea_tercero_id'];
								}
							?>
							{{ Form::bsSelect('ea_tercero_id', $ea_tercero_id, 'Tercero', App\Core\Tercero::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
									$ea_motivo_id = 1;
								if( isset($parametros['ea_motivo_id'] ) )
								{
									$ea_motivo_id = $parametros['ea_motivo_id'];
								}
							?>
							{{ Form::bsSelect('ea_motivo_id', $ea_motivo_id, 'Motivo entrada', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>


				<h4> Parámetros por defecto para salidas de Órdenes de Trabajo  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$motivo_salida_id_ot = 5;
								if( isset($parametros['motivo_salida_id_ot'] ) )
								{
									$motivo_salida_id_ot = $parametros['motivo_salida_id_ot'];
								}
							?>
							{{ Form::bsSelect('motivo_salida_id_ot', $motivo_salida_id_ot, 'Motivo salida default en OT', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros para Códigos de Barra  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$longitud_item = 5;
								if( isset($parametros['longitud_item'] ) )
								{
									$longitud_item = $parametros['longitud_item'];
								}
							?>
							{{ Form::bsText('longitud_item', $longitud_item, 'Longitud Item ID', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$longitud_talla = 2;
								if( isset($parametros['longitud_talla'] ) )
								{
									$longitud_talla = $parametros['longitud_talla'];
								}
							?>
							{{ Form::bsText('longitud_talla', $longitud_talla, 'Longitud Talla', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$longitud_color = 0;
								if( isset($parametros['longitud_color'] ) )
								{
									$longitud_color = $parametros['longitud_color'];
								}
							?>
							{{ Form::bsText('longitud_color', $longitud_color, 'Longitud Color', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$caracter_relleno = 0;
								if( isset($parametros['caracter_relleno'] ) )
								{
									$caracter_relleno = $parametros['caracter_relleno'];
								}
							?>
							{{ Form::bsText('caracter_relleno', $caracter_relleno, 'Caracter de relleno', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$ancho_hoja_impresion = 295;
								if( isset($parametros['ancho_hoja_impresion'] ) )
								{
									$ancho_hoja_impresion = $parametros['ancho_hoja_impresion'];
								}
							?>
							{{ Form::bsText('ancho_hoja_impresion', $ancho_hoja_impresion, 'Ancho hoja de impresión (px)', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$alto_hoja_impresion = '';
								if( isset($parametros['alto_hoja_impresion'] ) )
								{
									$alto_hoja_impresion = $parametros['alto_hoja_impresion'];
								}
							?>
							{{ Form::bsText('alto_hoja_impresion', $alto_hoja_impresion, 'Alto hoja de impresión (px) (dejar vacío para cálculo automático)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				

				<h4> Parámetros para manejo de prendas  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejo_items_mandatarios_activo = '0';
								if( isset($parametros['manejo_items_mandatarios_activo'] ) )
								{
									$manejo_items_mandatarios_activo = $parametros['manejo_items_mandatarios_activo'];
								}
							?>
							{{ Form::bsSelect('manejo_items_mandatarios_activo', $manejo_items_mandatarios_activo, 'Manejo Ítems mandatarios Activo', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						&nbsp;
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