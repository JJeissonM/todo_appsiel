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
								if( isset($parametros['lista_precios_id'] ) )
								{
									$lista_precios_id = $parametros['lista_precios_id'];
								}else{
									$lista_precios_id = 1;
								}
							?>
							{{ Form::bsSelect('lista_precios_id', $lista_precios_id, 'Lista de precios', App\Ventas\ListaPrecioEncabezado::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['lista_descuentos_id'] ) )
								{
									$lista_descuentos_id = $parametros['lista_descuentos_id'];
								}else{
									$lista_descuentos_id = 24;
								}
							?>
							{{ Form::bsSelect('lista_descuentos_id', $lista_descuentos_id, 'Lista de descuentos', App\Ventas\ListaDctoEncabezado::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['clase_cliente_id'] ) )
								{
									$clase_cliente_id = $parametros['clase_cliente_id'];
								}else{
									$clase_cliente_id = 1;
								}
							?>
							{{ Form::bsSelect('clase_cliente_id', $clase_cliente_id, 'Clase de cliente', App\Ventas\ClaseCliente::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['vendedor_id'] ) )
								{
									$vendedor_id = $parametros['vendedor_id'];
								}else{
									$vendedor_id = 1;
								}
							?>
							{{ Form::bsSelect('vendedor_id', $vendedor_id, 'Vendedor', App\Ventas\Vendedor::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['inv_bodega_id'] ) )
								{
									$inv_bodega_id = $parametros['inv_bodega_id'];
								}else{
									$inv_bodega_id = 1;
								}
							?>
							{{ Form::bsSelect('inv_bodega_id', $inv_bodega_id, 'Bodega', App\Inventarios\InvBodega::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['zona_id'] ) )
								{
									$zona_id = $parametros['zona_id'];
								}else{
									$zona_id = 1;
								}
							?>
							{{ Form::bsSelect('zona_id', $zona_id, 'Zona', App\Ventas\Zona::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['condicion_pago_id'] ) )
								{
									$condicion_pago_id = $parametros['condicion_pago_id'];
								}else{
									$condicion_pago_id = 1;
								}
							?>
							{{ Form::bsSelect('condicion_pago_id', $condicion_pago_id, 'Condicion de pago', App\Ventas\CondicionPago::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros por defecto para las facturas  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$factura_ventas_modelo_id = 139;
								if( isset($parametros['factura_ventas_modelo_id'] ) )
								{
									$factura_ventas_modelo_id = $parametros['factura_ventas_modelo_id'];
								}
							?>
							{{ Form::bsSelect('factura_ventas_modelo_id', $factura_ventas_modelo_id, 'Modelo para facturas de ventas', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$factura_ventas_tipo_transaccion_id = 23;
								if( isset($parametros['factura_ventas_tipo_transaccion_id'] ) )
								{
									$factura_ventas_tipo_transaccion_id = $parametros['factura_ventas_tipo_transaccion_id'];
								}
							?>
							{{ Form::bsSelect('factura_ventas_tipo_transaccion_id', $factura_ventas_tipo_transaccion_id, 'Tipo de transacción para facturas', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$factura_ventas_tipo_doc_app_id = 18;
								if( isset($parametros['factura_ventas_tipo_doc_app_id'] ) )
								{
									$factura_ventas_tipo_doc_app_id = $parametros['factura_ventas_tipo_doc_app_id'];
								}
							?>
							{{ Form::bsSelect('factura_ventas_tipo_doc_app_id', $factura_ventas_tipo_doc_app_id, 'Documento para facturas', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$longitud_consecutivo_factura = 0;
								if( isset($parametros['longitud_consecutivo_factura'] ) )
								{
									$longitud_consecutivo_factura = $parametros['longitud_consecutivo_factura'];
								}
							?>
							{{ Form::bsText('longitud_consecutivo_factura', $longitud_consecutivo_factura, 'Longitud consecutivo factura (el consecutivo será completado con ceros a la izquierda)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros por defecto para las remisiones  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['rm_modelo_id'] ) )
								{
									$rm_modelo_id = $parametros['rm_modelo_id'];
								}else{
									$rm_modelo_id = 164;
								}
							?>
							{{ Form::bsSelect('rm_modelo_id', $rm_modelo_id, 'Modelo para remisiones', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['rm_tipo_transaccion_id'] ) )
								{
									$rm_tipo_transaccion_id = $parametros['rm_tipo_transaccion_id'];
								}else{
									$rm_tipo_transaccion_id = 24;
								}
							?>
							{{ Form::bsSelect('rm_tipo_transaccion_id', $rm_tipo_transaccion_id, 'Tipo de transacción para remisiones', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['rm_tipo_doc_app_id'] ) )
								{
									$rm_tipo_doc_app_id = $parametros['rm_tipo_doc_app_id'];
								}else{
									$rm_tipo_doc_app_id = 7;
								}
							?>
							{{ Form::bsSelect('rm_tipo_doc_app_id', $rm_tipo_doc_app_id, 'Documento para remisiones', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros por defecto para las Devoluciones  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['dvc_modelo_id'] ) )
								{
									$dvc_modelo_id = $parametros['dvc_modelo_id'];
								}else{
									$dvc_modelo_id = 174;
								}
							?>
							{{ Form::bsSelect('dvc_modelo_id', $dvc_modelo_id, 'Modelo para devoluciones', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['dvc_tipo_transaccion_id'] ) )
								{
									$dvc_tipo_transaccion_id = $parametros['dvc_tipo_transaccion_id'];
								}else{
									$dvc_tipo_transaccion_id = 34;
								}
							?>
							{{ Form::bsSelect('dvc_tipo_transaccion_id', $dvc_tipo_transaccion_id, 'Tipo de transacción para devoluciones', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['dvc_tipo_doc_app_id'] ) )
								{
									$dvc_tipo_doc_app_id = $parametros['dvc_tipo_doc_app_id'];
								}else{
									$dvc_tipo_doc_app_id = 35;
								}
							?>
							{{ Form::bsSelect('dvc_tipo_doc_app_id', $dvc_tipo_doc_app_id, 'Documento para devoluciones', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>


				<h4> Parámetros por defecto para Pedidos  </h4>
				<hr>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pv_modelo_id'] ) )
								{
									$pv_modelo_id = $parametros['pv_modelo_id'];
								}else{
									$pv_modelo_id = 175;
								}
							?>
							{{ Form::bsSelect('pv_modelo_id', $pv_modelo_id, 'Modelo para devoluciones', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pv_tipo_transaccion_id'] ) )
								{
									$pv_tipo_transaccion_id = $parametros['pv_tipo_transaccion_id'];
								}else{
									$pv_tipo_transaccion_id = 42;
								}
							?>
							{{ Form::bsSelect('pv_tipo_transaccion_id', $pv_tipo_transaccion_id, 'Tipo de transacción para pedidos', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pv_tipo_doc_app_id'] ) )
								{
									$pv_tipo_doc_app_id = $parametros['pv_tipo_doc_app_id'];
								}else{
									$pv_tipo_doc_app_id = 41;
								}
							?>
							{{ Form::bsSelect('pv_tipo_doc_app_id', $pv_tipo_doc_app_id, 'Documento para pedidos', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>


				<br>
				<h4> Parámetros de precios  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['modo_liquidacion_precio'] ) )
								{
									$modo_liquidacion_precio = $parametros['modo_liquidacion_precio'];
								}else{
									$modo_liquidacion_precio = 'ultimo_precio';
								}
							?>
							{{ Form::bsSelect('modo_liquidacion_precio', $modo_liquidacion_precio, 'Modo de liquidación del precio de ventas', ['precio_estandar_venta'=>'Precio estándar de venta','ultimo_precio'=>'Último precio vendido','lista_de_precios'=>'Lista de precios del cliente'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>



				<br>
				<h4> Validaciones de ventas  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['permitir_venta_menor_costo'] ) )
								{
									$permitir_venta_menor_costo = $parametros['permitir_venta_menor_costo'];
								}else{
									$permitir_venta_menor_costo = 0;
								}
							?>
							{{ Form::bsSelect('permitir_venta_menor_costo', $permitir_venta_menor_costo, 'Permitir ventas menor que el costo', [ '0' => 'No', '1' => 'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['permitir_inventarios_negativos'] ) )
								{
									$permitir_inventarios_negativos = $parametros['permitir_inventarios_negativos'];
								}else{
									$permitir_inventarios_negativos = 0;
								}
							?>
							{{ Form::bsSelect('permitir_inventarios_negativos', $permitir_inventarios_negativos, 'Permitir inventarios negativos', [ '0' => 'No', '1' => 'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>



				<br>
				<h4> Etiquetas para formatos de impresión  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$encabezado_linea_1 = 'GRACIAS POR SU COMPRA.';
								if( isset($parametros['encabezado_linea_1'] ) )
								{
									$encabezado_linea_1 = $parametros['encabezado_linea_1'];
								}
							?>
							{{ Form::bsText('encabezado_linea_1', $encabezado_linea_1, 'Encabezado línea 1', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['encabezado_linea_2'] ) )
								{
									$encabezado_linea_2 = $parametros['encabezado_linea_2'];
								}else{
									$encabezado_linea_2 = '';
								}
							?>
							{{ Form::bsText('encabezado_linea_2', $encabezado_linea_2, 'Encabezado línea 2', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['encabezado_linea_3'] ) )
								{
									$encabezado_linea_3 = $parametros['encabezado_linea_3'];
								}else{
									$encabezado_linea_3 = '';
								}
							?>
							{{ Form::bsText('encabezado_linea_3', $encabezado_linea_3, 'Encabezado línea 3', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pie_pagina_linea_1'] ) )
								{
									$pie_pagina_linea_1 = $parametros['pie_pagina_linea_1'];
								}else{
									$pie_pagina_linea_1 = '';
								}
							?>
							{{ Form::bsText('pie_pagina_linea_1', $pie_pagina_linea_1, 'Pie de página línea 1', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pie_pagina_linea_2'] ) )
								{
									$pie_pagina_linea_2 = $parametros['pie_pagina_linea_2'];
								}else{
									$pie_pagina_linea_2 = 0;
								}
							?>
							{{ Form::bsText('pie_pagina_linea_2', $pie_pagina_linea_2, 'Pie de página línea 2', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pie_pagina_linea_3'] ) )
								{
									$pie_pagina_linea_3 = $parametros['pie_pagina_linea_3'];
								}else{
									$pie_pagina_linea_3 = '';
								}
							?>
							{{ Form::bsText('pie_pagina_linea_3', $pie_pagina_linea_3, 'Pie de página línea 3', ['class'=>'form-control']) }}
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