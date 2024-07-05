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
								$redondear_centena = '';
								if( isset($parametros['redondear_centena'] ) )
								{
									$redondear_centena = $parametros['redondear_centena'];
								}
							?>
							{{ Form::bsSelect('redondear_centena', $redondear_centena, 'Redondear el precio total de la factura a la centena más cercana', [ '1' => 'Si', '0' => 'No' ], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$permite_facturacion_con_archivo_plano = 0;
								if( isset($parametros['permite_facturacion_con_archivo_plano'] ) )
								{
									$permite_facturacion_con_archivo_plano = $parametros['permite_facturacion_con_archivo_plano'];
								}
							?>
							{{ Form::bsSelect('permite_facturacion_con_archivo_plano', $permite_facturacion_con_archivo_plano, 'Permite facturación con archivo plano', [ '0' => 'No', '1' => 'Si' ], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$activar_ingreso_tactil_productos = '';
								if( isset($parametros['activar_ingreso_tactil_productos'] ) )
								{
									$activar_ingreso_tactil_productos = $parametros['activar_ingreso_tactil_productos'];
								}else{
								}
							?>
							{{ Form::bsSelect('activar_ingreso_tactil_productos', $activar_ingreso_tactil_productos, 'Activar ingreso Tactil de productos al crear factura', [ ''=>'', '1' => 'Si', '0' => 'No' ], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php 
									$asignar_fecha_apertura_a_facturas = '';
									if( isset($parametros['asignar_fecha_apertura_a_facturas'] ) )
									{
										$asignar_fecha_apertura_a_facturas = $parametros['asignar_fecha_apertura_a_facturas'];
									}
								?>
								{{ Form::bsSelect('asignar_fecha_apertura_a_facturas', $asignar_fecha_apertura_a_facturas, 'Asignar la fecha de apertura en la creación de facturas', [ ''=>'', '1' => 'Si', '0' => 'No' ], ['class'=>'form-control', 'required'=>'required']) }}
							</div>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_resumen_ventas_pos_en_arqueo = '1';
								if( isset($parametros['mostrar_resumen_ventas_pos_en_arqueo'] ) )
								{
									$mostrar_resumen_ventas_pos_en_arqueo = $parametros['mostrar_resumen_ventas_pos_en_arqueo'];
								}
							?>
							{{ Form::bsSelect('mostrar_resumen_ventas_pos_en_arqueo', $mostrar_resumen_ventas_pos_en_arqueo, 'Mostra resúmen de Ventas en Arqueo', ['No','Sí'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php 
									$mostrar_solo_items_con_precios_en_lista_cliente_default = '0';
									if( isset($parametros['mostrar_solo_items_con_precios_en_lista_cliente_default'] ) )
									{
										$mostrar_solo_items_con_precios_en_lista_cliente_default = $parametros['mostrar_solo_items_con_precios_en_lista_cliente_default'];
									}
								?>
								{{ Form::bsSelect('mostrar_solo_items_con_precios_en_lista_cliente_default', $mostrar_solo_items_con_precios_en_lista_cliente_default, 'Mostra solo ítems que tienen precios en la lista del cliente por defecto del PDV', ['No','Sí'], ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$sumar_efectivo_base_en_saldo_esperado = '1';
								if( isset($parametros['sumar_efectivo_base_en_saldo_esperado'] ) )
								{
									$sumar_efectivo_base_en_saldo_esperado = $parametros['sumar_efectivo_base_en_saldo_esperado'];
								}
							?>
							{{ Form::bsSelect('sumar_efectivo_base_en_saldo_esperado', $sumar_efectivo_base_en_saldo_esperado, 'Sumar Saldo inicial (Base) en el Saldo Esperado', ['No','Sí'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros de Impresión  </h4>
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
							{{ Form::bsSelect('plantilla_factura_pos_default', $plantilla_factura_pos_default, 'Formato factura default', [ ''=>'', 'plantilla_factura' => 'Básico','plantilla_factura_2' => 'Visual','plantilla_factura_3' => 'Logo ancho','plantilla_factura_remision_cocina' => 'Factura + RM cocina'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tamanio_fuente_factura = '17';
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
								$usar_complemento_JSPrintManager = '0';
								if( isset($parametros['usar_complemento_JSPrintManager'] ) )
								{
									$usar_complemento_JSPrintManager = $parametros['usar_complemento_JSPrintManager'];
								}
							?>
							{{ Form::bsSelect('usar_complemento_JSPrintManager', $usar_complemento_JSPrintManager, 'Mostrar aviso para imprimir pedidos de meseros', ['No','Si'], ['class'=>'form-control']) }}
							
							@if(config('ventas_pos.usar_complemento_JSPrintManager') == 1)
								<br>
								{{ Form::bsSelect('lista_impresoras_equipo_local', null, 'Lista impresoras equipo local (Usar estos nombres para la config. de abajo)', [], ['class'=>'form-control']) }}
							@endif
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
								$impresora_cocina_por_defecto = '';
								if( isset($parametros['impresora_cocina_por_defecto'] ) )
								{
									$impresora_cocina_por_defecto = $parametros['impresora_cocina_por_defecto'];
								}
							?>
							{{ Form::bsText('impresora_cocina_por_defecto', $impresora_cocina_por_defecto, 'Nombre Impresora Cocina', ['class'=>'form-control']) }}
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
								$tamanio_letra_impresion_items_cocina = '0.5';
								if( isset($parametros['tamanio_letra_impresion_items_cocina'] ) )
								{
									$tamanio_letra_impresion_items_cocina = $parametros['tamanio_letra_impresion_items_cocina'];
								}
							?>
							{{ Form::bsText('tamanio_letra_impresion_items_cocina', $tamanio_letra_impresion_items_cocina, 'Tamaño letra para los ítems en la factura de cocina', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$imprimir_pedidos_en_cocina = '0';
								if( isset($parametros['imprimir_pedidos_en_cocina'] ) )
								{
									$imprimir_pedidos_en_cocina = $parametros['imprimir_pedidos_en_cocina'];
								}
							?>
							{{ Form::bsSelect('imprimir_pedidos_en_cocina', $imprimir_pedidos_en_cocina, 'Imprimir PEDIDO directamente en la cocina (Vista show Vtas.)', ['No','Si'], ['class'=>'form-control']) }}
							
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$ancho_formato_impresion = '3.15';
								if( isset($parametros['ancho_formato_impresion'] ) )
								{
									$ancho_formato_impresion = $parametros['ancho_formato_impresion'];
								}
							?>
							{{ Form::bsText('ancho_formato_impresion', $ancho_formato_impresion, 'Anchura Formato impresión (Pulgadas)', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cerrar_modal_al_seleccionar_producto = '';
								if( isset($parametros['cerrar_modal_al_seleccionar_producto'] ) )
								{
									$cerrar_modal_al_seleccionar_producto = $parametros['cerrar_modal_al_seleccionar_producto'];
								}
							?>
							{{ Form::bsSelect('cerrar_modal_al_seleccionar_producto', $cerrar_modal_al_seleccionar_producto, 'Cerrar modal al seleccionar producto', [''=>'',  '1' => 'Si', '0' => 'No' ], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros de la acumulación  </h4>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$validar_existencias_al_acumular = '1';
								if( isset($parametros['validar_existencias_al_acumular'] ) )
								{
									$validar_existencias_al_acumular = $parametros['validar_existencias_al_acumular'];
								}
							?>
							{{ Form::bsSelect('validar_existencias_al_acumular', $validar_existencias_al_acumular, 'Validar existencias en la acumulación', ['No','Sí'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$crear_ensamble_de_recetas = '';
								if( isset($parametros['crear_ensamble_de_recetas'] ) )
								{
									$crear_ensamble_de_recetas = $parametros['crear_ensamble_de_recetas'];
								}
							?>
							{{ Form::bsSelect('crear_ensamble_de_recetas', $crear_ensamble_de_recetas, 'Crear ensamble de recetas en la acumulación', ['No','Sí'], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros de Pedidos  </h4>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$agrupar_pedidos_por_cliente = '';
								if( isset($parametros['agrupar_pedidos_por_cliente'] ) )
								{
									$agrupar_pedidos_por_cliente = $parametros['agrupar_pedidos_por_cliente'];
								}
							?>
							{{ Form::bsSelect('agrupar_pedidos_por_cliente', $agrupar_pedidos_por_cliente, 'Agrupar todos los pedidos del mismo cliente para facturar', [ ''=>'', 'No','Sí'], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros manejo de propinas </h4>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_propinas = '0';
								if( isset($parametros['manejar_propinas'] ) )
								{
									$manejar_propinas = $parametros['manejar_propinas'];
								}
							?>
							{{ Form::bsSelect('manejar_propinas', $manejar_propinas, 'Manejar propinas', [ '0'=> 'No', '1'=> 'Sí'], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php 
									$porcentaje_propina = '5';
									if( isset($parametros['porcentaje_propina'] ) )
									{
										$porcentaje_propina = $parametros['porcentaje_propina'];
									}
								?>
								{{ Form::bsText('porcentaje_propina', $porcentaje_propina, 'Porcentaje propina', ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_tesoreria_propinas = '0';
								if( isset($parametros['motivo_tesoreria_propinas'] ) )
								{
									$motivo_tesoreria_propinas = $parametros['motivo_tesoreria_propinas'];
								}
							?>
							{{ Form::bsSelect('motivo_tesoreria_propinas', $motivo_tesoreria_propinas, 'Motivo Tesorería para Propinas', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros manejo de Datafono </h4>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_datafono = '0';
								if( isset($parametros['manejar_datafono'] ) )
								{
									$manejar_datafono = $parametros['manejar_datafono'];
								}
							?>
							{{ Form::bsSelect('manejar_datafono', $manejar_datafono, 'Manejar Datafono', [ '0'=> 'No', '1'=> 'Sí'], ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php 
									$porcentaje_datafono = '5';
									if( isset($parametros['porcentaje_datafono'] ) )
									{
										$porcentaje_datafono = $parametros['porcentaje_datafono'];
									}
								?>
								{{ Form::bsText('porcentaje_datafono', $porcentaje_datafono, 'Porcentaje datafono', ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_tesoreria_datafono = '0';
								if( isset($parametros['motivo_tesoreria_datafono'] ) )
								{
									$motivo_tesoreria_datafono = $parametros['motivo_tesoreria_datafono'];
								}
							?>
							{{ Form::bsSelect('motivo_tesoreria_datafono', $motivo_tesoreria_datafono, 'Motivo Tesorería para ingresos Datafono', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros Facturación Electrónica </h4>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$modulo_fe_activo = '0';
								if( isset($parametros['modulo_fe_activo'] ) )
								{
									$modulo_fe_activo = $parametros['modulo_fe_activo'];
								}
							?>
							{{ Form::bsSelect('modulo_fe_activo', $modulo_fe_activo, 'Módulo de Fact. Electrónica Activo',['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$ocultar_boton_guardar_factura_pos = '0';
								if( isset($parametros['ocultar_boton_guardar_factura_pos'] ) )
								{
									$ocultar_boton_guardar_factura_pos = $parametros['ocultar_boton_guardar_factura_pos'];
								}
							?>
							{{ Form::bsSelect('ocultar_boton_guardar_factura_pos', $ocultar_boton_guardar_factura_pos, 'Ocultar botón Guardar Factura POS',['No','Si'], ['class'=>'form-control']) }}
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
	
	@if(config('ventas_pos.usar_complemento_JSPrintManager') == 1)
		<script src="{{ asset( 'assets/js/ventas_pos/JSPrintManager.js' )}}"></script>
		<script src="{{ asset( 'assets/js/ventas_pos/script_to_printer.js?aux=' . uniqid() )}}"></script>
	@endif

	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection