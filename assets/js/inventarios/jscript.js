$(document).ready(function(){
	
			/* INVENTARIOS*/
			var respuesta;
			// VARIABLES GLOBALES PAR ENSAMBLES
			var suma_costo_total_prod_salida = 0;
			var suma_cantidades_prod_entrada = 0;
			var celda_costo_unitario;
			var celda_costo_total;
			var costos_finales_correctos = false;

			var LineaNum;

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

			// Al seleccionar una bodega, se muestra el ingreso de productos
			$('#inv_bodega_id').change(function(){
				if ($('#inv_bodega_id').val()!='') {
					$('#btn_nueva_linea').show();
					if ($('#id_transaccion').val()==2) { // Si es una transferencia
						$('#bodega_destino_id').focus();
					}else{
						$('#btn_nueva_linea').focus();
					}
				}else{
					$('#btn_nueva_linea').hide();
				}
			});	

			$('#bodega_destino_id').change(function(){
				$('#btn_nueva_linea').focus();
			});

			/*
			**	Abrir formulario de productos
			*/
			$("#btn_nueva_linea").click(function(event){
				event.preventDefault();
				nueva_linea_ingreso_datos();
		    });

		    function nueva_linea_ingreso_datos(){
		    	LineaNum ++;
		    	var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
		        var btn_confirmar = "<button type='button' class='btn btn-success btn-xs btn_confirmar'><i class='glyphicon glyphicon-ok'></i></button>";

		        $('#ingreso_registros').find('tbody:last').append('<tr>'+
																	'<td><span style="color:white;">'+texto_medio_recaudo[0]+'-</span><span>'+texto_medio_recaudo[1]+'</span></td>'+
																	'<td><span style="color:white;">'+texto_motivo[0]+'-</span><span>'+texto_motivo[1]+'</span></td>'+
																	'<td><span style="color:white;">'+texto_caja[0]+'-</span><span>'+texto_caja[1]+'</span></td>'+
																	'<td><span style="color:white;">'+texto_cuenta_bancaria[0]+'-</span><span>'+texto_cuenta_bancaria[1]+'</span></td>'+
																	celda_valor_total+
																	'<td>'+btn_borrar+'</td>'+
																	'</tr>');
		        
		    }
		    	
		    	// Al mostrar la ventana modal
		    $("#myModal,#myModal2").on('shown.bs.modal', function () {
		    	$('#inv_producto_id').focus();
		    });
			// Al OCULTAR la ventana modal
		    $("#myModal,#myModal2").on('hidden.bs.modal', function () {
		       $('#btn_nueva_linea').focus();
		    });

			/**
				* Al seleccionar un producto
			**/
			$('#inv_producto_id').change(function(){

				reset_form_producto();
				$('#spin').show();

				// Si no se seleccionó un producto, salir
				if ($('#inv_producto_id').val()==='') {
					$('#spin').hide();
					return false;
				}

				// Preparar datos de los controles para enviar formulario de ingreso de productos
				var form_producto = $('#form_producto');
				var url = form_producto.attr('action');
				$('#id_bodega').val($('#inv_bodega_id').val());
				var datos = form_producto.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					
					var mov = $('#motivo').val().split('-');
					$('#spin').hide();
					
					// Se valida la existencia actual
					$('#existencia_actual').val(respuesta.existencia_actual);
					$('#tipo_producto').val(respuesta.tipo);

					if (respuesta.existencia_actual>0) {
						$('#existencia_actual').attr('style','background-color:#97D897;');
					}else{
						$('#existencia_actual').attr('style','background-color:#FF8C8C;');
						// Si el MOVIMIENTO del MOTIVO no es suma, no se permite seguir con existencia 0
						if (mov[1]!='entrada'&&respuesta.tipo!='servicio' && $('#permitir_inventarios_negativos') == 0) {
							$('#btn_agregar').hide(500);
							return false;
						}
					}
					
					// Asignar datos a los controles
					$('#costo_unitario').val(parseFloat(respuesta.precio_compra).toFixed(2));
					$('#unidad_medida1').val(respuesta.unidad_medida1);
					
					// Si la TRANSACCIÓN es una Entrada, se puede modificar el costo unitario			
					if ($('#id_transaccion').val()==1) {
						$('#costo_unitario').removeAttr('disabled');
						$('#costo_unitario').attr('style','background-color:white;');
						$('#costo_unitario').focus();
					}else{
						// Se pasa a ingresar las cantidades
						$('#cantidad').removeAttr('disabled');
						$('#cantidad').attr('style','background-color:white;');
						$('#cantidad').focus();
					}			
				});
			});


			/*
			** Al dejar el control del costo unitario, se valida lo ingresado, se inactiva el control
			** y se pasa a las cantidades
			*/
			$('#costo_unitario').keyup(function(event){
				calcula_costo_total();
				var x = event.which || event.keyCode;
				if( x==13 ){
					if (enfocar_el_incorrecto()!='costo_unitario') {
						$('#cantidad').removeAttr('disabled');
						$('#cantidad').focus();
						$('#cantidad').attr('style','background-color:white;');
					}				
				}
			});

			/*
			** Al digitar la cantidad, se valida la existencia actual, si es un movimiento de resta
			** luego se calcular el costo total
			*/
			$('#cantidad').keyup(function(event){
				//console.log($('#tipo_producto').val());
				var mov = $('#motivo').val().split('-');
				
				if ( mov[1] == 'salida' ) {
					var existencia_actual = $('#existencia_actual').val();
					if ((parseFloat($('#cantidad').val()) > parseFloat(existencia_actual))&&$('#tipo_producto').val()!='servicio'&& $('#permitir_inventarios_negativos') == 0) {
						alert('La Cantidad ingresada supera la existencia actual');
						$('#cantidad').val('');
						$('#cantidad').focus();
						return false;
					}
				}else{
					// Para que pueda pasar las validadciones
					if ( $('#costo_unitario').val( ) == 0 || $('#costo_unitario').val( ) == "" ) 
					{
						$('#costo_unitario').val( 0.0000001 );
					}
				}

				calcula_costo_total();

				var x = event.which || event.keyCode;
				if(x==13){
					if (enfocar_el_incorrecto()!='cantidad') {
						if (enfocar_el_incorrecto()!='costo_unitario') {
							$('#btn_agregar').show();
							$('#btn_agregar').focus();
						}
					}				
				}

			});
			

			// Al cambiar de motivo
			$('#motivo').change(function(){
				reset_form_producto();
				$('#inv_producto_id').val('');
			});

			/*
			** Al presionar el botón agregar
			*/
			$('#btn_agregar').click(function(event){
				event.preventDefault();
				var costo_total = $('#costo_total').val(); // ya está asignado con la funcion calcular_costo_total
				var producto = $('#inv_producto_id');
				var nombre_producto = $( "#inv_producto_id option:selected" ).text();
				var costo_unitario = $('#costo_unitario').val();
				var cantidad = $('#cantidad').val();
				var mov = $('#motivo').val().split('-');
				var motivo = $( "#motivo option:selected" ).text();
				
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

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

				if($.isNumeric(costo_total) && costo_total>0){
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
																	'<td class="text-center">'+producto.val()+'</td>'+
																	'<td class="nom_prod">'+nombre_producto+'</td>'+
																	'<td><span style="color:white;">'+mov[0]+'-</span><span '+estilo+'>'+motivo+'</span><input type="hidden" class="movimiento" value="'+mov[1]+'"></td>'+
																	celda_costo_unitario+
																	'<td class="text-center cantidad">'+cantidad+" "+$('#unidad_medida1').val()+'</td>'+
																	celda_costo_total+
																	'<td>'+btn_borrar+'</td>'+
																	'</tr>');
					
					// Se calculan los totales para la última fila
					calcular_totales();

					// Se retira el producto del select
					$("#inv_producto_id option[value='"+producto.val()+"']").remove();
					reset_form_producto();
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

				}else{
					enfocar_el_incorrecto();
					alert('Datos incorrectos o incompletos. Por favor verifique.');
					$('#btn_agregar').hide();
				}
			});

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
				reset_form_producto();
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


									// Desactivar el click del botón
									$( this ).off( event );
				
									$('#form_create').submit();	
								}else{
									alert('NO se han calculado los costos finales.');
								}
							}else{

								// Desactivar el click del botón
								$( this ).off( event );
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

			function reset_form_producto(){
				$('#form_producto input[type="text"]').val('');
				$('#form_producto input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#form_producto input[type="text"]').attr('disabled','disabled');
				$('#spin').hide();
				$('#btn_agregar').hide();
				$('#inv_producto_id').focus();
			}

			function calcula_costo_total(){
				var costo_unitario = $('#costo_unitario').val();
				var cantidad = $('#cantidad').val();
				var costo_total = (costo_unitario * cantidad).toFixed(2);
				if(($.isNumeric(costo_total)) && (costo_total>0)){
					$('#costo_total').val(costo_total);
				}else{
					$('#costo_total').val('');
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

				$('#total_costo_total').text("$"+sum.toFixed(2));
			}

			function enfocar_el_incorrecto(){
				var costo_unitario = $('#costo_unitario').val();
				var cantidad = $('#cantidad').val();
				var ok;
				
				if($.isNumeric(costo_unitario) && costo_unitario>0){
					$('#costo_unitario').attr('style','background-color:white;');

					if($.isNumeric(cantidad) && cantidad>0){
						$('#cantidad').attr('style','background-color:white;');
						ok = true;
					}else{
						$('#cantidad').attr('style','background-color:#FF8C8C;');
						$('#cantidad').focus();
						ok = 'cantidad';
					}
				}else{
					$('#costo_unitario').attr('style','background-color:#FF8C8C;');
					$('#costo_unitario').focus();
					ok = 'costo_unitario';
				}
				return ok;
			}
});