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
			width: 35px;
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
			{{ Form::open([ 'url' => $form_create['url'],'id'=>'form_create']) }}
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
				{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="cliente_id" id="cliente_id" value="" required="required">
				<input type="hidden" name="zona_id" id="zona_id" value="" required="required">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="" required="required">
				<input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="" required="required">


				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="" required="required">
				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="" required="required">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="" required="required">
				<input type="hidden" name="lineas_registros" id="lineas_registros" value="">
				<input type="hidden" name="remision_doc_encabezado_id" value="">
				
			{{ Form::close() }}

			<br/>
				

		    {!! $tabla->dibujar() !!}


			Productos ingresados: <span id="numero_lineas"> 0 </span>
			
			<div style="text-align: right;">
				<div id="total_cantidad" style="display: none;"> 0 </div>
            	<table style="display: inline;">
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Descuento: &nbsp; </td> <td> <div id="descuento"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> <td> <div id="total_impuestos"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> <td> <div id="total_factura"> $ 0 </div> </td>
            		</tr>
            	</table>
			</div>
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script type="text/javascript">

		var producto_id, precio_total, costo_total, base_impuesto_total, valor_impuesto_total, tasa_impuesto, tasa_descuento, valor_total_descuento, cantidad, inv_producto_id, inv_bodega_id, inv_motivo_id;
		var costo_unitario = 0;
		var precio_unitario = 0;
		var base_impuesto_unitario = 0;
		var valor_impuesto_unitario = 0;
		var valor_unitario_descuento = 0;

		var hay_productos = 0;

		$(document).ready(function(){

			checkCookie();
			
			$('#fecha').val( get_fecha_hoy() );

			$('#cliente_input').focus( );

			/* INVENTARIOS*/
			var respuesta;
			var hay_productos = 0;

			$('#core_tipo_doc_app_id').change(function(){
				$('#fecha').focus();
			});


		    $('#cliente_input').on('focus',function(){
		    	$(this).select();
		    });

			$("#cliente_input").after('<div id="clientes_suggestions"> </div>');

			// Al ingresar código, descripción o código de barras del producto
		    $('#cliente_input').on('keyup',function(){

		    	reset_campos_formulario();

		    	var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x == 27 ) // 27 = ESC
				{
					$('#clientes_suggestions').html('');
                	$('#clientes_suggestions').hide();
                	return false;
				}


				/*
					Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
			    */
				if ( x == 40) // Flecha hacia abajo
				{
					var item_activo = $("a.list-group-item.active");					
					item_activo.next().attr('class','list-group-item list-group-item-cliente active');
					item_activo.attr('class','list-group-item list-group-item-cliente');
					$('#cliente_input').val( item_activo.next().html() );
					return false;

				}
	 			if ( x == 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-cliente active');
					item_activo.attr('class','list-group-item list-group-item-cliente');
					$('#cliente_input').val( item_activo.prev().html() );
					return false;
				}

				// Al presionar Enter
				if( x == 13 )
				{
					var item = $('a.list-group-item.active');
					
					if( item.attr('data-cliente_id') === undefined )
					{
						alert('El cliente ingresado no existe.');
						reset_campos_formulario();
					}else{
						seleccionar_cliente( item );
	                	return false;
					}
				}

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
						// Se llena el DIV con las sugerencias que arroja la consulta
		                $('#clientes_suggestions').show().html(data);
		                $('a.list-group-item.active').focus();
					});
		    });

		    //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-cliente', function(){
            	seleccionar_cliente( $(this) );
            	return false;
            });


			// Al seleccionar una bodega, se ubica en el siguiente elemento
			$('#inv_bodega_id').change(function(){

				$('#inv_producto_id').focus();

				if( $('#id_transaccion').val()==2 ) 
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
		    /*$('#inv_producto_id').on('keyup',function(event){

		    	$("[data-toggle='tooltip']").tooltip('hide');

		    	if ( validar_requeridos() == false )
				{
					return false;
				}

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x == 27 ) // 27 = ESC
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


				/*
					Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
			    
				if ( x == 40) // Flecha hacia abajo
				{
					var item_activo = $("a.list-group-item.active");					
					item_activo.next().attr('class','list-group-item list-group-item-productos active');
					item_activo.attr('class','list-group-item list-group-item-productos');
					$('#inv_producto_id').val( item_activo.html() );
					return false;

				}
	 			if ( x == 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-productos active');
					item_activo.attr('class','list-group-item list-group-item-productos');
					$('#inv_producto_id').val( item_activo.html() );
					return false;
				}

				
		    	if( $('#modo_ingreso').is(':checked') )
		    	{
		    		// Manejo códigos de barra
		    		var campo_busqueda = 'codigo_barras'; // Busqueda por CÓDIGO DE BARRA
		    	}else{

		    		// Se determina el campo de busqueda
		    		
		    		
		    		if( $.isNumeric( $(this).val() ) ){
			    		var campo_busqueda = 'id'; // Busqueda por CODIGO (ID en base de datos)
			    	}else{
			    		var campo_busqueda = 'descripcion'; // Busqueda por NOMBRE

			    		// Si la longitud es menor a tres, todavía no busca
			    		if ( $(this).val().length < 2 ) { return false; }
			    	}

		    		// Si el campo_busqueda es ID y el texto_busqueda coincide con el ID exacto del producto, en el listado de sugerencias ya viene marcado como Active el producto de la lista 
		    		
		    		// Cuando se ingresa el ID, se selecciona el item activo cuando se presiona Enter 
			    	
					if( x == 13 ) // && $.isNumeric( $(this).val() )
					{
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
					}
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

		    });
		    */

		    $('#inv_producto_id').on('keyup',function(event){

		    	$("[data-toggle='tooltip']").tooltip('hide');

		    	if ( validar_requeridos() == false )
				{
					return false;
				}

				var codigo_tecla_presionada = event.which || event.keyCode;

				switch( codigo_tecla_presionada )
		    	{
		    		case 27: // 27 = ESC
						terminar++;
						$('#suggestions').html('');
		            	$('#suggestions').hide();

		            	if ( terminar == 2 )
		            	{ 
		            		terminar = 0;
		            		$('#btn_guardar').focus(); 
		            	}
		    			break;

		    		case 40: // Flecha hacia abajo

						var item_activo = $("a.list-group-item.active");

						// Si es el útimo item, entonces no se mueve hacia abajo
						if( item_activo.attr('data-ultimo_item') == 1 )
						{
							return false;
						}

						item_activo.next().attr('class','list-group-item list-group-item-productos active');
						item_activo.attr('class','list-group-item list-group-item-productos');
						//$('#inv_producto_id').val( item_activo.html() );
		    			break;

		    		case 38: // Flecha hacia arriba
						$(".flecha_mover:focus").prev().focus();
						var item_activo = $("a.list-group-item.active");

						// Si es el primer item, entonces no se mueve hacia arriba
						if( item_activo.attr('data-primer_item') == 1 )
						{
							return false;
						}
											
						item_activo.prev().attr('class','list-group-item list-group-item-productos active');
						item_activo.attr('class','list-group-item list-group-item-productos');
						//$('#inv_producto_id').val( item_activo.html() );
		    			break;

		    		case 13: // Al presionar Enter

		    			if ( $(this).val() == '' )
						{
							return false;
						}

		    			// Si el campo_busqueda es ID y el texto_busqueda coincide con el ID exacto del producto, en el listado de sugerencias ya viene marcado como Active el producto de la lista 
		    		
		    			// Cuando se ingresa el ID, se selecciona el item activo cuando se presiona Enter 

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
			    		// Se determina el campo de busqueda
			    		if( $.isNumeric( $(this).val() ) )
			    		{
			    			if( $('#modo_ingreso').is(':checked') )
					    	{
					    		// Manejo códigos de barra
					    		var campo_busqueda = 'codigo_barras'; // Busqueda por CÓDIGO DE BARRA
					    	}else{
					    		var campo_busqueda = 'id'; // Busqueda por CODIGO (ID en base de datos)
					    	}
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
			var ir_al_precio_total = 0;
			$('#cantidad').keyup(function(event){
				
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					cantidad = parseFloat( $(this).val() );

					if( codigo_tecla_presionada == 13)
					{
						$('#precio_unitario').select();

						return true;					
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
						$('#tasa_descuento').select();			
					}

				}else{

					$(this).select();
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

					$(this).select();
					return false;
				}
			});

			$('#valor_unitario_descuento').keyup(function(){

				var codigo_tecla_presionada = event.which || event.keyCode;
				if( codigo_tecla_presionada == 13 )
				{
					agregar_nueva_linea();
					return true;
				}
			});

			$('#precio_total').keyup(function(){

				var codigo_tecla_presionada = event.which || event.keyCode;
				if( codigo_tecla_presionada == 13 )
				{
					agregar_nueva_linea();
					return true;
				}
			});


			function calcular_valor_descuento()
			{
				// El descuento se calcula cuando el precio tiene el IVA incluido
				valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
				$('#valor_unitario_descuento').val( valor_unitario_descuento );

				valor_total_descuento = valor_unitario_descuento * cantidad;
				$('#valor_total_descuento').val( valor_total_descuento );
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
                $('#equipo_ventas_id').val( item_sugerencia.attr('data-equipo_ventas_id') );
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
                console.log( fecha.getFullYear() + '-' +  mes + '-' + dia );

                $('#fecha_vencimiento').val( fecha.getFullYear() + '-' +  mes + '-' + dia );


                //Hacemos desaparecer el resto de sugerencias
                $('#clientes_suggestions').html('');
                $('#clientes_suggestions').hide();

                reset_tabla_ingreso();

                // Cargar contactos asociados al cliente
				$('#contacto_cliente_id').html('<option value=""></option>');
		    	$('#div_cargando').show();
				
                var url = url_raiz + "/get_opciones_select_contactos/" + $('#cliente_id').val();

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){
		        		$('#div_cargando').hide();	    				
	    				$('#contacto_cliente_id').html( datos );
						$('#contacto_cliente_id').focus();

						// Bajar el Scroll hasta el final de la página
						//$("html, body").animate( { scrollTop: $(document).height()+"px"} );
			        }
			    });
            }

            
            function seleccionar_producto(item_sugerencia)
            {
            	reset_linea_ingreso_default();
            	var fila = $('#linea_ingreso_default');

            	// Asignar ID del producto al campo oculto
            	inv_producto_id = item_sugerencia.attr('data-producto_id');

                // Asignar ID del motivo al campo oculto
                var mov = $('#inv_motivo_id').val().split('-');
				fila.find('.inv_motivo_id').html( mov[0] );

				// Asignar descripción del producto al TextInput
                $('#inv_producto_id').val( item_sugerencia.html() );
                //Hacemos desaparecer el resto de sugerencias
                $('#suggestions').html('');
                $('#suggestions').hide();
            }

            // Asignar valores de existecia_actual y costo_unitario
            function consultar_existencia(bodega_id, producto_id)
            {
            	$('#div_cargando').show();
            	var url = '../vtas_consultar_existencia_producto';

				$.get( url, { transaccion_id: $('#core_tipo_transaccion_id').val(), bodega_id: bodega_id, producto_id: producto_id, fecha: $('#fecha').val(), lista_precios_id: $('#lista_precios_id').val(), lista_descuentos_id: $('#lista_descuentos_id').val(), cliente_id: $('#cliente_id').val() } )
					.done(function( respuesta ) {

						$('#div_cargando').hide();
					
						// Se valida la existencia actual
						$('#existencia_actual').val(respuesta.existencia_actual);
						$('#saldo_original').val(respuesta.existencia_actual);
						$('#tipo_producto').val(respuesta.tipo);

						$('#existencia_actual').attr('style','background-color:#97D897;'); // color verde

						if ( $("#permitir_inventarios_negativos").val() == 0 )
						{
							if (respuesta.existencia_actual<=0)
							{
								$('#existencia_actual').attr('style','background-color:#FF8C8C;'); // color rojo
								
								// Si no es un motivo de entrada, no se permite seguir con existencia 0
								
								var mov = $('#inv_motivo_id').val().split('-');
								
								if ( mov[1] != 'entrada' && respuesta.tipo != 'servicio' ) 
								{	
									$('#inv_producto_id').select();
									return false;
								}
								/**/
							}
						}

						costo_unitario = respuesta.costo_promedio;

						tasa_impuesto = respuesta.tasa_impuesto;
						precio_unitario = respuesta.precio_venta;

						//asignar_valores_campos_invisibles_linea_registro(); // ( valores sin formato )


						// Asignar datos a los controles (formateados visualmente para el usuario)
						$('#precio_unitario').val(  respuesta.precio_venta );
						$('#tasa_impuesto').val( respuesta.tasa_impuesto + '%' );

						// Se pasa a ingresar las cantidades
						$('#cantidad').removeAttr('disabled');
						$('#cantidad').attr('style','background-color:white;');
						$('#cantidad').select();

						if ( (respuesta.tipo != 'servicio') && (respuesta.costo_promedio == 0) )
						{
							alert('Advertencia! El producto no tiene costo en inventarios, esto puede afectar la contabilidad. Por favor comuníquese con el área de contabilidad.');
						}
							

						return true;
					});
            }

			/*
				validar_existencia_actual
			*/
			function validar_existencia_actual()
			{
				// TODO COMENTADO: SE PERMITEN NEGATIVOS
				/*if ( $('#tipo_producto').val() == 'servicio') { return true; }

				if ( parseFloat( $('#cantidad').val() ) > parseFloat( $('#existencia_actual').val() ) ) 
				{
					alert('La Cantidad ingresada supera la existencia actual.');
					$('#cantidad').val('');
					$('#cantidad').focus();
					return false;
				}*/
				return true;
			}

			/*
			** Al presionar enter, luego de ingresar la cantidad y si se pasan la validaciones
			*/
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

				$('#popup_alerta').hide();
				agregar_la_linea();
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

				numero_linea++;
			}



			// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
			// Debe ser complatible con las columnas de la tabla de ingreso de registros
			function generar_string_celdas( fila )
			{
				var celdas = [];
				var num_celda = 0;
				
				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_motivo_id">'+ fila.find('.inv_motivo_id').html() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_bodega_id">'+ $('#inv_bodega_id').val() +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="inv_producto_id">'+ inv_producto_id +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="costo_unitario">'+ costo_unitario +'</div></td>';
				
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

				celdas[ num_celda ] = '<td style="display: none;"><div class="costo_total">'+ costo_unitario * cantidad +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="precio_total">'+ precio_total +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_descuento">'+ tasa_descuento +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"><div class="valor_total_descuento">'+ valor_total_descuento +'</div></td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> &nbsp; </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> <span style="background-color:#F7B2A3;">'+ inv_producto_id + "</span> " + $('#inv_producto_id').val() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ $('#inv_motivo_id option:selected').text() + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> '+ new Intl.NumberFormat("de-DE").format( $('#existencia_actual').val() ) + '</td>';
				
				num_celda++;

				/*var celda_debito = ;
		        var celda_credito = ;
		        var celda_detalle = ;*/

				celdas[ num_celda ] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + cantidad + '</div> </div> </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> '+ '$ <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + precio_unitario + '</div> </div> </td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + tasa_descuento + '</div> </div> % </td>';
				
				num_celda++;
				// ¿se va  amostrar valor del descuento?
				celdas[ num_celda ] = '<td> '+ '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_descuento ) + '</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td>'+ $('#tasa_impuesto').val() + '</td>';
				
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
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;	
				}

				if( hay_productos == 0) 
				{
					alert('No ha ingresado productos.');
					reset_linea_ingreso_default();
					return false;		  			
				}				

				// Desactivar el click del botón
				$( this ).off( event );

				$('#linea_ingreso_default').remove();

				var table = $('#ingreso_registros').tableToJSON();

				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_registros').val(JSON.stringify(table));
				
			 	// No se puede enviar controles disabled
				habilitar_campos_encabezado();

		 		// Enviar formulario
				$('#form_create').submit();					
			});

			
			function reset_campos_formulario()
			{
				$('#cliente_id').val( '' );
				$('#cliente_input').css( 'background-color','#FF8C8C' );
                $('#vendedor_id').val( '' );
                $('#inv_bodega_id').val( '' );
                $('#forma_pago').val( 'contado' );
				//$('#fecha_vencimiento').val( '' );
                $('#lista_precios_id').val( '' );
                $('#lista_descuentos_id').val( '' );
                $('#liquida_impuestos').val( '' );

                $('#equipo_ventas_id').val( '' );
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


				reset_linea_ingreso_default()
			}


			function reset_linea_ingreso_default()
			{
				$('#linea_ingreso_default input[type="text"]').val(0);
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');


				$('#inv_motivo_id').attr('style','background-color:#ECECE5;');
				$('#inv_motivo_id').attr('disabled','disabled');

				$('#precio_unitario').removeAttr('style');
				$('#precio_unitario').removeAttr('disabled');

				$('#tasa_descuento').removeAttr('style');
				$('#tasa_descuento').removeAttr('disabled');

				$('#valor_total_descuento').removeAttr('style');
				$('#valor_total_descuento').removeAttr('disabled');

				$('#valor_unitario_descuento').removeAttr('style');
				$('#valor_unitario_descuento').removeAttr('disabled');

				$('#precio_total').removeAttr('style');
				$('#precio_total').removeAttr('disabled');

				$('#inv_producto_id').val('');
				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');
				$('#inv_producto_id').select();
				$("[data-toggle='tooltip']").tooltip('show');

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
			$(document).on('blur','#valor_nuevo',function(){
				guardar_valor_nuevo();
			});/**/

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
		        	guardar_valor_nuevo();
				}
			});

			function guardar_valor_nuevo()
			{
				var valor_nuevo = document.getElementById('valor_nuevo').value;

				// Si no cambió el valor_nuevo, no pasa nada
				if ( valor_nuevo == valor_actual)
				{
					elemento_padre.find('#valor_nuevo').remove();
		        	elemento_modificar.show();
		        	return false;
				}

				elemento_modificar.html( valor_nuevo );
				elemento_modificar.show();

				recalcular_linea_editada( elemento_padre.closest('tr') );
				calcular_totales();

				elemento_padre.find('#valor_nuevo').remove();
				
			}

			function recalcular_linea_editada( fila )
			{
				var elemento_modificar = fila.find('.elemento_modificar'); // son tres

				var cantidad = parseFloat( elemento_modificar[0].innerHTML );
				var precio_unitario = parseFloat( elemento_modificar[1].innerHTML );
				var tasa_descuento = parseFloat( elemento_modificar[2].innerHTML );

				var tasa_impuesto = parseFloat( fila.find('.tasa_impuesto').html() );

				var descuento_unitario = precio_unitario * ( tasa_descuento / 100 );

				var base_impuesto_unitario = ( precio_unitario - descuento_unitario )  / ( 1 + tasa_impuesto / 100 );

				console.log( base_impuesto_unitario, precio_unitario, descuento_unitario, tasa_impuesto );
				
				var precio_total = (precio_unitario - descuento_unitario) * cantidad;

				fila.find('.cantidad').html( cantidad );
				fila.find('.precio_unitario').html( precio_unitario );
				fila.find('.tasa_descuento').html( tasa_descuento );

				fila.find('.base_impuesto').html( base_impuesto_unitario ); // unitario
				fila.find('.base_impuesto_total').html( base_impuesto_unitario * cantidad );
				fila.find('.valor_impuesto').html( base_impuesto_unitario * tasa_impuesto / 100 ); // unitario
				fila.find('.precio_total').html( precio_total );

				fila.find('.valor_total_descuento').html( descuento_unitario * cantidad );

				var celdas = fila.find('td');
				celdas[21].innerHTML = '$ ' + new Intl.NumberFormat("de-DE").format( descuento_unitario * cantidad );
				celdas[23].innerHTML = '$ ' + new Intl.NumberFormat("de-DE").format( precio_total );
			}


		});
	</script>

	<script src="{{ asset( 'assets/js/ventas/cotizaciones.js' ) }}"></script>

@endsection