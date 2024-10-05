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
		#div_list_suggestions {
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

		.alert .close {
		    color: #574696 !important;
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

				{{ Form::hidden('url_id', Input::get('id')) }}
				{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
				{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion'), ['id'=>'url_id_transaccion'] ) }}

				<input type="hidden" name="proveedor_id" id="proveedor_id" value="" required="required">
				<input type="hidden" name="clase_proveedor_id" id="clase_proveedor_id" value="" required="required">


				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="" required="required">
				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

				<input type="hidden" name="tipo_transaccion"  id="tipo_transaccion" value="factura_directa">

				<input type="hidden" name="saldo_original" id="saldo_original" value="0">

				<input type="hidden" name="valor_total_retefuente" id="valor_total_retefuente" value="0">
				<input type="hidden" name="retencion_id" id="retencion_id" value="0">

				<input type="hidden" name="inv_bodega_default_id" id="inv_bodega_default_id" value="{{ config('inventarios.item_bodega_principal_id') }}">				
				
				<div id="popup_alerta"> </div>

				<input type="hidden" name="lineas_registros_medios_recaudo" id="lineas_registros_medios_recaudo" value="0">

				<p style="display: none;">
					<input type="hidden" name="item_sugerencia_cliente" id="item_sugerencia_cliente" value="{{$item_sugerencia_cliente}}">
				</p>

			{{ Form::close() }}
			<br/>

			@include('compras.incluir.elementos_entradas_pendientes')

			<br/>

			<div id="div_ingreso_registros">

				<!-- 
					<div class="container-fluid"> 
						<label class="checkbox-inline" title="Activar ingreso por código de barras"><input type="checkbox" id="modo_ingreso" name="modo_ingreso"><i class="fa fa-barcode"></i> <i>Activar ingreso por código de barras</i></label>
					</div>
				-->

			    {!! $tabla->dibujar() !!}

			    Productos ingresados: <span id="numero_lineas"> 0 </span>

			</div>
				
				<div style="text-align: right;">
					<div id="total_cantidad" style="display: none;"> 0 </div>
	            	<table style="display: inline;">
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> 
								Subtotal: &nbsp; 
							</td> 
							<td>
								&nbsp;
							</td>
							<td> 
								<div id="subtotal"> $ 0 </div> 
							</td>
							<td>
								&nbsp;
							</td>
	            		</tr>
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> Descuento: &nbsp; </td> 
							<td>
								&nbsp;
							</td>
							 <td> <div id="descuento"> $ 0 </div> </td>
							 <td>
								 &nbsp;
							 </td>
	            		</tr>
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> 
							<td>
								&nbsp;
							</td>
							 <td> <div id="total_impuestos"> $ 0 </div> </td>
							 <td>
								 &nbsp;
							 </td>
	            		</tr>
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> ReteFuente: &nbsp; </td> 
							<td style="width: 120px;">
								<span id="select_tasa_retefuente" style="display: none;">
									{{ Form::bsSelect('tasa_retefuente', null, ' ', App\Contabilidad\Retencion::opciones_campo_select(), ['class'=>'form-control']) }}
								</span>
							</td>
							 <td> 
								<div id="lbl_total_retefuente"> $ 0 </div>
							 </td>
							 <td>
								&nbsp;&nbsp;<button class="btn btn-xs btn-success" id="btn_add_retefuente"><i class="fa fa-plus"></i> </button>
								&nbsp;&nbsp;<button class="btn btn-xs btn-default" id="btn_cancel_retefuente" style="display: none;"><i class="fa fa-close"></i> </button>
							</td>
	            		</tr>
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> 
							<td>
								&nbsp;
							</td>
							<td> <div id="total_factura"> $ 0 </div> </td>
							<td>
								&nbsp;
							</td>
	            		</tr>
	            	</table>
				</div>
			<div id="mostrar_medios_recaudos">
				@include('tesoreria.incluir.medios_recaudos')
			</div>
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script src="{{ asset( 'assets/js/compras/functions_create.js?aux=' . uniqid() )}}"></script>

	<script type="text/javascript">

		$(document).ready(function(){

			checkCookie();

			$('#fecha').val( get_fecha_hoy() );

			$('#fecha_vencimiento').attr( 'readonly','readonly' );

			$('#proveedor_input').select( );

			$("#proveedor_input").after('<div id="div_list_suggestions"> </div>');

			if ( $('#item_sugerencia_cliente').val() != '' && $('#item_sugerencia_cliente').val() != '0' && $('#item_sugerencia_cliente').val() != undefined )
			{
				console.log('go')
				$('#item_sugerencia_cliente').after( $('#item_sugerencia_cliente').val() );
				seleccionar_proveedor( $('#item_sugerencia_cliente').next() );
			}

			$('#forma_pago').on('change',function (event){
				
				$('#fecha_vencimiento').val( actualizar_fecha_vencimiento( new Date( $('#fecha').val() ) ) );

				if($('#forma_pago').val() == 'contado'){
					$('#mostrar_medios_recaudos').removeAttr('style','display');
					$('#fecha_vencimiento').attr( 'readonly','readonly' );
				}else{
					$('#mostrar_medios_recaudos').attr('style','display:none');
					$('#fecha_vencimiento').removeAttr( 'readonly' );
				}

			});

			$('#fecha').on('change',function(event){
				var fecha = new Date( $(this).val() );
				$('#fecha_vencimiento').val( actualizar_fecha_vencimiento( fecha ) );
		    	
		    	// Reset línea de registro
		    	$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');

				$('#inv_motivo_id').attr('style','background-color:#ECECE5;');
				$('#inv_motivo_id').attr('disabled','disabled');

				$('#precio_unitario').removeAttr('style');
				$('#precio_unitario').removeAttr('disabled');

				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');

			});

		    $('#proveedor_input').on('focus',function(){
		    	$(this).select();
		    });
			
			// Al ingresar número de identificacion o descripción del proveedor
		    $('#proveedor_input').on('keyup',function(){

		    	reset_campos_formulario();

		    	var codigo_tecla_presionada = event.which || event.keyCode;

		    	switch( codigo_tecla_presionada )
		    	{
		    		case 27:// 27 = ESC
						$('#div_list_suggestions').html('');
                		$('#div_list_suggestions').hide();
		    			break;

		    		case 40:// Flecha hacia abajo
		    			var item_activo = $("a.list-group-item.active");

						// Si es el útimo item, entonces no se mueve hacia abajo
						if( item_activo.attr('data-ultimo_item') == 1 )
						{
							return false;
						}
					
						item_activo.next().attr('class','list-group-item list-group-item-proveedor active');
						item_activo.attr('class','list-group-item list-group-item-proveedor');
						$('#proveedor_input').val( item_activo.next().html() );
		    			break;

		    		case 38:// Flecha hacia arriba
		    			var item_activo = $("a.list-group-item.active");

						// Si es el útimo item, entonces no se mueve hacia abajo
						if( item_activo.attr('data-primer_item') == 1 )
						{
							return false;
						}

						item_activo.prev().attr('class','list-group-item list-group-item-proveedor active');
						item_activo.attr('class','list-group-item list-group-item-proveedor');
						$('#proveedor_input').val( item_activo.prev().html() );
		    			break;

		    		case 13:// Al presionar Enter

		    			if ( $(this).val() == '' )
						{
							return false;
						}

		    			window[tecla_enter_on_keyup( $('a.list-group-item.active') ) ];
		    			break;

		    		default :
		    			// Si no se presiona tecla especial, se muestra listado de sugerencias

		    		// Manejo código de producto o nombre
		    		var campo_busqueda = 'descripcion';
		    		if( $.isNumeric( $(this).val() ) ){
			    		var campo_busqueda = 'numero_identificacion';
			    	}

			    	// Si la longitud es menor a tres, todavía no busca
				    if ( $(this).val().length < 2 ) { return false; }

			    	var url = '../compras_consultar_proveedores';

					$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
						.done(function( data ) {
							// Se llena el DIV con las sugerencias que arooja la consulta
			                $('#div_list_suggestions').show().html(data);
			                $('a.list-group-item.active').focus();
						});
		    			break;
		    	}	
		    });

		    //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-proveedor', function(){
            	seleccionar_proveedor( $(this) );
            	return false;
            });

			// Al seleccionar una bodega, se ubica en el siguiente elemento
			$('#inv_bodega_id').change(function(){
				$('#inv_producto_id').select();
			});
			
			$('#doc_proveedor_prefijo').on('keyup',function(){
				var codigo_tecla_presionada = event.which || event.keyCode;
				if( codigo_tecla_presionada == 13 )
				{
					$('#doc_proveedor_consecutivo').focus();
				}
			});
			
			$('#doc_proveedor_consecutivo').on('keyup',function(){

				$('#popup_alerta').hide();

				var codigo_tecla_presionada = event.which || event.keyCode;
				if( codigo_tecla_presionada == 13 )
				{
					$('#inv_producto_id').select();
					$("html, body").animate( { scrollTop: $(document).height()+"px"} );
				}
				
				validar_documento_proveedor();

			});



		    $('[data-toggle="tooltip"]').tooltip();
		    var terminar = 0; // Al presionar ESC dos veces, se posiciona en el botón guardar
		    
		    // Al ingresar código, descripción o código de barras del producto
		    $('#inv_producto_id').on('keyup',function(event){

		    	$("[data-toggle='tooltip']").tooltip('hide');

		    	if ( validar_requeridos() == false )
				{
					return false;
				}

				var codigo_tecla_presionada = event.which || event.keyCode;
				
				// 27 = ESC
				if( codigo_tecla_presionada == 27 )
		    	{
					terminar++;
					$('#suggestions').html('');
                	$('#suggestions').hide();

                	if ( terminar == 2 ){ 
                		terminar = 0;
                		$('#btn_guardar').focus(); 
                	}
                	return false;
		    	}

				if( $('#modo_ingreso').is(':checked') )
		    	{
		    		// Manejo códigos de barra
		    		var campo_busqueda = 'codigo_barras'; // Busqueda por CÓDIGO DE BARRA
		    		// Realizar consulta y mostar sugerencias

			    	var url = '../inv_consultar_productos';

					$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
						.done(function( data ) {
							//Escribimos las sugerencias que nos manda la consulta
			                $('#suggestions').show().html(data);
			                $('.list-group-item-productos:first').focus();

			                var item = $('a.list-group-item.active');
						
							if( item.attr('data-producto_id') === undefined )
							{
								//alert('El producto ingresado no existe.');
								//reset_linea_ingreso_default();
								$('#inv_producto_id').select();
							}else{
								seleccionar_producto( item );
			                	consultar_existencia( $('#inv_bodega_id').val(), item.attr('data-producto_id') );
			                	return false;
							}

						});
					return false;
		    	}

		    	switch( codigo_tecla_presionada )
		    	{
		    		case 40:// Flecha hacia abajo
		    			var item_activo = $("a.list-group-item.active");

						// Si es el útimo item, entonces no se mueve hacia abajo
						if( item_activo.attr('data-ultimo_item') == 1 )
						{
							return false;
						}

						item_activo.next().attr('class','list-group-item list-group-item-productos active');
						item_activo.attr('class','list-group-item list-group-item-productos');
						$('#inv_producto_id').val( item_activo.html() );
		    			break;

		    		case 38:// Flecha hacia arriba
		    			$(".flecha_mover:focus").prev().focus();
						var item_activo = $("a.list-group-item.active");

						// Si es el primer item, entonces no se mueve hacia arriba
						if( item_activo.attr('data-primer_item') == 1 )
						{
							return false;
						}
											
						item_activo.prev().attr('class','list-group-item list-group-item-productos active');
						item_activo.attr('class','list-group-item list-group-item-productos');
						$('#inv_producto_id').val( item_activo.html() );
		    			break;

		    		case 13:// Al presionar Enter

		    			if ( $(this).val() == '' )
						{
							return false;
						}

		    			var item = $('a.list-group-item.active');
						
						if( item.attr('data-producto_id') === undefined )
						{
							alert('El producto ingresado no existe.');
							reset_linea_ingreso_default();
						}else{
							seleccionar_producto( item );
		                	consultar_existencia( $('#inv_bodega_id').val(), item.attr('data-producto_id') );
		                	return false;
						}
		    			break;

		    		default :
		    			if( $.isNumeric( $(this).val() ) ){
					    	var campo_busqueda = 'id'; // Busqueda por CODIGO (ID en base de datos)
				    	}else{
				    		var campo_busqueda = 'descripcion'; // Busqueda por NOMBRE

				    		// Si la longitud es menor a tres, todavía no busca
				    		if ( $(this).val().length < 2 ) { return false; }
				    	}

				    	terminar = 0;

				    	// Realizar consulta y mostar sugerencias
				    	var url = '../inv_consultar_productos';

						$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
							.done(function( data ) {
								//Escribimos las sugerencias que nos manda la consulta
				                $('#suggestions').show().html(data);
				                $('.list-group-item-productos:first').focus();
							});
		    			break;
		    	}
		    });


            //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-productos', function(){
                
            	seleccionar_producto( $(this) );

                // Consultar datos de existencia y costo y asignarlos a los inputs
                consultar_existencia( $('#inv_bodega_id').val(), $(this).attr('data-producto_id') );
            });


			/*
			** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
			*/
			$('#cantidad').keyup(function(event){

				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					cantidad = parseFloat( $(this).val() );

					if( $('#url_id_transaccion').val() == 40 ) 
					{ 
						// Si es una Nota Crédito Directa
						calcular_nuevo_saldo_a_la_fecha();
					}

					var codigo_tecla_presionada = event.which || event.keyCode;
					if( codigo_tecla_presionada == 13 )
					{
						/*$('#precio_unitario').removeAttr('disabled');
						$('#precio_unitario').attr('style','background-color:white;');*/
						$('#precio_unitario').select();
						return true;
						
					}

					// Si el costo_unitario del producto es cero (por algún motivo de la APP Inventarios, ejemplo al hacer ENSAMBLES)
					if ( costo_unitario == 0 || costo_unitario == "" ) 
					{
						costo_unitario = 0.0000001;
					}

					if ( !validar_existencia_actual() )
					{
						return false;
					}

					if ( $(this).val() != '')
					{
						calcular_valor_descuento();
						calcular_impuestos();
						calcular_precio_total();
					}

				}else{
					return false;
				}
			});


            // Al modificar el precio de compra
            $('#precio_unitario').keyup(function(event){
				
				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					precio_unitario = parseFloat( $(this).val() );

					var codigo_tecla_presionada = event.which || event.keyCode;
					if( codigo_tecla_presionada == 13 )
					{
						$('#tasa_descuento').select();			
					}
					
					calcular_valor_descuento();
					calcular_impuestos();
					calcular_precio_total();
				}else{
					$(this).select();
					return false;
				}

			});


            $('#tasa_descuento').keyup(function(){

            	if( validar_input_numerico( $(this) ) )
				{	
					tasa_descuento = parseFloat( $(this).val() );

					var codigo_tecla_presionada = event.which || event.keyCode;
					if( codigo_tecla_presionada == 13 )
					{
						//agregar_nueva_linea();
						
						$('#btn_agregar_nueva_linea').focus();
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

					$(this).select();
					return false;
				}
			});


			/*
			** Al eliminar una fila
			*/
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				fila.remove();

				calcular_totales();
				aplicar_retefuente( false );

				hay_productos--;
				$('#numero_lineas').text(hay_productos);

				if ( hay_productos == 0)
				{
					$('#proveedor_input').removeAttr('disabled');
					$('#fecha').removeAttr('disabled');
				}

				reset_linea_ingreso_default();

			});

			// GUARDAR EL FORMULARIO
			$('#btn_guardar').click(function(event){
				event.preventDefault();				

				if ( !validar_todo() )
				{
					return false;
				}

				var valor_total_recaudos = $('#total_valor_total').text();
				
				// Se reemplaza varias veces el "." por vacio, y luego la coma por punto
				var total_factura = $('#total_factura').text().replace(".","").replace(".","").replace(".","").replace(".","").replace(",",".");

				if( valor_total_recaudos !== '$0.00' && parseFloat( valor_total_recaudos.substring(1) ) !== parseFloat( total_factura.substring(2) )  )
				{
					alert('El total de recaudos no coincide con el total de la factura.');
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );
				
				$('#linea_ingreso_default').remove();

				if ( $('#tipo_transaccion').val() == 'factura_directa' ) 
				{

					// Se transfoma la tabla a formato JSON a través de un plugin JQuery
					var table = $('#ingreso_registros').tableToJSON();

			 		// No se puede enviar controles disabled
			 		$('#fecha').removeAttr('disabled');
				}else{

					var table = $('#tabla_registros_documento').tableToJSON();					
				}

				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_registros').val( JSON.stringify(table) );

		 		/*		Para Recaudos      */
		 		// Se transfoma la tabla a formato JSON a través de un plugin JQuery
				var tabla_recaudos = $('#ingreso_registros_medios_recaudo').tableToJSON();

				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_registros_medios_recaudo').val( JSON.stringify(tabla_recaudos) );
				

		 		// Se envía el formulario
				$('#form_create').submit();
			});

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

			// Al Activar/Inactivar modo de ingreso
		    $('#modo_ingreso').on('click',function(){

		    	if( $(this).val() == "true" ){
		    		$(this).val( "false" );
		    		setCookie("modo_ingreso_codigo_de_barra", "false", 365);
		    	}else{
		    		$(this).val( "true" );
		    		setCookie("modo_ingreso_codigo_de_barra", "true", 365);
		    	}
		    	
		    	$('#inv_producto_id').select();
		    });

			$("#btn_cerrar_alert").on('click', function(){
				$('#div_entradas_pendientes').hide();

				$('#listado_entradas_seleccionadas').hide();
				$('#tabla_registros_documento').find('tbody').append( '' );
				hay_productos = 0;

				$('#tipo_transaccion').val( 'factura_directa' );

				cambiar_action_form( 'compras' );
				
				$('#div_ingreso_registros').show( 500 );
				$('#doc_proveedor_consecutivo').focus();
			});


			$(document).on('click', '.btn_agregar_documento', function(event) 
			{
				event.preventDefault();

				$('#tipo_transaccion').val( 'factura_entrada_pendiente' );

				cambiar_action_form( 'factura_entrada_pendiente' );

				$('#listado_entradas_seleccionadas').show();
				$(this).hide();
				$('#tabla_registros_documento').find('tbody:last').append( $(this).closest("tr") );

				var total_factura = 0.0;
				$('#tabla_registros_documento tr').each(function()
				{
					var valor = $(this).find('td').eq(6).text().replace(" ","").replace("$","").replace(".","").replace(".","").replace(".","").replace(",",".");
					if ( valor != '' )
					{
					    total_factura += parseFloat( valor );
					    console.log( valor );						
					}
				});
				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_factura.toFixed(2) ) );

				hay_productos = 1;
			});

		});
	</script>
	<script type="text/javascript" src="{{ asset( 'assets/js/tesoreria/medios_recaudos.js?aux=' . uniqid() ) }}"></script>
	<script type="text/javascript" src="{{ asset( 'assets/js/compras/retefuente.js?aux=' . uniqid() ) }}"></script>
@endsection