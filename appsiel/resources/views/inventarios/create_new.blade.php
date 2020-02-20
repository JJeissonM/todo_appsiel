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
			{{ Form::open(['url'=>'web','id'=>'form_create']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux'])}}
				
			{{ Form::close() }}

			<br/>

		    {!! $tabla->dibujar() !!}
			
			<div class="right">
            	@if($id_transaccion==4)
            		<button type="button" class="btn btn-warning btn-xs" id="btn_calcular_costos_finales"><i class="fa fa-btn fa-calculator"></i> Calcular costos</button>
            	@else
            		&nbsp;
            	@endif
			</div>

			<!-- @ include('inventarios.incluir.ingreso_productos_2') -->
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			checkCookie();
			
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();

			if(dd<10) {
			    dd = '0'+dd
			} 

			if(mm<10) {
			    mm = '0'+mm
			} 

			today = yyyy + '-' + mm + '-' + dd;

			$('#fecha').val( today );

			/* INVENTARIOS*/
			var respuesta;
			// VARIABLES GLOBALES PAR ENSAMBLES
			var suma_costo_total_prod_salida = 0;
			var suma_cantidades_prod_entrada = 0;
			var celda_costo_unitario;
			var celda_costo_total;
			var costos_finales_correctos = false;

			$('#core_tipo_doc_app_id').change(function(){
				$('#fecha').focus();
			});

			$('#fecha').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#core_tercero_id').focus();				
				}		
			});

			$('#core_tercero_id').change(function(){
				$('#inv_bodega_id').focus();
			});

			// Al seleccionar una bodega, se ubica en el siguiente elemento
			$('#inv_bodega_id').change(function(){

				$('#inv_producto_id').focus();

				if( $('#id_transaccion').val()==2 ) 
				{ // Si es una transferencia
					$('#bodega_destino_id').focus();
				}

			});	

			$('#bodega_destino_id').change(function(){
				$('#inv_producto_id').focus();
			});


			/*
				DINÁMICA DEL INGRESO DE PRODUCTOS
				Se puede ingresar por ID, descripción o código de barras del producto
				Para ingreso por código de barras debe estar activada la casilla de verificación modo_ingreso

				INGRESO POR ID
				El sistema verifica si el texto ingresado es numérico y lo tomará como ID para la busqueda y muestra las sugerencias que coincidan de la forma: id LIKE texto_ingresado%. El sistema va marcando como activo al item de la lista que coincide con el ID exacto del texto ingresado. El producto se puede seleccionar presionando Enter.

				INGRESO POR DESCRIPCION
				Cuando el texto ingresado no es númerico, el sistema asume que se está buscando por descripción del producto y muestra las sugerencias que coincidan de la forma: descripcion LIKE %texto_ingresado%. Se debe hacer click en el item que se quiera seleccionar.

				INGRESO POR CÓDIGO DE BARRAS
				Cuando está activada la casilla de verificación modo_ingreso, el sistema asume que se usará lector de código de barras y busca en la base de datos de la forma: codigo_barras = texto_ingresado


			*/


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


		    // Al ingresar código, descripción o código de barras del producto
		    $('#inv_producto_id').on('keyup',function(){

		    	// Validar que se haya escogido una bodega
		    	if ($('#inv_bodega_id').val()=='') {
		    		alert('No ha seleccionado una bodega.');
		    		$(this).val('');
		    		$('#inv_bodega_id').focus();
		    		return false;
		    	}

		    	if( $('#modo_ingreso').is(':checked') )
		    	{
		    		// Manejo códigos de barra
		    		var campo_busqueda = 'codigo_barras';
		    	}else{

		    		// Si se presiona Enter, se selecciona el item activo
			    	var x = event.which || event.keyCode;
					if( x==13 ){
						var item = $('a.list-group-item.active');
						seleccionar_producto( item );
		                var hay = consultar_existencia( $('#inv_bodega_id').val(), item.attr('data-producto_id') );
		                if (!hay) { $('#inv_producto_id').select(); }
		                return false;				
					}

		    		// Manejo código de producto o nombre
		    		var campo_busqueda = 'descripcion';
		    		if( $.isNumeric( $(this).val() ) ){
			    		var campo_busqueda = 'id';
			    	}
		    	}

		    	var url = '../inv_consultar_productos';

				$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
					.done(function( data ) {
						//Escribimos las sugerencias que nos manda la consulta
		                $('#suggestions').show().html(data);
					});
		    });



            //Al hacer click en alguna de las sugerencias (escoger un producto)
            $(document).on('click','.list-group-item', function(){
                
            	seleccionar_producto( $(this) );

                // Consultar datos de existencia y costo y asignarlos a los inputs
                var hay = consultar_existencia( $('#inv_bodega_id').val(), $(this).attr('data-producto_id') );
                if (!hay) { $('#inv_producto_id').select(); }
            });


			/*
			** Al dejar el control del costo unitario, se valida lo ingresado, se inactiva el control
			** y se pasa a las cantidades
			*/
			$('#costo_unitario').keyup(function(event){
				
				calcula_costo_total();
				
				var x = event.which || event.keyCode;
				if( x==13 && validar_costo_unitario_y_cantidad() == true )
				{
					$('#cantidad').removeAttr('disabled');
					$('#cantidad').attr('style','background-color:white;');
					$('#cantidad').focus();			
				}

			});

			/*
			** Al digitar la cantidad, se valida la existencia actual, si es un movimiento de resta
			** luego se calcular el costo total
			*/
			$('#cantidad').keyup(function(event){

				// Para los movimientos de salida se valida la existencia actual
				var mov = $('#inv_motivo_id').val().split('-');

				if ( mov[1] == 'salida' ){
					if ( !validar_existencia_actual() )
					{
						return false;
					}
				}else{
					// Para que pueda pasar las validaciones, cuando el costo promedio del producto es 0
					if ( $('#costo_unitario').val() == 0 || $('#costo_unitario').val() == "" ) 
					{
						$('#costo_unitario').val( 0.0000001 );
					}
				}

				calcula_costo_total();

				var x = event.which || event.keyCode;
				if( x==13 && validar_costo_unitario_y_cantidad() == true)
				{
					agregar_nueva_linea();
				}
			});
			


            function seleccionar_producto(item_sugerencia)
            {

            	var fila = $('#linea_ingreso_default');

            	// Asignar ID del producto al campo oculto
                fila.find('.inv_producto_id').html( item_sugerencia.attr('data-producto_id') );

                // Asignar ID del motivo al campo oculto
                var mov = $('#inv_motivo_id').val().split('-');
				fila.find('.inv_motivo_id').html( mov[0] );

				// Asignar descripción del producto al TextInput
                $('#inv_producto_id').val( item_sugerencia.attr('data-descripcion') );
                //Hacemos desaparecer el resto de sugerencias
                $('#suggestions').hide();
            }

            // Asignar valores de existecia_actual y costo_unitario
            function consultar_existencia(bodega_id, producto_id)
            {
            	$('#div_cargando').show();
            	var url = '../inv_consultar_existencia_producto';

				$.get( url, { transaccion_id: $('#core_tipo_transaccion_id').val(), bodega_id: bodega_id, producto_id: producto_id } )
					.done(function( respuesta ) {

						$('#div_cargando').hide();
					
						// Se valida la existencia actual
						$('#existencia_actual').val(respuesta.existencia_actual);
						$('#tipo_producto').val(respuesta.tipo);

						$('#existencia_actual').attr('style','background-color:#97D897;'); // color verde

						if (respuesta.existencia_actual<=0)
						{
							$('#existencia_actual').attr('style','background-color:#FF8C8C;'); // color rojo
							
							var mov = $('#inv_motivo_id').val().split('-');
							// Si no es un motivo de entrada, no se permite seguir con existencia 0
							if ( mov[1] != 'entrada' && respuesta.tipo != 'servicio' ) 
							{
								return false;
							}
						}
						
						// Asignar datos a los controles
						$('#costo_unitario').val( parseFloat(respuesta.precio_compra).toFixed(2) );
						$('#linea_ingreso_default').find('costo_unitario').html( respuesta.precio_compra );
						//$('#unidad_medida1').val(respuesta.unidad_medida1);
						
						// Si la TRANSACCIÓN es una Entrada, se puede modificar el costo unitario			
						if ($('#core_tipo_transaccion_id').val()==1) {
							$('#costo_unitario').removeAttr('disabled');
							$('#costo_unitario').attr('style','background-color:white;');
						}

						// Se pasa a ingresar las cantidades
						$('#cantidad').removeAttr('disabled');
						$('#cantidad').attr('style','background-color:white;');
						$('#cantidad').focus();

						return true;
					});
            }

			/*
				validar_existencia_actual
			*/
			function validar_existencia_actual()
			{
				if ( $('#tipo_producto').val() == 'servicio') { return true; }

				if ( parseFloat( $('#cantidad').val() ) > parseFloat( $('#existencia_actual').val() ) ) 
				{
					alert('La Cantidad ingresada supera la existencia actual.');
					$('#cantidad').val('');
					$('#cantidad').focus();
					return false;
				}
				return true;
			}

			/*
			** Al presionar enter, luego de ingresar la cantidad y si se pasan la validaciones
			*/
			function agregar_nueva_linea()
			{
				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

				// Campos Visibles (formateados para el ususario)
				var nombre_producto = $( "#inv_producto_id option:selected" ).text();
				var motivo = $( "#inv_motivo_id option:selected" ).text();
				var existencia = $('#existencia_actual').val();
				var lbl_costo_unitario = $('#costo_unitario').val();
				var lbl_cantidad = $('#cantidad').val();
				var lbl_costo_total = $('#costo_total').val(); // ya está asignado y validado con la funcion calcula_costo_total
				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

				// Campos invisibles (números sin formatear)
				var motivo_id = fila.find('.inv_motivo_id').html();
				var bodega_id = $('#inv_bodega_id').val();
				var producto_id = fila.find('.inv_producto_id').html();
				var costo_unitario = fila.find('.costo_unitario').html();
				var cantidad = fila.find('.cantidad').html();
				var costo_total = fila.find('.costo_total').html();
				

				
				// Se crea una nueva fila con los datos ingresados
				// 1. Formatear los textos
				switch(mov[1]){
					case 'entrada':
						var estilo = 'style="color:green;"';
						if( costo_total == "" )
						{
							costo_total = 0.00000000001;
						}
						break;
					case 'salida':
						var estilo = 'style="color:red;"';
						break;
					default:
						break;
				}


				celda_costo_unitario = '<td>$'+parseFloat(costo_unitario).toFixed(2)+'</td>';
				celda_costo_total = '<td class="costo_total">$'+costo_total+'</td>';
				
				// Si la transaccion es ENSAMBLE, no se muestran los costos para los productos
				// finales (tipo entrada, movimiento suma)
				if ($('#id_transaccion').val()==4) {
					if (mov[1]=='entrada') {
						// para la entradas se suman las cantidades
						suma_cantidades_prod_entrada = suma_cantidades_prod_entrada + parseFloat(cantidad);
						// la class costo_unitario solo la tienen las celdas de los productos de entradas
						celda_costo_unitario = '<td class="costo_unitario">Sin calcular</td>';
						// se usa otra class costo_total2 porque esta es la que se va a rellenar cuando se
						// calcule el costo de los productos de entrada
						celda_costo_total = '<td class="costo_total2">Sin calcular</td>';
					}else{
						// para las salidas se suman los costos
						suma_costo_total_prod_salida = suma_costo_total_prod_salida + parseFloat(costo_total);
					}
				}

				$('#ingreso_productos').find('tbody:last').append('<tr id="'+producto.val()+'">'+
																'<td>'+producto.val()+'</td>'+
																'<td class="nom_prod">'+nombre_producto+'</td>'+
																'<td><span style="color:white;">'+mov[0]+'-</span><span '+estilo+'>'+motivo+'</span><input type="hidden" class="movimiento" value="'+mov[1]+'"></td>'+
																celda_costo_unitario+
																'<td class="cantidad">'+cantidad+" "+$('#unidad_medida1').val()+'</td>'+
																celda_costo_total+
																'<td>'+btn_borrar+'</td>'+
																'</tr>');
				
				// Se calculan los totales para la última fila
				calcular_totales();

				// Se retira el producto del select
				$("#inv_producto_id option[value='"+producto.val()+"']").remove();
				reset_linea_ingreso_default();
				$('#inv_producto_id').val('');

				// Se incrementa variable auxiliar para llevar control del ingreso 
				// de productos al agregar o elminiar
				var hay_productos = $('#hay_productos').val();
				hay_productos = parseFloat(hay_productos) + 1;
				$('#hay_productos').val(hay_productos);

				// se inactiva la bodega para que ya no se pueda cambiar, pues el motiviemto está amarrado a la bodega
				// 1ro. Se pasa el valor del id de la bodega (select) a un input hidden bodega
				$('#inv_bodega_id_aux').val($('#inv_bodega_id').val());
				// 2do. Intercambio los atributos de los campos, pues cuando está desabilitado, el campo name no es enviado
				$('#inv_bodega_id').attr('name','no_inv_bodega_id');
				$('#inv_bodega_id_aux').attr('name','inv_bodega_id');
				// 3ro. Desabilito el select
				$('#inv_bodega_id').attr('style','background-color:#ECECE5;');
				$('#inv_bodega_id').attr('disabled','disabled');

				// Se resetean los costos finales para obligar a que se calculen nuevamente
				if ($('#id_transaccion').val()==4) {
					$('.costo_unitario,.costo_total2').html('Sin Calcular');
					costos_finales_correctos = false;
				}
			}

			/*
			** Al eliminar una fila
			*/
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");

				// Se agrega nuevamente el producto al select
				var id_producto = fila.attr("id");
				var nombre_producto = fila.find("td.nom_prod").html();
				$('#inv_producto_id').append($('<option>', { value: id_producto, text: nombre_producto}));

				fila.remove();
				reset_linea_ingreso_default();
				$('#inv_producto_id').val('');

				// Se DECREMENTA variable auxiliar para llevar control del ingreso 
				// de prodcutos al agregar o elminiar
				var hay_productos = $('#hay_productos').val();
				hay_productos = parseFloat(hay_productos) - 1;
				$('#hay_productos').val(hay_productos);

				if (hay_productos==0) {
					// Se ACTIVA el select de la bodegas para que ya no se pueda cambiar, pues el motiviemto está amarrado a la bodega
					// 1ro. Se pasa el valor del id del select bodega a un input hidden bodega
					$('#inv_bodega_id_aux').val($('#inv_bodega_id').val());
					// 2do. Intercambio los traibutos de los campos, pues cuando está desabilitado, el campo name no es enviado
					$('#inv_bodega_id').attr('name','inv_bodega_id');
					$('#inv_bodega_id_aux').attr('name','inv_bodega_id_aux');
					// 3ro. Desabilito el select
					$('#inv_bodega_id').removeAttr('disabled');
					$('#inv_bodega_id').attr('style','background-color:white;');
				}


				if ($('#id_transaccion').val()==4) {
					var mov = fila.find("input.movimiento").val();
					if (mov=='entrada') {
						var cantidad = fila.find('td.cantidad').html();
						// Se elimina la cadena "UND" del texto de la cantidad
						var pos_espacio = cantidad.search(" ");
						cantidad = cantidad.substring(0,pos_espacio);
						suma_cantidades_prod_entrada = suma_cantidades_prod_entrada - parseFloat(cantidad);
						
					}else{
						var costo_total = fila.find('td.costo_total').html();
						// Se elimina el signo "$" del texto del costo
						costo_total = costo_total.substring(1);
						suma_costo_total_prod_salida = suma_costo_total_prod_salida - parseFloat(costo_total);
					}
					$('.costo_unitario,.costo_total2').html('Sin Calcular');
					costos_finales_correctos = false;
				}

				calcular_totales();
			});

			$('#btn_guardar').click(function(event){
				event.preventDefault();

				var object = $('#ingreso_productos').val();
				
				if( typeof object == typeof undefined){
					// Si no existe la tabla de ingreso_productos, se envía el formulario
					// Esto es para los otros modelos que usan el ModeloController y que no
					// son una transacción
					$('#form_create').submit();
				}else{
					var hay_productos = $('#hay_productos').val();
					if(hay_productos>0) {
						var table = $('#ingreso_productos').tableToJSON();
				 		$('#movimiento').val(JSON.stringify(table));
				 		var count = Object.values($('#movimiento')).length;
			  			var control = 1;
						$( "*[required]" ).each(function() {
							if ( $(this).val() == "" ) {
							  $(this).focus();
							  control = 0;
							  alert( $(this).attr('name') + ': Este campo es requerido.');
							  return false;
							}else{
							  control = 1;
							}
						});

						if (control==1) {

							// Si es un ENSAMBLE
							if ($('#id_transaccion').val()==4) {
								// se validan los costos finales
								if (costos_finales_correctos) {
									$('#form_create').submit();	
								}else{
									alert('NO se han calculado los costos finales.');
								}
							}else{
								$('#form_create').submit();	
							}
							
						}else{
							alert('Faltan campos por llenar.');
						}
			  			
					}else{
						alert('No ha ingresado productos.');
						$('#inv_producto_id').focus();
					}
				}
					
			});

			$('#btn_calcular_costos_finales').click(function(event){
				event.preventDefault();
				if (suma_cantidades_prod_entrada>0 && suma_costo_total_prod_salida>0) {
					costo_promedio = suma_costo_total_prod_salida / suma_cantidades_prod_entrada;
					$('.costo_unitario').text('$'+costo_promedio.toFixed(2));

					$('.costo_total2').each(function()
					{
						var costo_total = $(this); // celda costo_total
						var cant = costo_total.prev(); // celda cantidad
						var cost_unit = cant.prev(); // celda costo_unitario

						var cantidad = cant.text();
						// Se elimina la cadena "UND" del texto de la cantidad
						var pos_espacio = cantidad.search(" ");
						cantidad = cantidad.substring(0,pos_espacio);

						cost_unit = cost_unit.text();
						// Se elimina el signo "$" del texto del costo
						cost_unit = cost_unit.substring(1);
						
						valor_costo = parseFloat(cantidad) * parseFloat(cost_unit);
					    costo_total.text('$'+(valor_costo).toFixed(2));
					});
					costos_finales_correctos = true;
					calcular_totales();
				}else{
					costos_finales_correctos = false;
					alert('Datos incompletos para calcular el costo unitario. Recuerde que Costo unitario = costo_total_productos_salidas / cantidades_productos_entradas');
				}
			});

			function reset_linea_ingreso_default(){
				$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');
				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');
				$('#inv_producto_id').focus();
			}

			function calcula_costo_total()
			{
				if ( validar_costo_unitario_y_cantidad() !=true ) { return false; }

				var fila = $('#linea_ingreso_default');

				var costo_unitario = parseFloat( fila.find('.costo_unitario').html() );
				var cantidad = $('#cantidad').val();
				var costo_total = costo_unitario * cantidad;
				
				$('#costo_total').val('');
				fila.find('.costo_total').html('');

				if( $.isNumeric(costo_total) && costo_total > 0 )
				{
					$('#costo_total').val( costo_total.toFixed(2) ); // Se asigna el
					fila.find('.costo_total').html( costo_total );
				}

			}

			function calcular_totales(){
				var sum = 0.0;
				$('.cantidad').each(function()
				{
				    var cantidad = $(this).text();
					// Se elimina la cadena "UND" del texto de la cantidad
					var pos_espacio = cantidad.search(" ");
					cantidad = cantidad.substring(0,pos_espacio);
				    sum += parseFloat(cantidad);
				});
				var texto = sum.toFixed(2);
				$('#total_cantidad').text(texto);

				sum = 0.0;
				$('.costo_total,.costo_total2').each(function()
				{
				    var cadena = $(this).text();
				    sum += parseFloat(cadena.substring(1));
				});

				$('#total_costo_total').text(sum); // datos para almacenar
				$('#lbl_total_costo_total').text("$"+sum.toFixed(2));  // datos para visualizar
			}

			// Validar costo_unitario y cantidad
			function validar_costo_unitario_y_cantidad()
			{
				var costo_unitario = $('#costo_unitario').val();
				$('#costo_unitario').attr('style','background-color:white;');
				if( !$.isNumeric(costo_unitario) || costo_unitario<=0 )
				{
					$('#costo_unitario').attr('style','background-color:#FF8C8C;'); // Color rojo
					$('#costo_unitario').focus();
					return 'costo_unitario';
				}

				var cantidad = $('#cantidad').val();
				$('#cantidad').attr('style','background-color:white;');
				if( !$.isNumeric(cantidad) || cantidad <= 0)
				{
					$('#cantidad').attr('style','background-color:#FF8C8C;'); // Color rojo
					$('#cantidad').focus();
					return 'cantidad';
				}

				return true;
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