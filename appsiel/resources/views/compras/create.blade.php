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
		#proveedores_suggestions {
		    position: absolute;
		    z-index: 9999;
		}

		#existencia_actual, #tasa_impuesto{
			width: 35px;
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
  
				
				<div id="popup_alerta"> </div>

			{{ Form::close() }}

			<br/>

			@include('compras.incluir.elementos_entradas_pendientes')

			<br/>

			<div id="div_ingreso_registros">

			    {!! $tabla->dibujar() !!}

			    Productos ingresados: <span id="numero_lineas"> 0 </span>
				
				<div style="text-align: right;">
					<div id="total_cantidad" style="display: none;"> 0 </div>
	            	<table style="display: inline;">
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ 0 </div> </td>
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
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script type="text/javascript">

		var dias_plazo;

		$(document).ready(function(){

			checkCookie();


			$('#fecha').val( get_fecha_hoy() );

			$('#fecha_vencimiento').attr( 'readonly','readonly' );

			$('#proveedor_input').focus( );

			$("#proveedor_input").after('<div id="proveedores_suggestions"> </div>');

			var respuesta; // de consultar_existencia
			var hay_productos = 0;

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

		    	var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x == 27 ) // 27 = ESC
				{
					$('#proveedores_suggestions').html('');
                	$('#proveedores_suggestions').hide();
                	return false;
				}


				/*
					Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
			    */
				if ( x == 40) // Flecha hacia abajo
				{
					var item_activo = $("a.list-group-item.active");					
					item_activo.next().attr('class','list-group-item list-group-item-proveedor active');
					item_activo.attr('class','list-group-item list-group-item-proveedor');
					$('#proveedor_input').val( item_activo.next().html() );
					return false;

				}
	 			if ( x == 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-proveedor active');
					item_activo.attr('class','list-group-item list-group-item-proveedor');
					$('#proveedor_input').val( item_activo.prev().html() );
					return false;
				}

				// Al presionar Enter
				if( x == 13 )
				{
					var item = $('a.list-group-item.active');
					
					if( item.attr('data-proveedor_id') === undefined )
					{
						alert('El proveedor ingresado no existe.');
						reset_campos_formulario();
					}else{
						seleccionar_proveedor( item );
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

		    	var url = '../compras_consultar_proveedores';

				$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arooja la consulta
		                $('#proveedores_suggestions').show().html(data);
		                $('a.list-group-item.active').focus();
					});
		    });


		    //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-proveedor', function(){
            	seleccionar_proveedor( $(this) );
            	return false;
            });


			// Al seleccionar una bodega, se ubica en el siguiente elemento
			$('#inv_bodega_id').change(function(){

				$('#inv_producto_id').focus();

			});

			
			$('#doc_proveedor_prefijo').on('keyup',function(){
				var x = event.which || event.keyCode; // Capturar la tecla presionada
				// Al presionar Enter
				if( x == 13 )
				{
					$('#doc_proveedor_consecutivo').focus();
				}
				
			});
			
			$('#doc_proveedor_consecutivo').on('keyup',function(){

				$('#popup_alerta').hide();

				var x = event.which || event.keyCode; // Capturar la tecla presionada
				// Al presionar Enter
				if( x == 13 )
				{
					$('#inv_producto_id').focus();
				}
				
				validar_documento_proveedor();

			});

			var ya_esta;
			function validar_documento_proveedor()
			{
				url = '../compras_validar_documento_proveedor';
				$.get( url, { proveedor_id: $('#proveedor_id').val(), doc_proveedor_prefijo: $('#doc_proveedor_prefijo').val(), doc_proveedor_consecutivo: $('#doc_proveedor_consecutivo').val() } )
					.done(function( data ) {

						if( data == 'true' )
						{
							ya_esta = true;
							$('#doc_proveedor_consecutivo').focus();
							$('#popup_alerta').show();
							$('#popup_alerta').css('background-color','red');
							$('#popup_alerta').text( 'Ya existe una factura ingresada para el proveedor con ese prefijo y consecutivo: ' + $('#doc_proveedor_prefijo').val() + ' - ' + $('#doc_proveedor_consecutivo').val() );
						}else{
							ya_esta = false;
						}
					});

				return ya_esta;
			}


		    $('[data-toggle="tooltip"]').tooltip();
		    var terminar = 0; // Al presionar ESC dos veces, se posiciona en el botón guardar
		    
		    // Al ingresar código, descripción o código de barras del producto
		    $('#inv_producto_id').on('keyup',function(event){

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
			    */
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

				    

            //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item-productos', function(){
                
            	seleccionar_producto( $(this) );

                // Consultar datos de existencia y costo y asignarlos a los inputs
                consultar_existencia( $('#inv_bodega_id').val(), $(this).attr('data-producto_id') );
            });


            // Al modificar el precio de compra
            $('#precio_unitario').keyup(function(event){
				
				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{	

					var x = event.which || event.keyCode;
					if( x==13 )
					{
						// Se pasa a ingresar las cantidades
						$('#cantidad').removeAttr('disabled');
						$('#cantidad').attr('style','background-color:white;');
						$('#cantidad').focus();				
					}

					var tasa_impuesto = $('#linea_ingreso_default').find('.tasa_impuesto').html();

	            	// Los impuestos se obtienen del precio ingresado
					var precio_compra = parseFloat( $(this).val() );
		            
		            var base_impuesto = ( precio_compra ) / ( 1 + parseFloat(tasa_impuesto) / 100 );
		            var valor_impuesto =  precio_compra - base_impuesto;

					$('#linea_ingreso_default').find('.costo_unitario').html( precio_compra );
					$('#linea_ingreso_default').find('.base_impuesto').html( base_impuesto );
					
					$('#linea_ingreso_default').find('.valor_impuesto').html( valor_impuesto );
					$('#linea_ingreso_default').find('.precio_unitario').html( precio_compra );

		            // Si se ha liquidado impuestos en la transacción, el costo_unitario corresponde a base_impuesto
		            if ( tasa_impuesto > 0 )
		            {        
						$('#linea_ingreso_default').find('.costo_unitario').html( base_impuesto );
		            }

					// Asignar datos a los controles (formateados visualmente para el usuario)
					$('#tasa_impuesto').val( tasa_impuesto + '%' );

					calcula_precio_total();
				}else{

					$(this).focus();
					return false;
				}

			});


			/*
			** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
			*/
			$('#cantidad').keyup(function(event){

				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					if( $('#url_id_transaccion').val() == 40 ) 
					{ 
						// Si es una Nota Crédito Directa
						calcula_nuevo_saldo_a_la_fecha();
					}

					// El registro se agrega al presionas Enter, si pasa las validaciones
					var x = event.which || event.keyCode;
					if( x==13)
					{
						if( $('#url_id_transaccion').val() == 40 ) 
						{ 
							// Si es una Nota Crédito Directa
							validacion_saldo_movimientos_posteriores();
						}else{
							agregar_nueva_linea();
						}
						
						return true;
						
					}

					// Si el costo_unitario del producto es cero (por algún motivo de la APP Inventarios, ejemplo al hacer ENSAMBLES) 
					var costo_unitario = $('#linea_ingreso_default').find('.costo_unitario').html();
					if ( costo_unitario == 0 || costo_unitario == "" ) 
					{
						$('#linea_ingreso_default').find('.costo_unitario').html( 0.0000001 );
					}

					if ( !validar_existencia_actual() )
					{
						return false;
					}

					if ( $(this).val() != '')
					{
						calcula_precio_total();
					}

				}else{
					return false;
				}
			});
			

		    function seleccionar_proveedor(item_sugerencia)
            {

				// Asignar descripción al TextInput
                $('#proveedor_input').val( item_sugerencia.html() );
                $('#proveedor_input').css( 'background-color','white ' );

                // Asignar Campos ocultos
                $('#proveedor_id').val( item_sugerencia.attr('data-proveedor_id') );
                $('#clase_proveedor_id').val( item_sugerencia.attr('data-clase_proveedor_id') );
                $('#liquida_impuestos').val( item_sugerencia.attr('data-liquida_impuestos') );
                $('#core_tercero_id').val( item_sugerencia.attr('data-core_tercero_id') );

                // Asignar resto de campos
                $('#vendedor_id').val( item_sugerencia.attr('data-vendedor_id') );
                $('#inv_bodega_id').val( item_sugerencia.attr('data-inv_bodega_id') );


                var forma_pago = 'contado';
                dias_plazo = parseInt( item_sugerencia.attr('data-dias_plazo') );
                if ( dias_plazo > 0 ) { forma_pago = 'credito'; }
                $('#forma_pago').val( forma_pago );

                // Para llenar la fecha de vencimiento
                var fecha = new Date( $('#fecha').val() );
				
                $('#fecha_vencimiento').val( actualizar_fecha_vencimiento( fecha) );


                //Hacemos desaparecer el resto de sugerencias
                $('#proveedores_suggestions').html('');
                $('#proveedores_suggestions').hide();

                reset_tabla_ingreso();

                consultar_entradas_pendientes();

                $('#doc_proveedor_prefijo').focus();
            }

            // Recibe objeto Date()
            function actualizar_fecha_vencimiento( fecha )
            {

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

                return fecha.getFullYear() + '-' +  mes + '-' + dia;
            }

            function seleccionar_producto(item_sugerencia)
            {
            	reset_linea_ingreso_default();
            	var fila = $('#linea_ingreso_default');

            	// Asignar ID del producto al campo oculto
                fila.find('.inv_producto_id').html( item_sugerencia.attr('data-producto_id') );

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
            	var url = '../compras_consultar_existencia_producto';

				$.get( url, { transaccion_id: $('#core_tipo_transaccion_id').val(), liquida_impuestos: $('#liquida_impuestos').val(), bodega_id: bodega_id, producto_id: producto_id, fecha: $('#fecha').val(), proveedor_id: $('#proveedor_id').val() } )
					.done(function( respuesta ) {

						$('#div_cargando').hide();
					
						// Se valida la existencia actual
						$('#existencia_actual').val(respuesta.existencia_actual);
						$('#saldo_original').val( respuesta.existencia_actual );
						$('#tipo_producto').val(respuesta.tipo);

						$('#existencia_actual').attr('style','background-color:#97D897;'); // color verde

						if (respuesta.existencia_actual<=0)
						{
							$('#existencia_actual').attr('style','background-color:#FF8C8C;'); // color rojo
							
							var mov = $('#inv_motivo_id').val().split('-');
							// Si no es un motivo de entrada, no se permite seguir con existencia 0
							if ( mov[1] != 'entrada' && respuesta.tipo != 'servicio' ) 
							{	
								$('#inv_producto_id').select();
								return false;
							}
						}

						// Asignar datos a columnas invisibles (cantidades sin formatear)
						$('#linea_ingreso_default').find('.inv_bodega_id').html( $('#inv_bodega_id').val() );
						$('#linea_ingreso_default').find('.costo_unitario').html( respuesta.precio_compra );
						$('#linea_ingreso_default').find('.base_impuesto').html( respuesta.base_impuesto );
						$('#linea_ingreso_default').find('.tasa_impuesto').html( respuesta.tasa_impuesto );
						$('#linea_ingreso_default').find('.valor_impuesto').html( respuesta.valor_impuesto );
						$('#linea_ingreso_default').find('.precio_unitario').html( respuesta.precio_compra );

						// Asignar datos a los controles (formateados visualmente para el usuario)
						//var precio_compra = 
						$('#precio_unitario').val( respuesta.precio_compra  );
						$('#tasa_impuesto').val( respuesta.tasa_impuesto + '%' );

						// Se pasa a ingresar las cantidades
						$('#precio_unitario').removeAttr('disabled');
						$('#precio_unitario').attr('style','background-color:white;');
						$('#precio_unitario').select();

						return true;
					});
            }

			/*
				validar_existencia_actual
			*/
			function validar_existencia_actual()
			{
				// Si es una factura de compras o documento equivalente, no se valida la existencia
				if( $('#url_id_transaccion').val() == 25 || $('#url_id_transaccion').val() == 29 ) 
				{ 
					return true;
				}

				// Se valida para las notas crédito directas (salidas de inventarios)
				if ( $('#tipo_producto').val() == 'servicio') { return true; }

				if ( parseFloat( $('#existencia_actual').val() ) < 0 ) 
				{
					alert('Saldo negativo a la fecha.');
					$('#cantidad').val('');
					$('#cantidad').focus();
					return false;
				}/**/

				return true;
			}

			/*
			** Al presionar enter, luego de ingresar la cantidad y si se pasan la validaciones
			*/
			var numero_linea = 1;
			function agregar_nueva_linea()
			{
				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

				$('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="'+numero_linea+'"><td style="display: none;"><div class="inv_motivo_id">'+ fila.find('.inv_motivo_id').html() +'</div></td><td style="display: none;"><div class="inv_bodega_id">'+ fila.find('.inv_bodega_id').html() +'</div></td><td style="display: none;"><div class="inv_producto_id">'+ fila.find('.inv_producto_id').html() +'</div></td><td style="display: none;"><div class="costo_unitario">'+ fila.find('.costo_unitario').html() +'</div></td><td style="display: none;"><div class="precio_unitario">'+ fila.find('.precio_unitario').html() +'</div></td><td style="display: none;"><div class="base_impuesto">'+ fila.find('.base_impuesto').html() +'</div></td><td style="display: none;"><div class="tasa_impuesto">'+ fila.find('.tasa_impuesto').html() +'</div></td><td style="display: none;"><div class="valor_impuesto">'+ fila.find('.valor_impuesto').html() +'</div></td><td style="display: none;"><div class="cantidad">'+ $('#cantidad').val() +'</div></td><td style="display: none;"><div class="costo_total">'+ fila.find('.costo_total').html() +'</div></td><td style="display: none;"><div class="precio_total">'+ fila.find('.precio_total').html() +'</div></td><td> &nbsp; </td><td> <span style="background-color:#F7B2A3;">'+ fila.find('.inv_producto_id').html() + "</span> " + $('#inv_producto_id').val() + '</td> <td>'+ $('#inv_motivo_id option:selected').text() + '</td><td> '+ $('#existencia_actual').val() + '</td><td> '+ $('#precio_unitario').val() + '</td> <td>  '+ $('#tasa_impuesto').val() + ' </td> <td> '+ $('#cantidad').val() + ' </td> <td> '+ $('#precio_total').val() + ' </td><td>' + btn_borrar + '</td></tr>');
				
				// Se calculan los totales
				calcular_totales();

				hay_productos++;
				$('#numero_lineas').text(hay_productos);

				$('#proveedor_input').attr('disabled','disabled');
				$('#fecha').attr('disabled','disabled');

				// Bajar el Scroll hasta el final de la página
				$("html, body").animate( { scrollTop: $(document).height()+"px"} );
				
				reset_linea_ingreso_default();

				// !!    WARNING   !!!!!!!!!!!!!!!! Se DEBE INACTIVAR la bodega para que ya no se pueda cambiar, pues el motiviemto está amarrado a la bodega

				numero_linea++;
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

				var object = $('#ingreso_registros').val();
				
				if( typeof object == typeof undefined){
					// Si no existe la tabla de ingreso_registros, se envía el formulario
					// Esto es para los otros modelos que usan el ModeloController y que no
					// son una transacción
					$('#form_create').submit();
				}

				if ( !validar_todo() )
				{
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

		 		// Se envía el formulario
				$('#form_create').submit();
					
			});

			function validar_todo()
			{
				if ( validar_documento_proveedor() ) { return false; }
				
				if ( !validar_requeridos() ) { return false; }

				if( hay_productos == 0) 
				{
					alert('No ha ingresado productos.');
					reset_linea_ingreso_default();
					return false;		  			
				}

				return true;
			}

			var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
			function validar_requeridos()
			{
				control_requeridos = true;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" )
					{
					  $(this).focus();
					  alert( 'Este campo es requerido: ' + $(this).attr('name') );
					  control_requeridos = false;
					  return false;
					}
				});

				return control_requeridos;
			}

			function reset_campos_formulario()
			{
				$('#proveedor_id').val( '' );
				$('#proveedor_input').css( 'background-color','#FF8C8C' );
                $('#vendedor_id').val( '' );
                $('#inv_bodega_id').val( '' );
                $('#forma_pago').val( 'contado' );
				$('#fecha_vencimiento').val( '' );
                $('#lista_precios_id').val( '' );
                $('#lista_descuentos_id').val( '' );
                $('#liquida_impuestos').val( '' );
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

				// Total impuestos (Sumatoria de valor_impuesto por cantidad)
				$('#total_impuestos').text( '$ 0' );

				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ 0' );


				reset_linea_ingreso_default()
			}


			function reset_linea_ingreso_default()
			{
				$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');


				$('#inv_motivo_id').attr('style','background-color:#ECECE5;');
				$('#inv_motivo_id').attr('disabled','disabled');


				$('#precio_unitario').removeAttr('style');
				$('#precio_unitario').removeAttr('disabled');

				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');
				$('#inv_producto_id').focus();
				$("[data-toggle='tooltip']").tooltip('show');
			}

			function calcula_precio_total()
			{
				var fila = $('#linea_ingreso_default');
				
				var cantidad = $('#cantidad').val();

				if( $.isNumeric(cantidad) && cantidad > 0 )
				{
				
					var base_impuesto = parseFloat( fila.find('.base_impuesto').html() );
					var base_impuesto_total = base_impuesto * cantidad;

					var precio_unitario = parseFloat( fila.find('.precio_unitario').html() );
					var precio_total = precio_unitario * cantidad;

					var costo_unitario = parseFloat( fila.find('.costo_unitario').html() );
					var costo_total = costo_unitario * cantidad;
					
					$('#precio_total').val('');
					fila.find('.precio_total').html('');
					
					$('#costo_total').val('');
					fila.find('.costo_total').html('');
					
					$('#base_impuesto_total').val('');
					fila.find('.base_impuesto_total').html('');

					if( $.isNumeric(precio_total) && precio_total > 0 )
					{
						$('#precio_total').val( '$ ' + new Intl.NumberFormat("de-DE").format( precio_total )  );
						fila.find('.precio_total').html( precio_total );

						fila.find('.base_impuesto_total').html( base_impuesto_total );
						
						fila.find('.costo_total').html( costo_total );
					}else{
						//alert('Error en precio, por favor verifique.');
						$('#cantidad').select();
					}
				}
			}

			function calcular_totales()
			{	
				var cantidad = 0.0;
				var subtotal = 0.0;
				var total_impuestos = 0.0;
				var total_factura = 0.0;
				$('.linea_registro').each(function()
				{
				    cantidad += parseFloat( $(this).find('.cantidad').text() );
				    subtotal += parseFloat( $(this).find('.base_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
				    total_impuestos += parseFloat( $(this).find('.valor_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
				    total_factura += parseFloat( $(this).find('.precio_total').text() );

				});
				$('#total_cantidad').text( new Intl.NumberFormat("de-DE").format( cantidad ) );

				// Subtotal (Sumatoria de base_impuestos por cantidad)
				//var valor = ;
				$('#subtotal').text( '$ ' + new Intl.NumberFormat("de-DE").format( subtotal.toFixed(2) )  );

				// Total impuestos (Sumatoria de valor_impuesto por cantidad)
				$('#total_impuestos').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_impuestos.toFixed(2) ) );

				// Total factura  (Sumatoria de precio_total)
				$('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_factura.toFixed(2) ) );
				
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

			function consultar_entradas_pendientes()
			{
				$('#div_entradas_pendientes').hide();
				url = '../compras_consultar_entradas_pendientes';
				$.get( url, { core_tercero_id: $('#core_tercero_id').val() } )
					.done(function( data ) {
						if ( data != 'sin_registros')
						{
							$('#div_entradas_pendientes').show( 500 );
							$('#listado_entradas_pendientes').html( data );
							$('.td_boton').show();
		                	$('.btn_agregar_documento').show();
		                	$('#div_ingreso_registros').hide();
						}else{
							$('#div_ingreso_registros').show( 500 );
						}
						return false;
					});/**/
			}

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
				hay_productos = 1;
			});
			
			function cambiar_action_form( nueva_accion )
			{
				var accion = $('#form_create').attr('action');
				var n = accion.search('compras');

				if( n === -1 )
				{
					// No está la palabra compras
					n = accion.search('factura_entrada_pendiente');
					$('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
				}else{
					$('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
				}
			}


			// Para las notas crédito directas (salida de inventario)
			function calcula_nuevo_saldo_a_la_fecha()
			{
				// 0 es la cantidad_original
				var nuevo_saldo = parseFloat( $('#saldo_original').val() ) + 0 - parseFloat( $('#cantidad').val() );

				$('#existencia_actual').val( nuevo_saldo );
			}
            
            function validacion_saldo_movimientos_posteriores( )
            {

            	// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

                var url = '../inv_validacion_saldo_movimientos_posteriores/' + $('#inv_bodega_id').val() + '/' + fila.find('.inv_producto_id').html() + '/' + $('#fecha').val() + '/' + $('#cantidad').val() + '/' + $('#existencia_actual').val() + '/salida';

                $.get( url )
                    .done( function( data ) {
                        if ( data != 0 )
                        {
                            $('#popup_alerta_danger').show();
                            $('#popup_alerta_danger').text( data );
                        }else{
							agregar_nueva_linea();
                            $('#popup_alerta_danger').hide();
                        }
                    });
            }
			

		});
	</script>
@endsection