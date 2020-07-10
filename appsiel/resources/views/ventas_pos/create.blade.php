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
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
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

				{{ Form::hidden('pdv_id',Input::get('pdv_id')) }}
				{{ Form::hidden('cajero_id', Auth::user()->id ) }}

				{{ Form::hidden('inv_bodega_id_aux',$pdv->bodega_default_id,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="cliente_id" id="cliente_id" value="{{$pdv->cliente_default_id}}" required="required">
				<input type="hidden" name="zona_id" id="zona_id" value="{{$pdv->cliente->zona_id}}" required="required">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="{{$pdv->cliente->clase_cliente_id}}" required="required">

				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$pdv->cliente->core_tercero_id}}" required="required">
				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="{{$pdv->cliente->lista_precios_id}}" required="required">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="{{$pdv->cliente->lista_descuentos_id}}" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="{{$pdv->cliente->liquida_impuestos}}" required="required">

				<input type="hidden" name="inv_motivo_id" id="inv_motivo_id" value="{{$inv_motivo_id}}">

				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

				<input type="hidden" name="tipo_transaccion"  id="tipo_transaccion" value="factura_directa">

				<input type="hidden" name="rm_tipo_transaccion_id"  id="rm_tipo_transaccion_id" value="{{config('ventas')['rm_tipo_transaccion_id']}}">
				<input type="hidden" name="dvc_tipo_transaccion_id"  id="dvc_tipo_transaccion_id" value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

				<input type="hidden" name="saldo_original" id="saldo_original" value="0">

				<input type="hidden" name="valor_total_cambio" id="valor_total_cambio" value="0">

				<div id="popup_alerta"> </div>
				
			{{ Form::close() }}

			<br/>

			<div class="container-fluid">
				<div class="row">
					
					<div class="col-md-8">
						{!! $tabla->dibujar() !!}
						Productos ingresados: <span id="numero_lineas"> 0 </span>
					</div>

					<div class="col-md-4 well" style="font-size: 1.3em;">
						<h1 style="width: 100%; text-align: center;">Totales</h1>
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
	<br/><br/>

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
				<label class="checkbox-inline" title="Activar ingreso por código de barras">
					<input type="checkbox" id="modo_ingreso" name="modo_ingreso" value="false"><i class="fa fa-barcode"></i>
				</label>
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
@endsection

@section('scripts')

<script type="text/javascript">

	// Variables de cada línea de ingresos de registros.
	var producto_id, precio_total, costo_total, base_impuesto_total, valor_impuesto_total, tasa_impuesto, tasa_descuento, valor_total_descuento, cantidad, inv_producto_id, inv_bodega_id, inv_motivo_id, unidad_medida;
	var costo_unitario = 0;
	var precio_unitario = 0;
	var base_impuesto_unitario = 0;
	var valor_impuesto_unitario = 0;
	var valor_unitario_descuento = 0;
	var total_cambio = 0;

	var hay_productos = 0;

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

	function ventana_imprimir( doc_encabezado_id )
	{
		window.open( "{{ url('pos_factura_imprimir') }}" + "/" + doc_encabezado_id + "?id=" + getParameterByName('id') + "&id_modelo=" + getParameterByName('id_modelo') + "&id_transaccion=" + getParameterByName('id_transaccion') , "Impresión de factura POS", "width=800,height=600,menubar=no" );
		//window.print();
	}

	$(document).ready(function(){

			checkCookie();

			$('#btn_guardar').hide();

			$('#fecha').val( get_fecha_hoy() );

			agregar_la_linea_ini();	

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
						
						$('#efectivo_recibido').focus();
						
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

		    	if ( $('#valor_total_factura').val() <= 0 )
		    	{
		    		return false;
		    	}

		    	var codigo_tecla_presionada = event.which || event.keyCode;

		    	if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					switch( codigo_tecla_presionada )
			    	{
			    		case 13: // Al presionar Enter

			    			if ( total_cambio >= 0 )
			    			{
			    				$('#btn_guardar_factura').focus();
			    			}else{
			    				return false;
			    			}

			    			break;

			    		default :

			    			$('#lbl_efectivo_recibido').text( '$ ' + new Intl.NumberFormat("de-DE").format( parseFloat( $(this).val() ).toFixed(2) ) );

			    			total_cambio = ( parseFloat( $('#valor_total_factura').val() ) - parseFloat( $(this).val() ) ) * -1;

			    			if ( total_cambio >= 0 )
			    			{
			    				$('#btn_guardar_factura').removeAttr('disabled');
			    				$('#div_total_cambio').attr('class','alert alert-success');
			    				
			    			}else{
			    				$('#btn_guardar_factura').attr('disabled','disabled');
			    				$('#div_total_cambio').attr('class','alert alert-danger');
			    			}

			    			$('#total_cambio').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_cambio.toFixed(2) ) );
			    			$('#valor_total_cambio').val( total_cambio );

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
				total_cambio = 0;
				$('#btn_guardar_factura').attr('disabled','disabled');
			}


			/*
			** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
			*/
			var ir_al_precio_total = 0;
			$('#cantidad').keyup(function(event){
				
				var codigo_tecla_presionada = event.which || event.keyCode;

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

				if( validar_input_numerico( $(this) ) )
				{
					precio_unitario = parseFloat( $(this).val() );

					calcular_valor_descuento();

					calcular_impuestos();

					calcular_precio_total();

					var codigo_tecla_presionada = event.which || event.keyCode;
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

						console.log( [ precios, descuentos ] );

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

				celdas[ num_celda ] = '<td> <span style="background-color:#F7B2A3;">'+ inv_producto_id + "</span> " + $('#inv_producto_id').val() + ' (' + unidad_medida + ')' + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>' + cantidad + ' </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> '+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_unitario ) + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ tasa_descuento + '% ( $' + new Intl.NumberFormat("de-DE").format( valor_total_descuento ) + ' ) </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ tasa_impuesto + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
				celdas[ num_celda ] = '<td> '+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_total ) + ' </td><td>' + btn_borrar + '</td>';

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

				$.post(url, data, function( doc_encabezado_id ){

					location.reload();

					ventana_imprimir(doc_encabezado_id);
				});

		 		// Enviar formulario
				//$('#form_create').submit();					
			});

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
				var total_factura = 0.0;
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

				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_factura.toFixed(2) ) );
				
				$('#valor_total_factura').val( total_factura );
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

			function checkCookie() {
			  var modo_ingreso_codigo_de_barra = getCookie("modo_ingreso_codigo_de_barra");

			  if (modo_ingreso_codigo_de_barra == "true" || modo_ingreso_codigo_de_barra == "")
			  {
		        $('#modo_ingreso').attr('checked','checked');
		        $('#modo_ingreso').val( "true" );
			  }else{
			  	$('#modo_ingreso').removeAttr('checked');
		        $('#modo_ingreso').val( "false" );
			  }
			}

		});

</script>

@endsection