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
								$plantilla_factura_pos_default = 'plantilla_factura';
								if( isset($parametros['plantilla_factura_pos_default'] ) )
								{
									$plantilla_factura_pos_default = $parametros['plantilla_factura_pos_default'];
								}
							?>
							{{ Form::bsSelect('plantilla_factura_pos_default', $plantilla_factura_pos_default, 'Formato factura default', ['plantilla_factura' => 'Básico','plantilla_factura_2' => 'Visual','plantilla_factura_3' => 'Logo ancho','plantilla_factura_remision_cocina' => 'Factura + RM cocina'], ['class'=>'form-control']) }}
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
								$cerrar_modal_al_seleccionar_producto = '1';
								if( isset($parametros['cerrar_modal_al_seleccionar_producto'] ) )
								{
									$cerrar_modal_al_seleccionar_producto = $parametros['cerrar_modal_al_seleccionar_producto'];
								}
							?>
							{{ Form::bsSelect('cerrar_modal_al_seleccionar_producto', $cerrar_modal_al_seleccionar_producto, 'Cerrar modal al seleccionar producto', [ '1' => 'Si', '0' => 'No' ], ['class'=>'form-control']) }}
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

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['activar_ingreso_tactil_productos'] ) )
								{
									$activar_ingreso_tactil_productos = $parametros['activar_ingreso_tactil_productos'];
								}else{
									$activar_ingreso_tactil_productos = 1;
								}
							?>
							{{ Form::bsSelect('activar_ingreso_tactil_productos', $activar_ingreso_tactil_productos, 'Activar ingreso Tactil de productos al crear factura', [ '1' => 'Si', '0' => 'No' ], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php 
									if( isset($parametros['asignar_fecha_apertura_a_facturas'] ) )
									{
										$asignar_fecha_apertura_a_facturas = $parametros['asignar_fecha_apertura_a_facturas'];
									}else{
										$asignar_fecha_apertura_a_facturas = 1;
									}
								?>
								{{ Form::bsSelect('asignar_fecha_apertura_a_facturas', $asignar_fecha_apertura_a_facturas, 'Asignar la fecha de apertura en la creación de facturas', [ '1' => 'Si', '0' => 'No' ], ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_resumen_ventas_pos_en_arqueo = '0';
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

				<h4> Parámetros de la acumulación  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$validar_existencias_al_acumular = '0';
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
								$crear_ensamble_de_recetas = '1';
								if( isset($parametros['crear_ensamble_de_recetas'] ) )
								{
									$crear_ensamble_de_recetas = $parametros['crear_ensamble_de_recetas'];
								}
							?>
							{{ Form::bsSelect('crear_ensamble_de_recetas', $crear_ensamble_de_recetas, 'Crear ensamble de recetas en la acumulación', ['No','Sí'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros de Pedidos  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$agrupar_pedidos_por_cliente = '0';
								if( isset($parametros['agrupar_pedidos_por_cliente'] ) )
								{
									$agrupar_pedidos_por_cliente = $parametros['agrupar_pedidos_por_cliente'];
								}
							?>
							{{ Form::bsSelect('agrupar_pedidos_por_cliente', $agrupar_pedidos_por_cliente, 'Agrupar todos los pedidos del mismo cliente para facturar', ['No','Sí'], ['class'=>'form-control']) }}
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