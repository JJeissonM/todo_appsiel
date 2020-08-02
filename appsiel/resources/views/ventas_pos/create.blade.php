@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
	<style>
		#suggestions {
		    position: absolute;
		    z-index: 9999;
		}
		#clientes_suggestions {
		    position: absolute;
		    z-index: 9999;
		    bottom: 20px;
		}

		#existencia_actual, #tasa_impuesto, #tasa_descuento{
			width: 40px;
		}

		#popup_alerta{
			display: none;/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right:10px; /*A la izquierda deje un espacio de 0px*/
			bottom:10px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index:999999;
			float: right;
    		text-align: center;
    		padding: 5px;
    		opacity: 0.7;
		}

		.elemento_fondo{
		    position: fixed;
		    z-index: 9999;
			bottom: 0;
		    margin-bottom: 0;
		    float: left;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">

		<div class="row">
			<div class="col-md-4 col-xs-12">
				<div class="btn-group">
					<button class="btn btn-primary btn-xs btn_consultar_estado_pdv" data-pdv_id="{{Input::get('pdv_id')}}" data-lbl_ventana="Estado de PDV"> <i class="fa fa-btn fa-search"></i> Estado PDV </button>
					<button class="btn btn-default btn-xs btn_consultar_documentos" data-pdv_id="{{Input::get('pdv_id')}}" data-lbl_ventana="Facturas de ventas"> <i class="fa fa-btn fa-search"></i> Consultar facturas </button>
				</div>
			</div>


			<div class="col-md-4 col-xs-12 text-center">
				<div class="btn-group">
					<button class="btn btn-info btn-xs btn_registrar_ingresos_gastos" data-id_modelo="46" data-id_transaccion="8" data-lbl_ventana="Ingresos"><i class="fa fa-btn fa-money"></i> <i class="fa fa-btn fa-arrow-up"></i> Registrar Ingresos </button>
					<button class="btn btn-warning btn-xs btn_registrar_ingresos_gastos" data-id_modelo="54" data-id_transaccion="17" data-lbl_ventana="Gastos"><i class="fa fa-btn fa-money"></i> <i class="fa fa-btn fa-arrow-down"></i> Registrar Salidas </button>
				</div>
			</div>


			<div class="col-md-4 col-xs-12">
				<div class="btn-group">
					&nbsp;
				</div>
			</div>
		</div>

		<br>
			

		<div class="marco_formulario">		    

			<h4>Nuevo registro</h4>
			<hr>
			{{ Form::open([ 'url' => $form_create['url'], 'id'=>'form_create']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

				<input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{Input::get('id_transaccion')}}" required="required">

				{{ Form::hidden( 'pdv_id', Input::get('pdv_id'), ['id'=>'pdv_id'] ) }}
				{{ Form::hidden('cajero_id', Auth::user()->id, ['id'=>'cajero_id'] ) }}

				{{ Form::hidden('inv_bodega_id_aux',$pdv->bodega_default_id,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="cliente_id" id="cliente_id" value="{{$pdv->cliente_default_id}}" required="required">
				<input type="hidden" name="zona_id" id="zona_id" value="{{$pdv->cliente->zona_id}}" required="required">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="{{$pdv->cliente->clase_cliente_id}}" required="required">

				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$pdv->cliente->core_tercero_id}}" required="required">

				<input type="hidden" name="cliente_descripcion" id="cliente_descripcion" value="{{$pdv->cliente->tercero->descripcion}}" required="required">
				{{ Form::bsText( 'numero_identificacion', $pdv->cliente->tercero->numero_identificacion, 'NIT/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
				{{ Form::bsText( 'direccion1', $pdv->cliente->tercero->direccion1, 'Dirección de entrega', ['id'=>'direccion1', 'required'=>'required', 'class'=>'form-control'] ) }}
				{{ Form::bsText( 'telefono1', $pdv->cliente->tercero->telefono1, 'Teléfono', ['id'=>'telefono1', 'required'=>'required', 'class'=>'form-control'] ) }}

				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="{{$pdv->cliente->lista_precios_id}}" required="required">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="{{$pdv->cliente->lista_descuentos_id}}" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="{{$pdv->cliente->liquida_impuestos}}" required="required">

				<input type="hidden" name="inv_motivo_id" id="inv_motivo_id" value="{{$inv_motivo_id}}">

				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

				<input type="hidden" name="estado" id="estado" value="Pendiente">

				<input type="hidden" name="tipo_transaccion"  id="tipo_transaccion" value="factura_directa">

				<input type="hidden" name="rm_tipo_transaccion_id"  id="rm_tipo_transaccion_id" value="{{config('ventas')['rm_tipo_transaccion_id']}}">
				<input type="hidden" name="dvc_tipo_transaccion_id"  id="dvc_tipo_transaccion_id" value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

				<input type="hidden" name="caja_id" id="saldo_original" value="0">

				<input type="hidden" name="valor_total_cambio" id="valor_total_cambio" value="0">

				<div id="popup_alerta"> </div>
				
			{{ Form::close() }}

			<div class="container-fluid">
				<div class="row">					
					<div class="col-md-8">
						{!! $tabla->dibujar() !!}
						Productos ingresados: <span id="numero_lineas"> 0 </span>
					</div>

					<div class="col-md-4 well" style="font-size: 1.2em;">
						<h3 style="width: 100%; text-align: center;">Totales</h3>
						<hr>
							<div id="total_cantidad" style="display: none;"> 0 </div>
							
							<div class="alert alert-info">
								<table style="width: 100%; margin: 0px;">
									<tr>
										<td width="35%" style="border: 0px;">
											<strong> Subtotal </strong>
										</td>
										<td style="text-align: right; border: 0px; background-color: transparent !important;">
											<div id="subtotal" style="display: inline;"> $ 0 </div>
										</td>
									</tr>
								</table>								
							</div>
							
							<div class="alert alert-info">
								<table style="width: 100%; margin: 0px;">
									<tr>
										<td width="35%" style="border: 0px;">
											<strong> Descuento </strong>
										</td>
										<td style="text-align: right; border: 0px; background-color: transparent !important;">
											<div id="descuento" style="display: inline;"> $ 0 </div>
										</td>
									</tr>
								</table>								
							</div>
							
							<div class="alert alert-info">
								<table style="width: 100%; margin: 0px;">
									<tr>
										<td width="35%" style="border: 0px;">
											<strong> Impuestos </strong>
										</td>
										<td style="text-align: right; border: 0px; background-color: transparent !important;">
											<div id="total_impuestos" style="display: inline;"> $ 0 </div>
										</td>
									</tr>
								</table>								
							</div>
							
							<div class="alert alert-info">
								<table style="width: 100%; margin: 0px;">
									<tr>
										<td width="35%" style="border: 0px;">
											<strong> Total factura </strong>
										</td>
										<td style="text-align: right; border: 0px; background-color: transparent !important;">
											<div id="total_factura" style="display: inline;"> $ 0 </div>
											<input type="hidden" name="valor_total_factura" id="valor_total_factura" value="0">
											<br>
											<div id="lbl_ajuste_al_peso" style="display: inline; font-size: 9px;"> $ 0 </div>
										</td>
									</tr>
								</table>								
							</div>
							
							<div class="alert alert-warning">
								<table style="width: 100%; margin: 0px;">
									<tr>
										<td width="35%" style="border: 0px;">
											<strong> Efectivo Recibido </strong>
										</td>
										<td style="text-align: right; border: 0px; background-color: transparent !important;">
											<input type="text" name="efectivo_recibido" id="efectivo_recibido" class="form-control">
											<div id="lbl_efectivo_recibido" style="display: inline;"> $ 0 </div>
										</td>
									</tr>
								</table>								
							</div>
							
							<div class="alert alert-default" id="div_total_cambio">
								<table style="width: 100%; margin: 0px;">
									<tr>
										<td width="35%" style="border: 0px;">
											<strong> Total cambio </strong>
										</td>
										<td style="text-align: right; border: 0px; background-color: transparent !important;">
											<div id="total_cambio" style="display: inline;"> $ 0 </div>
										</td>
									</tr>
								</table>								
							</div>

							<div style="width: 100%; text-align: center;">
								<button class="btn btn-lg btn-primary" id="btn_guardar_factura" disabled="disabled"> <i class="fa fa-check"></i> Guardar factura </button>
							</div>
							
					</div>
				</div>
			</div>			
			
			<br>
		</div>
	</div>
	<br/>

	<table style="display: none;">
		<tr id="linea_ingreso_default_aux">
			<td style="display: none;">
				<div class="inv_producto_id"></div>
			</td>
			<td style="display: none;">
				<div class="precio_unitario"></div>
			</td>
			<td style="display: none;">
				<div class="base_impuesto"></div>
			</td>
			<td style="display: none;">
				<div class="tasa_impuesto"></div>
			</td>
			<td style="display: none;">
				<div class="valor_impuesto"></div>
			</td>
			<td style="display: none;">
				<div class="base_impuesto_total"></div>
			</td>
			<td style="display: none;">
				<div class="cantidad"></div>
			</td>
			<td style="display: none;">
				<div class="precio_total"></div>
			</td>
			<td style="display: none;">
				<div class="tasa_descuento"></div>
			</td>
			<td style="display: none;">
				<div class="valor_total_descuento"></div>
			</td>
			<td> 
				<button id="btn_listar_items" style="border: 0; background: transparent;"> <i class="fa fa-btn fa-search"></i> </button>
			</td>
			<td>
				{{ Form::text( 'inv_producto_id', null, [ 'class' => 'form-control', 'id' => 'inv_producto_id' ] ) }}
			</td>
			<td> 
				<input class="form-control" id="cantidad" width="30px" name="cantidad" type="text">
			</td>
			<td>
				<input class="form-control" id="precio_unitario" name="precio_unitario" type="text">
			</td>
			<td>
				<input class="form-control" id="tasa_descuento" width="30px" name="tasa_descuento" type="text">
			</td>
			<td>
				<input class="form-control" id="tasa_impuesto" width="30px" name="tasa_impuesto" type="text">
			</td>
			<td>
				<input class="form-control" id="precio_total" name="precio_total" type="text">
			</td>
			<td></td>
		</tr>
	</table>

	<!-- La ventana contiene la variable contenido_modal -->
	@include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>''])


	@include('components.design.ventana_modal2',['titulo2'=>'','texto_mensaje2'=>'', 'clase_tamanio' => 'modal-lg'])

	<div id="div_plantilla_factura" style="display: none;">
		{!! $plantilla_factura !!}
	</div>


	<div class="container-fluid elemento_fondo" style="left: 0; width: 99%; background: #bce0f1; height: 42px; z-index: 999; border-top-right-radius: 10px; border-top-left-radius: 10px; margin: 0px 10px;"> </div>

@endsection

@section('scripts')

<script type="text/javascript">

	// Variables de cada línea de ingresos de registros.
	var producto_id, precio_total, costo_total, base_impuesto_total, valor_impuesto_total, tasa_impuesto, tasa_descuento, valor_total_descuento, cantidad, inv_producto_id, inv_bodega_id, inv_motivo_id, unidad_medida, total_factura;
	var costo_unitario = 0;
	var precio_unitario = 0;
	var base_impuesto_unitario = 0;
	var valor_impuesto_unitario = 0;
	var valor_unitario_descuento = 0;
	var total_cambio = 0;
	var valor_ajuste_al_peso = 0;

	var hay_productos = 0;

	var redondear_centena = {{ $redondear_centena }}

	var productos = {!! json_encode($productos) !!};
	var precios = {!! json_encode($precios) !!};
	var descuentos = {!! json_encode($descuentos) !!};

	function get_precio( producto_id )
	{
		var precio = precios.find( item => item.producto_codigo === producto_id );

		if ( precio === undefined )
		{
			precio = 0;
		}else{
			precio = precio.precio;
		}


		precio_unitario = precio;

		return precio;
	}

	function get_descuento( producto_id )
	{
		var descuento = descuentos.find( item => item.producto_codigo === producto_id );

		if ( descuento === undefined )
		{
			descuento = 0;
		}else{
			descuento = descuento.descuento1;
		}

		tasa_descuento = descuento;

		return descuento;
	}

	var ventana_factura;

	function ventana_imprimir()
	{

		ventana_factura = window.open( "" , "Impresión de factura POS", "width=400,height=600,menubar=no" );

		ventana_factura.document.write( $('#div_plantilla_factura').html() );

		ventana_factura.print();

		//location.reload();

	}

	function mandar_codigo( item_id )
	{
		$('#myModal').modal("hide");

		var producto = productos.find( item => item.id === parseInt( item_id ) );

		tasa_impuesto = producto.tasa_impuesto;
		inv_producto_id = producto.id;
		unidad_medida = producto.unidad_medida1;

		$('#inv_producto_id').val( producto.descripcion );
		$('#precio_unitario').val( get_precio( producto.id ) );
		$('#tasa_descuento').val( get_descuento( producto.id ) );

		$('#cantidad').select();


	}

	$(document).ready(function(){

			checkCookie();

			$('#btn_guardar').hide();

			$('#fecha').val( get_fecha_hoy() );

			agregar_la_linea_ini();	

			// Elementos al final de la página
			$('#cliente_input').parent().parent().attr('class','elemento_fondo');
			$('#vendedor_id').parent().parent().attr('class','elemento_fondo');


		    // Al cambiar la fecha
		    $('#fecha').on('change',function(){

		    	// Reset línea de registro de productos
		    	$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');

				// Se habilitan los campos necesarios
				$('#precio_unitario').removeAttr('style');
				$('#precio_unitario').removeAttr('disabled');

				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');
		    });


		    $('#cliente_input').on('focus',function(){
		    	$(this).select();
		    });

			$("#cliente_input").after('<div id="clientes_suggestions"> </div>');

			// Al ingresar código, descripción o código de barras del producto
		    $('#cliente_input').on('keyup',function(){

		    	reset_campos_formulario();

		    	var codigo_tecla_presionada = event.which || event.keyCode;

		    	switch( codigo_tecla_presionada )
		    	{
		    		case 27:// 27 = ESC
						$('#clientes_suggestions').html('');
	                	$('#clientes_suggestions').hide();
		    			break;

		    		case 40:// Flecha hacia abajo
						var item_activo = $("a.list-group-item.active");					
						item_activo.next().attr('class','list-group-item list-group-item-cliente active');
						item_activo.attr('class','list-group-item list-group-item-cliente');
						$('#cliente_input').val( item_activo.next().html() );
		    			break;

		    		case 38:// Flecha hacia arriba
						$(".flecha_mover:focus").prev().focus();
						var item_activo = $("a.list-group-item.active");					
						item_activo.prev().attr('class','list-group-item list-group-item-cliente active');
						item_activo.attr('class','list-group-item list-group-item-cliente');
						$('#cliente_input').val( item_activo.prev().html() );
		    			break;

		    		case 13:// Al presionar Enter

		    			if ( $(this).val() == '' )
						{
							return false;
						}

						var item = $('a.list-group-item.active');
						
						if( item.attr('data-cliente_id') === undefined )
						{
							alert('El cliente ingresado no existe.');
							reset_campos_formulario();
						}else{
							seleccionar_cliente( item );
						}
		    			break;

		    		default :
			    		// Manejo código de producto o nombre
			    		var campo_busqueda = 'descripcion';
			    		if( $.isNumeric( $(this).val() ) ){
				    		var campo_busqueda = 'numero_identificacion';
				    	}

				    	// Si la longitud es menor a tres, todavía no busca
					    if ( $(this).val().length < 2 ) { return false; }

				    	var url = '../vtas_consultar_clientes';

						$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
							.done(function( data ) {
								// Se llena el DIV con las sugerencias que arooja la consulta
				                $('#clientes_suggestions').show().html(data);
				                $('a.list-group-item.active').focus();
							});
		    			break;
		    	}
		    });


		    //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-cliente', function(){
            	seleccionar_cliente( $(this) );
            	return false;
            });


			// Al seleccionar una bodega, se ubica en el siguiente elemento
			$('#inv_bodega_id').change(function(){

				reset_linea_ingreso_default();

				$('#inv_producto_id').focus();

				if( $('#url_id_transaccion').val()==2 ) 
				{ // Si es una transferencia
					$('#bodega_destino_id').focus();
				}

			});


		    // Al Activar/Inactivar modo de ingreso
		    $('#modo_ingreso').on('click',function(){

		    	if( $(this).val() == "true" ){
	        		$(this).val( "false" );
	        		setCookie("modo_ingreso_codigo_de_barra", "false", 365);
	        	}else{
	        		$(this).val( "true" );
	        		setCookie("modo_ingreso_codigo_de_barra", "true", 365);
	        	}
		    	
		    	reset_linea_ingreso_default();
		    });

		    $('[data-toggle="tooltip"]').tooltip();
		    var terminar = 0; // Al presionar ESC dos veces, se posiciona en el botón guardar
		    // Al ingresar código, descripción o código de barras del producto
		    $('#inv_producto_id').on('keyup',function(event){

		    	$("[data-toggle='tooltip']").tooltip('hide');
		    	$('#popup_alerta').hide();

				var codigo_tecla_presionada = event.which || event.keyCode;

				switch( codigo_tecla_presionada )
		    	{
		    		case 27: // 27 = ESC
						
						$('#efectivo_recibido').select();
						
		    			break;

		    		case 13: // Al presionar Enter


		    			if ( $(this).val() == '' )
						{
							return false;
						}

						// Si la longitud del codigo ingresado es mayor que 5
						// se supone que es un código de barras
						var campo_busqueda = '';
						if ( $(this).val().length > 5 )
						{
							var producto = productos.find( item => item.codigo_barras === $(this).val() );
							campo_busqueda = 'codigo_barras';
						}else{
							var producto = productos.find( item => item.id === parseInt( $(this).val() ) );
							campo_busqueda = 'id';
						}

						if ( producto !== undefined )
						{

							tasa_impuesto = producto.tasa_impuesto;
							inv_producto_id = producto.id;
							unidad_medida = producto.unidad_medida1;

							$(this).val( producto.descripcion );
							$('#precio_unitario').val( get_precio( producto.id ) );
							$('#tasa_descuento').val( get_descuento( producto.id ) );

							if ( campo_busqueda == 'id' )
							{
								$('#cantidad').select();
							}else{
								// Por código de barras, se agrega la línea con un unidad de producto
								$('#cantidad').val( 1 );
								
								cantidad = 1;

								calcular_valor_descuento();
								calcular_impuestos();
								calcular_precio_total();
								agregar_nueva_linea();
							}

							
						}else{
							$('#popup_alerta').show();
							$('#popup_alerta').css('background-color','red');
							$('#popup_alerta').text( 'Producto no encontrado.' );
							$(this).select();
						}
						

		    			break;

		    		default :
		    			break;
		    	}

		    });

		    $('#efectivo_recibido').on('keyup',function(event){


		    	var codigo_tecla_presionada = event.which || event.keyCode;

		    	if( codigo_tecla_presionada == 27 )
		    	{
		    		$('#inv_producto_id').focus();
		    		return false;
		    	}

		    	if ( $('#valor_total_factura').val() <= 0 )
		    	{
		    		return false;
		    	}

		    	if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					switch( codigo_tecla_presionada )
			    	{
			    		case 13: // Al presionar Enter

			    			if ( total_cambio.toFixed(0) >= 0 )
			    			{
			    				$('#btn_guardar_factura').focus();
			    			}else{
			    				return false;
			    			}

			    			break;

			    		default :

			    			$('#lbl_efectivo_recibido').text( '$ ' + new Intl.NumberFormat("de-DE").format( parseFloat( $(this).val() ).toFixed(2) ) );

			    			total_cambio = ( redondear_a_centena( parseFloat( $('#valor_total_factura').val() ) ) - parseFloat( $(this).val() ) ) * -1;

			    			if ( total_cambio.toFixed(0) >= 0 )
			    			{
			    				$('#btn_guardar_factura').removeAttr('disabled');
			    				$('#div_total_cambio').attr('class','alert alert-success');
			    				
			    			}else{
			    				$('#btn_guardar_factura').attr('disabled','disabled');
			    				$('#div_total_cambio').attr('class','alert alert-danger');
			    			}

			    			// Label
			    			$('#total_cambio').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_cambio.toFixed(0) ) );
			    			// Input hidden
			    			$('#valor_total_cambio').val( total_cambio );

			    			calcular_totales();

			    			break;
			    	}

				}else{
					return false;
				}

		    });

			function reset_efectivo_recibido()
			{
				$('#efectivo_recibido').val('');
				$('#lbl_efectivo_recibido').text('$ 0');
				$('#total_cambio').text('$ ');
				$('#lbl_ajuste_al_peso').text('$ ');
				total_cambio = 0;
				$('#btn_guardar_factura').attr('disabled','disabled');
			}


			/*
			** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
			*/
			var ir_al_precio_total = 0;
			$('#cantidad').keyup(function(event){
				
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 && $(this).val() == '' )
				{
					$('#precio_unitario').select();
					return false;
				}

				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					cantidad = parseFloat( $(this).val() );

					if( codigo_tecla_presionada == 13) // ENTER
					{
						agregar_nueva_linea();				
					}

					if ( $(this).val() != '' )
					{
						calcular_valor_descuento();
						calcular_impuestos();
						calcular_precio_total();
					}
				}else{
					return false;
				}
			});

			function validar_venta_menor_costo()
			{
				if ( $("#permitir_venta_menor_costo").val() == 0 )
				{
					var ok = true;

					if ( base_impuesto_unitario < costo_unitario)
					{
						$('#popup_alerta').show();
						$('#popup_alerta').css('background-color','red');
						$('#popup_alerta').text( 'El precio está por debajo del costo de venta del producto.' + ' $'+ new Intl.NumberFormat("de-DE").format( costo_unitario.toFixed(2) ) + ' + IVA' );
						ok = false;
					}else{
						$('#popup_alerta').hide();
						ok = true;
					}
				}else{
					$('#popup_alerta').hide();
					ok = true;
				}					

				return ok;
			}

            // Al modificar el precio de venta
            $('#precio_unitario').keyup(function(event){
            	
            	var codigo_tecla_presionada = event.which || event.keyCode;

            	if( codigo_tecla_presionada == 13 && $('#cantidad').val() == '' )
				{
					$('#cantidad').select();
					return false;
				}

				if( validar_input_numerico( $(this) ) )
				{
					precio_unitario = parseFloat( $(this).val() );

					calcular_valor_descuento();

					calcular_impuestos();

					calcular_precio_total();
					
					if( codigo_tecla_presionada == 13 )
					{
						$('#tasa_descuento').focus();			
					}

				}else{

					$(this).focus();
					return false;
				}

			});

            // Valores unitarios
			function calcular_impuestos()
			{
				var precio_venta = precio_unitario - valor_unitario_descuento;

	            base_impuesto_unitario = precio_venta / ( 1 + tasa_impuesto / 100 );

	            valor_impuesto_unitario = precio_venta - base_impuesto_unitario;

			}


            $('#tasa_descuento').keyup(function(){

            	if( validar_input_numerico( $(this) ) )
				{	
					tasa_descuento = parseFloat( $(this).val() );

					var codigo_tecla_presionada = event.which || event.keyCode;
					if( codigo_tecla_presionada == 13 )
					{
						agregar_nueva_linea();
						return true;
					}

					// máximo valor permitido = 100
					if ( $(this).val() > 100 )
					{ 
						$(this).val(100);
					}
					
					calcular_valor_descuento();
					calcular_impuestos();
					calcular_precio_total();

				}else{

					$(this).focus();
					return false;
				}
			});


			function calcular_valor_descuento()
			{
				// El descuento se calcula cuando el precio tiene el IVA incluido
				valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
				valor_total_descuento = valor_unitario_descuento * cantidad;

			}


			function reset_descuento()
			{
				$('#tasa_descuento').val( 0 );
				calcular_valor_descuento();
			}
			

		    function seleccionar_cliente(item_sugerencia)
            {
            	
				// Asignar descripción al TextInput
                $('#cliente_input').val( item_sugerencia.html() );
                $('#cliente_input').css( 'background-color','white ' );

                // Asignar Campos ocultos
                $('#cliente_id').val( item_sugerencia.attr('data-cliente_id') );
                $('#zona_id').val( item_sugerencia.attr('data-zona_id') );
                $('#clase_cliente_id').val( item_sugerencia.attr('data-clase_cliente_id') );
                $('#liquida_impuestos').val( item_sugerencia.attr('data-liquida_impuestos') );
                $('#core_tercero_id').val( item_sugerencia.attr('data-core_tercero_id') );
                $('#lista_precios_id').val( item_sugerencia.attr('data-lista_precios_id') );
                $('#lista_descuentos_id').val( item_sugerencia.attr('data-lista_descuentos_id') );

                // Asignar resto de campos
                $('#vendedor_id').val( item_sugerencia.attr('data-vendedor_id') );
                $('#inv_bodega_id').val( item_sugerencia.attr('data-inv_bodega_id') );


                $('#cliente_descripcion').val( item_sugerencia.attr('data-nombre_cliente') );
                $('#numero_identificacion').val( item_sugerencia.attr('data-numero_identificacion') );
                $('#direccion1').val( item_sugerencia.attr('data-direccion1') );
                $('#telefono1').val( item_sugerencia.attr('data-telefono1') );


                var forma_pago = 'contado';
                var dias_plazo = parseInt( item_sugerencia.attr('data-dias_plazo') );
                if ( dias_plazo > 0 ) { forma_pago = 'credito'; }
                $('#forma_pago').val( forma_pago );

                // Para llenar la fecha de vencimiento
                var fecha = new Date( $('#fecha').val() );
				fecha.setDate( fecha.getDate() + (dias_plazo + 1) );
				
				var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
				var dia = fecha.getDate();// + 1; // Se le suma 1,

                if( mes < 10 )
                {
                	mes = '0' + mes;
                }

                if( dia < 10 )
                {
                	dia = '0' + dia;
                }
                $('#fecha_vencimiento').val( fecha.getFullYear() + '-' +  mes + '-' + dia );


                //Hacemos desaparecer el resto de sugerencias
                $('#clientes_suggestions').html('');
                $('#clientes_suggestions').hide();

                reset_tabla_ingreso();

   				$.get( "{{ url('vtas_get_lista_precios_cliente') }}" + "/" + $('#cliente_id').val() )
					.done(function( data ) {
						precios = data[0];
						descuentos = data[1];

					});
                
				// Bajar el Scroll hasta el final de la página
				$("html, body").animate( { scrollTop: $(document).height()+"px"} );
            }

			var numero_linea = 1;
			function agregar_nueva_linea()
			{
				if ( !calcular_precio_total() )
				{
					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color','red');
					$('#popup_alerta').text( 'Error en precio total. Por favor verifique' );
					return false;
				}

				agregar_la_linea();
			}

			function agregar_la_linea_ini()
			{
				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default_aux');

				// agregar nueva fila a la tabla
				$('#ingreso_registros').find('tfoot:last').append( fila );

				$('#inv_producto_id').focus();
			}

			function agregar_la_linea()
			{
				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

				// agregar nueva fila a la tabla
				$('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="'+numero_linea+'">' + generar_string_celdas( fila ) + '</tr>');
				
				// Se calculan los totales
				calcular_totales();

				hay_productos++;
				$('#numero_lineas').text(hay_productos);
				deshabilitar_campos_encabezado();

				// Bajar el Scroll hasta el final de la página
				$("html, body").animate( { scrollTop: $(document).height()+"px"} );

				reset_linea_ingreso_default();
				reset_efectivo_recibido();

				numero_linea++;
			}



			// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
			// Debe ser complatible con las columnas de la tabla de ingreso de registros
			function generar_string_celdas( fila )
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_producto_id">'+ inv_producto_id +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="precio_unitario">'+ precio_unitario +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto">'+ base_impuesto_unitario +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_impuesto">'+ tasa_impuesto +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="valor_impuesto">'+ valor_impuesto_unitario +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto_total">'+ base_impuesto_unitario * cantidad +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="cantidad">'+ cantidad +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="precio_total">'+ precio_total +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_descuento">'+ tasa_descuento +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="valor_total_descuento">'+ valor_total_descuento +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> &nbsp; </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> <span style="background-color:#F7B2A3;">'+ inv_producto_id + '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' + $('#inv_producto_id').val() + ' </div> </td>';
				
				num_celda++;

				//celdas[ num_celda ] = '<td>' + cantidad + ' </td>';
				celdas[ num_celda ] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + cantidad + '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' + unidad_medida + '</div>)' + ' </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> <div class="lbl_precio_unitario" style="display: inline;">'+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_unitario ) + '</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ tasa_descuento + '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' + new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(0) ) + '</div> ) </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td><div class="lbl_tasa_impuesto" style="display: inline;">'+ tasa_impuesto + '</div></td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
				celdas[ num_celda ] = '<td> <div class="lbl_precio_total" style="display: inline;">'+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_total.toFixed(0) ) + ' </div> </td> <td>' + btn_borrar + '</td>';

				var cantidad_celdas = celdas.length;
				var string_celdas = '';
				for (var i = 0; i < cantidad_celdas; i++)
				{
					string_celdas = string_celdas + celdas[i];
				}

				return string_celdas;
			}

			function deshabilitar_campos_encabezado()
			{
				$('#cliente_input').attr('disabled','disabled');
				$('#fecha').attr('disabled','disabled');
				$('#inv_bodega_id').attr('disabled','disabled');				
			}

			function habilitar_campos_encabezado()
			{
				$('#cliente_input').removeAttr('disabled');
				$('#fecha').removeAttr('disabled');
				$('#inv_bodega_id').removeAttr('disabled');
			}

			/*
			** Al eliminar una fila
			*/
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				fila.remove();

				calcular_totales();

				hay_productos--;
				numero_linea--;
				$('#numero_lineas').text(hay_productos);

				if ( hay_productos == 0)
				{
					habilitar_campos_encabezado();
				}

				reset_linea_ingreso_default();

			});

			// GUARDAR EL FORMULARIO
			$('#btn_guardar_factura').click(function(event){
				event.preventDefault();

				if( hay_productos == 0) 
				{
					alert('No ha ingresado productos.');
					reset_linea_ingreso_default();
					reset_efectivo_recibido();
					return false;		  			
				}

				// Desactivar el click del botón
				$( this ).attr( 'disabled', 'disabled' );

				$('#linea_ingreso_default').remove();
				$('#linea_ingreso_default_aux').remove();

				var table = $('#ingreso_registros').tableToJSON();
				
				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_registros').val(JSON.stringify(table));
				
			 	// No se puede enviar controles disabled
				habilitar_campos_encabezado();

				var url = $("#form_create").attr('action');
				var data = $("#form_create").serialize();

				setCookie( 'ultimo_valor_total_cambio', total_cambio, 1);
				setCookie( 'ultimo_valor_total_factura', total_factura, 1);
				setCookie( 'ultimo_valor_efectivo_recibido',  parseFloat( $('#efectivo_recibido').val() ), 1);
				setCookie( 'ultimo_valor_ajuste_al_peso',  valor_ajuste_al_peso, 1);

				$.post(url, data, function( doc_encabezado_consecutivo ){
					$('.lbl_consecutivo_doc_encabezado').text( doc_encabezado_consecutivo );
					llenar_tabla_productos_facturados();
					
					ventana_imprimir();

					location.reload();
				});
					
			});

			function llenar_tabla_productos_facturados()
			{
				var linea_factura;
				var lbl_total_factura = 0;

				$('.linea_registro').each(function( ){

					linea_factura = '<tr> <td> ' + $(this).find('.lbl_producto_descripcion').text() + ' </td> <td> '+ $(this).find('.cantidad').text() + ' ' + $(this).find('.lbl_producto_unidad_medida').text()  + '  (' + $(this).find('.lbl_precio_unitario').text() + ') </td> <td> ' + $(this).find('.lbl_tasa_impuesto').text() + '% </td> <td> ' + $(this).find('.lbl_precio_total').text() + '  </td></tr>';

	                if( parseFloat( $(this).find('.valor_total_descuento').text() )  != 0 )
	                {
	                	linea_factura += '<tr> <td colspan="2" style="text-align: right;">Dcto.</td> <td colspan="2"> ( -$' + new Intl.NumberFormat("de-DE").format( parseFloat( $(this).find('.valor_total_descuento').text() ).toFixed(0) ) + ' ) </td> </tr>';
	                }

	                $('#tabla_productos_facturados').find('tbody:last').append( linea_factura );

	                lbl_total_factura += parseFloat( $(this).find('.precio_total').text() );

				});


				$('.lbl_total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( redondear_a_centena(lbl_total_factura ) ) );
				$('.lbl_ajuste_al_peso').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_ajuste_al_peso ) );
				$('.lbl_total_recibido').text( '$ ' + new Intl.NumberFormat("de-DE").format( parseFloat( $('#efectivo_recibido').val() ) ) );
				$('.lbl_total_cambio').text( '$ ' + new Intl.NumberFormat("de-DE").format( redondear_a_centena( total_cambio ) ) );

				if( $('#forma_pago').val() == 'credito' )
				{
					$('#tr_fecha_vencimiento').show();
					$('.lbl_condicion_pago').text( $('#forma_pago').val() );
					$('.lbl_fecha_vencimiento').text( $('#fecha_vencimiento').val() );
				}

				$('.lbl_cliente_descripcion').text( $('#cliente_descripcion').val() );
				$('.lbl_cliente_nit').text( $('#numero_identificacion').val() );
				$('.lbl_cliente_direccion').text( $('#direccion1').val() );
				$('.lbl_cliente_telefono').text( $('#telefono1').val() );
				$('.lbl_atendido_por').text( $('#vendedor_id option:selected').text() );
				$('.lbl_descripcion_doc_encabezado').text( $('#descripcion').val() );

			}

			function redondear_a_centena( numero, aproximacion_superior = false )
			{
				if ( !redondear_centena )
				{
					return numero.toFixed(0);
				}

				var millones = 0;
				var millares = 0;
				var centenas = 0;

				var saldo1, saldo2, saldo3;

				if ( numero > 999999.99999 )
				{
					// se obtiene solo la parte entera
					millones = Math.trunc( numero / 1000000 ) * 1000000;
				}

				saldo1 = numero - millones;

				if ( saldo1 > 999.99999 )
				{
					// se obtiene solo la parte entera
					millares = Math.trunc( saldo1 / 1000 ) * 1000;
				}

				saldo2 = saldo1 - millares;

				if ( saldo2 > 99.99999 )
				{
					// se obtiene solo la parte entera
					//centenas = Math.trunc( saldo2 / 100 ) * 100;
					centenas = ( saldo2 / 100 ).toFixed(0) * 100;
				}

				return ( millones + millares + centenas );

			}

			function reset_campos_formulario()
			{
				$('#cliente_id').val( '' );
				$('#cliente_input').css( 'background-color','#FF8C8C' );
                $('#vendedor_id').val( '' );
                $('#inv_bodega_id').val( '' );
                $('#forma_pago').val( 'contado' );
				$('#fecha_vencimiento').val( '' );
                $('#lista_precios_id').val( '' );
                $('#lista_descuentos_id').val( '' );
                $('#liquida_impuestos').val( '' );

                $('#core_tercero_id').val( '' );
                $('#lineas_registros').val( 0 );
                $('#zona_id').val( '' );
                $('#clase_cliente_id').val( '' );
			}

			function reset_tabla_ingreso()
			{
				$('.linea_registro').each(function( ){
					$(this).remove();
				});

				// reset totales
				$('#total_cantidad').text( '0' );

				// Subtotal (Sumatoria de base_impuestos por cantidad)
				$('#subtotal').text( '$ 0' );

				$('#descuento').text( '$ 0' );

				// Total impuestos (Sumatoria de valor_impuesto por cantidad)
				$('#total_impuestos').text( '$ 0' );

				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ 0' );
				$('#valor_total_factura').val( 0 );

				reset_linea_ingreso_default()
			}


			function reset_linea_ingreso_default()
			{
				$('#inv_producto_id').val('');
				$('#cantidad').val('');
				$('#precio_unitario').val('');
				$('#tasa_descuento').val('');
				$('#tasa_impuesto').val('');
				$('#precio_total').val('');

				$('#inv_producto_id').focus();

				$('#popup_alerta').hide();

				producto_id = 0; precio_total = 0; costo_total = 0; base_impuesto_total = 0; valor_impuesto_total = 0; tasa_impuesto = 0; tasa_descuento = 0; valor_total_descuento = 0; cantidad = 0; costo_unitario = 0; precio_unitario = 0; base_impuesto_unitario = 0; valor_impuesto_unitario = 0; valor_unitario_descuento = 0;
			}

			function calcular_precio_total()
			{
				precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;
				
				$('#precio_total').val(0);

				if( $.isNumeric( precio_total ) && precio_total > 0 )
				{
					$('#precio_total').val( precio_total );
					return true;
				}else{
					precio_total = 0;
					return false;
				}
			}


			function calcular_totales()
			{	
				var cantidad = 0.0;
				var subtotal = 0.0;
				var valor_total_descuento = 0.0;
				var total_impuestos = 0.0;
				total_factura = 0.0;
				
				$('.linea_registro').each(function()
				{
				    cantidad += parseFloat( $(this).find('.cantidad').text() );
				    subtotal += parseFloat( $(this).find('.base_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
				    valor_total_descuento += parseFloat( $(this).find('.valor_total_descuento').text() );
				    total_impuestos += parseFloat( $(this).find('.valor_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
				    total_factura += parseFloat( $(this).find('.precio_total').text() );

				});

				$('#total_cantidad').text( new Intl.NumberFormat("de-DE").format( cantidad ) );

				// Subtotal (Sumatoria de base_impuestos por cantidad)
				//var valor = ;
				$('#subtotal').text( '$ ' + new Intl.NumberFormat("de-DE").format( (subtotal + valor_total_descuento).toFixed(2) )  );

				$('#descuento').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(2) )  );

				// Total impuestos (Sumatoria de valor_impuesto por cantidad)
				$('#total_impuestos').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_impuestos.toFixed(2) ) );

				// label Total factura  (Sumatoria de precio_total)
				var valor_redondeado = redondear_a_centena( total_factura );
				$('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_redondeado )  );
				
				// input hidden
				$('#valor_total_factura').val( total_factura );

				valor_ajuste_al_peso = valor_redondeado - total_factura;

			    $('#lbl_ajuste_al_peso').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_ajuste_al_peso ) );
			}


		var valor_actual, elemento_modificar, elemento_padre;
				
			// Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
			$(document).on('dblclick','.elemento_modificar',function(){

				elemento_modificar = $(this);

				elemento_padre = elemento_modificar.parent();

				valor_actual = $(this).html();

				elemento_modificar.hide();

				elemento_modificar.after( '<input type="text" name="valor_nuevo" id="valor_nuevo" style="display:inline;"> ');

				document.getElementById('valor_nuevo').value = valor_actual;
				document.getElementById('valor_nuevo').select();

			});

			// Si la caja de texto pierde el foco
			$(document).on('blur','#valor_nuevo',function(event){

				var x = event.which || event.keyCode; // Capturar la tecla presionada
				if( x != 13 ) // 13 = Tecla Enter
				{
					elemento_padre.find('#valor_nuevo').remove();
			        elemento_modificar.show();
			    }

			});

			// Al presiona teclas en la caja de texto
			$(document).on('keyup','#valor_nuevo',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				// Abortar la edición
				if( x == 27 ) // 27 = ESC
				{
					elemento_padre.find('#valor_nuevo').remove();
		        	elemento_modificar.show();
		        	return false;
				}

				// Guardar
				if( x == 13 ) // 13 = ENTER
				{
					var fila = $(this).closest("tr");
		        	guardar_valor_nuevo( fila );
				}
			});


			$("#btn_listar_items").click(function(event){

		        $("#myModal").modal({keyboard: true});
		        $(".btn_edit_modal").hide();
		        $(".btn_edit_modal").hide();
		        $('#myTable_filter').find('input').css( "border", "3px double red" );
		        $('#myTable_filter').find('input').select();

		    });

			$(document).on('click',".btn_registrar_ingresos_gastos",function(event){
				event.preventDefault();

		        $('#contenido_modal2').html('');
				$('#div_spin2').fadeIn();

		        $("#myModal2").modal(
		        	{backdrop: "static"}
		        );

		        $("#myModal2 .modal-title").text('Nuevo registro de ' + $(this).attr('data-lbl_ventana'));

		        $("#myModal2 .btn_edit_modal").hide();
		        $("#myModal2 .btn-danger").hide();
				$("#myModal2 .btn_save_modal").show();
		        
		        var url = "{{ url('ventas_pos_form_registro_ingresos_gastos') }}" + "/" + $('#pdv_id').val() + "/" + $(this).attr('data-id_modelo') + "/" + $(this).attr('data-id_transaccion');

		        $.get( url, function( respuesta ){
		        	$('#div_spin2').hide();
		        	$('#contenido_modal2').html( respuesta );
		        });/**/
		    });
		    

			$(document).on('click',".btn_consultar_estado_pdv",function(event){
				event.preventDefault();

		        $('#contenido_modal2').html('');
				$('#div_spin2').fadeIn();

		        $("#myModal2").modal(
		        	{backdrop: "static"}
		        );

		        $("#myModal2 .modal-title").text('Consulta de ' + $(this).attr('data-lbl_ventana'));

		        $("#myModal2 .btn_edit_modal").hide();
				$("#myModal2 .btn_save_modal").hide();
		        
		        var url = "{{ url('pos_get_saldos_caja_pdv') }}" + "/" + $('#pdv_id').val() + "/" + "{{date('Y-m-d')}}" + "/" + "{{date('Y-m-d')}}";

		        $.get( url, function( respuesta ){
		        	$('#div_spin2').hide();
		        	$('#contenido_modal2').html( respuesta );
		        });/**/
		    });


		    $(document).on('click','#myModal2 .btn_save_modal',function(event){
		    	event.preventDefault();

		    	if( $('#combobox_motivos').val() == '' )
		    	{
		    		$('#combobox_motivos').focus();
		    		alert('Debe ingresar un Motivo');
		    		return false;
		    	}

		    	if( $('#cliente_proveedor_id').val() == '' )
		    	{
		    		$('#cliente_proveedor_id').focus();
		    		alert('Debe ingresar un Cliente/Proveedor.');
		    		return false;
		    	}

		    	if( !validar_input_numerico( $('#col_valor') ) || $('#col_valor').val() == '' )
		    	{
		    		alert('No ha ingresado una valor para la transacción.');
		    		return false;
		    	}

		    	var url = $("#form_registrar_ingresos_gastos").attr('action');
				var data = $("#form_registrar_ingresos_gastos").serialize();

				$.post(url, data, function( respuesta ){
					$('#contenido_modal2').html( respuesta );
					$("#myModal2 .btn-danger").show();
					$("#myModal2 .btn_save_modal").hide();
				});

		    });
		    

			$(document).on('click',".btn_consultar_documentos",function(event){
				event.preventDefault();

		        $('#contenido_modal2').html('');
				$('#div_spin2').fadeIn();

		        $("#myModal2").modal(
		        	{backdrop: "static"}
		        );

		        $("#myModal2 .modal-title").text('Consulta de ' + $(this).attr('data-lbl_ventana'));

		        $("#myModal2 .btn_edit_modal").hide();
				$("#myModal2 .btn_save_modal").hide();
		        
		        var url = "{{ url('pos_consultar_documentos_pendientes') }}" + "/" + $('#pdv_id').val() + "/" + $('#fecha').val() + "/" + $('#fecha').val();

		        $.get( url, function( respuesta ){
		        	$('#div_spin2').hide();
		        	$('#contenido_modal2').html( respuesta );
		        });/**/
		    });
		    
			var fila;
			$(document).on('click',".btn_anular_factura",function(event){
				event.preventDefault();

				var opcion = confirm('¿Seguro desea anular la factura ' + $(this).attr('data-lbl_factura') + ' ?');

				if ( opcion )
				{
					fila = $(this).closest("tr");

					$('#div_spin2').fadeIn();
					var url = "{{ url('pos_factura_anular') }}" + "/" + $(this).attr('data-doc_encabezado_id');

			        $.get( url, function( respuesta ){
			        	$('#div_spin2').hide();

			        	fila.find('td').eq(6).text('Anulado');
			        	fila.find('.btn_anular_factura').hide();
			        	alert('Documento anulado correctamente.');
			        });
				}else{
					return false;
				}
		    });


			function guardar_valor_nuevo( fila )
			{
				var valor_nuevo = document.getElementById('valor_nuevo').value;

				// Si no cambió el valor_nuevo, no pasa nada
				if ( valor_nuevo == valor_actual) { return false; }

				elemento_modificar.html( valor_nuevo );
				elemento_modificar.show();

				cantidad = parseFloat( valor_nuevo );
				
				$('#inv_producto_id').focus();

				reset_efectivo_recibido();

				calcular_precio_total_lbl( fila );
				calcular_totales();

				elemento_padre.find('#valor_nuevo').remove();
			}



			function calcular_precio_total_lbl( fila )
			{
				precio_unitario = parseFloat( fila.find('.precio_unitario').text() );
				base_impuesto_unitario = parseFloat( fila.find('.base_impuesto').text() );
				tasa_descuento = parseFloat( fila.find('.tasa_descuento').text() );

				valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
				valor_total_descuento = valor_unitario_descuento * cantidad;

				precio_total = ( precio_unitario - valor_unitario_descuento ) * cantidad;

			    fila.find('.cantidad').text( cantidad );
			    
			    fila.find('.precio_total').text( precio_total );

			    fila.find('.base_impuesto_total').text( base_impuesto_unitario * cantidad );

			    fila.find('.valor_total_descuento').text( valor_total_descuento );

			    fila.find('.lbl_valor_total_descuento').text( new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(2) ) );

			    fila.find('.lbl_precio_total').text( new Intl.NumberFormat("de-DE").format( precio_total.toFixed(2) ) );
			}



			function setCookie(cname, cvalue, exdays) {
			  var d = new Date();
			  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			  var expires = "expires="+d.toUTCString();
			  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			}

			function getCookie(cname) {
			  var name = cname + "=";
			  var ca = document.cookie.split(';');
			  for(var i = 0; i < ca.length; i++) {
			    var c = ca[i];
			    while (c.charAt(0) == ' ') {
			      c = c.substring(1);
			    }
			    if (c.indexOf(name) == 0) {
			      return c.substring(name.length, c.length);
			    }
			  }
			  return "";
			}

			function checkCookie()
			{
			  var ultimo_valor_total_factura = parseFloat( getCookie("ultimo_valor_total_factura") );
			  var ultimo_valor_efectivo_recibido = parseFloat( getCookie("ultimo_valor_efectivo_recibido") );
			  var ultimo_valor_total_cambio = parseFloat( getCookie("ultimo_valor_total_cambio") );
			  var ultimo_valor_ajuste_al_peso = parseFloat( getCookie("ultimo_valor_ajuste_al_peso") );

			  if( ultimo_valor_total_factura > 0 )
			  {
			  	$('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( redondear_a_centena( ultimo_valor_total_factura ) ) );
			  	$('#lbl_efectivo_recibido').text( '$ ' + new Intl.NumberFormat("de-DE").format( ultimo_valor_efectivo_recibido.toFixed(2) ) );
			  	//$('#total_cambio').text( '$ ' + new Intl.NumberFormat("de-DE").format( redondear_a_centena( ultimo_valor_total_cambio) ) );
			  	$('#total_cambio').text( '$ ' + new Intl.NumberFormat("de-DE").format( ( ultimo_valor_total_cambio) ) );
			  	$('#lbl_ajuste_al_peso').text( '$ ' + new Intl.NumberFormat("de-DE").format( ultimo_valor_ajuste_al_peso ) );
			  	$("html, body").animate( { scrollTop: $(document).height()+"px"} );
			  }

			}

		});

</script>

@endsection