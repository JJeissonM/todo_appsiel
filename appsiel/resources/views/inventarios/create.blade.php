@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">

		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>

		    @if( $form_create['modo'] == 'create' )
				{{ Form::open(['url' => $form_create['url'],'id'=>'form_create','files' => true]) }}
			@else
				{{ Form::model($registro_id, ['url' => $form_create['url'] , 'id'=>'form_create', 'method' => 'PUT','files' => true]) }}
			@endif

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
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}
				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux'])}}

				@if( !is_null( Input::get('ruta_redirect') ))
					{{ Form::bsHidden( 'ruta_redirect', Input::get('ruta_redirect') ) }}
				@endif

				@if( !is_null( Input::get('registro_id') ))
					{{ Form::bsHidden( 'registro_id', Input::get('registro_id') ) }}
				@endif

				@if( !is_null( Input::get('doc_ventas_id') ))
					{{ Form::bsHidden( 'doc_ventas_id', Input::get('doc_ventas_id') ) }}
				@endif

				@if( isset( $hay_existencias_negativas ) )
					{{ Form::bsHidden( 'hay_existencias_negativas', $hay_existencias_negativas ) }}
				@else
					{{ Form::bsHidden( 'hay_existencias_negativas', 0 ) }}
				@endif

				<div class="alert alert-warning" id="div_hay_existencias_negativas" style="display: none;">
				  <strong>Advertencia!</strong> Los items con filas en rojo no tienen cantidades sufiencientes. No podrá guardar este documento.
				</div>
				
				
			{{ Form::close() }}

			@include('inventarios.create_tabla_productos')

			<!-- Modal -->
			@include('inventarios.incluir.ingreso_productos_2')
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script src="{{ asset( 'assets/js/inventarios/commons.js' ) }}"></script>
	<script src="{{ asset( 'assets/js/modificar_con_doble_click_sin_recargar.js' ) }}"></script>

	<script type="text/javascript">
		
		function ejecutar_funcion_guardar_nuevo_valor_doble_click( campo_modificado, nuevo_valor )
	    {
	    	recalcular_totales();
	    }

		function recalcular_totales()
		{
			var sum = 0.0;
			$('.cantidad').each(function()
			{
			    var cantidad = $(this).text();
				// Se elimina la cadena "UND" del texto de la cantidad
				var pos_espacio = cantidad.search(" ");
				cantidad = cantidad.substring(0,pos_espacio);
			    sum += parseFloat(cantidad);

			    var fila = $(this).closest("tr");
			    var costo_unitario_text = fila.find('td.costo_unitario').text();
			    var costo_unitario = parseFloat( costo_unitario_text.substring(1) );
			    fila.find('td.costo_total').text( '$' + cantidad * costo_unitario );
			});
			var texto = sum.toFixed(2);
			$('#total_cantidad').text(texto);

			sum = 0.0;
			$('.costo_total,.costo_total2').each(function()
			{
			    var cadena = $(this).text();
			    sum += parseFloat(cadena.substring(1));
			});

			/*
			** Al presionar el botón agregar
			*/
			$('#btn_agregar').click(function(event){
				event.preventDefault();
				
				if ( !validar_existencia_actual() )
		        {
		            $('#costo_total').val('');
		            return false;
		        }

				var costo_total = $('#costo_total').val(); // ya está asignado con la funcion calcular_costo_total
				var producto = $('#inv_producto_id');
				var nombre_producto = producto.val() + ' ' + $( "#inv_producto_id_aux" ).val();
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
					celda_costo_unitario = '<td class="text-right">$'+parseFloat(costo_unitario).toFixed(2)+'</td>';
					celda_costo_total = '<td class="text-right costo_total">$'+costo_total+'</td>';
					
					// Si la transaccion es ENSAMBLE, no se muestran los costos para los productos
					// finales (tipo entrada, movimiento suma)
					if ($('#id_transaccion').val()==4) {
						if (mov[1]=='entrada') {
							// para la entradas se suman las cantidades
							suma_cantidades_prod_entrada = suma_cantidades_prod_entrada + parseFloat(cantidad);
							// la class costo_unitario solo la tienen las celdas de los productos de entradas
							celda_costo_unitario = '<td class="text-right costo_unitario">Sin calcular</td>';
							// se usa otra class costo_total2 porque esta es la que se va a rellenar cuando se
							// calcule el costo de los productos de entrada
							celda_costo_total = '<td class="text-right costo_total2">Sin calcular</td>';
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
					$('#inv_producto_id_aux').val('');
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
					$('#fecha').attr('disabled','disabled');

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
				//var id_producto = fila.attr("id");
				//var nombre_producto = fila.find("td.nom_prod").html();
				//$('#inv_producto_id').append($('<option>', { value: id_producto, text: nombre_producto}));

				fila.remove();
				reset_form_producto();
				$('#inv_producto_id_aux').val('');
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


					$('#fecha').removeAttr('disabled');
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

					// Desactivar el click del botón
					$( this ).off( event );

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
							  $(this).removeAttr('disabled');
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

									$('#fecha').removeAttr('disabled');
									$('#form_create').submit();	
								}else{
									alert('NO se han calculado los costos finales.');
								}
							}else{

								// Desactivar el click del botón
								$( this ).off( event );
								
								$('#fecha').removeAttr('disabled');
								$('#form_create').submit();	
							}
							
						}else{
							$(this).removeAttr('disabled');
							alert('Faltan campos por llenar.');
						}
			  			
					}else{
						$(this).removeAttr('disabled');
						alert('No ha ingresado productos.');
						$('#inv_producto_id_aux').focus();
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

			function enfocar_el_incorrecto()
			{
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
						$('#cantidad').select();
						ok = 'cantidad';
					}
				}else{
					$('#costo_unitario').attr('style','background-color:#FF8C8C;');
					$('#costo_unitario').select();
					ok = 'costo_unitario';
				}
				return ok;
			}
		            
		    function validacion_saldo_movimientos_posteriores()
		    {
		        var url = "{{ url('inv_validacion_saldo_movimientos_posteriores' ) }}" + '/' + $('#inv_bodega_id').val() + '/' + $('#inv_producto_id').val() + '/' + $('#fecha').val() + '/' + $('#cantidad').val() + '/' + $('#existencia_actual').val() + '/' + $('#motivo').val().split('-')[1];

		        $.get( url )
		            .done( function( data ) {
		                if ( data != 0 )
		                {
		                    $('#popup_alerta_danger').show();
		                    $('#popup_alerta_danger').text( data );
		                    $('#btn_agregar').hide();
		                }else{
		                    $('.btn_save_modal').off( 'click' );
		                    $('#popup_alerta_danger').hide();
		                    $('#btn_agregar').show();
							$('#btn_agregar').focus();
		                }
		            });

		    }

		    function calcula_nuevo_saldo_a_la_fecha()
		    {
		    	var mov = $('#motivo').val().split('-');
		        // PARA ENTRADAS
		        if ( mov[1] == 'entrada' )
		        {
		            var saldo_actual = parseFloat( $('#existencia_actual').val() );
		            var cantidad_anterior = parseFloat( $('#cantidad_anterior').val() );
		            var nuevo_saldo = saldo_actual - cantidad_anterior + parseFloat( $('#cantidad').val() );

		            $('#existencia_actual').val( nuevo_saldo );
		            $('#cantidad_anterior').val( $('#cantidad').val() );
		        }

		        // PARA SALIDAS
		        if ( mov[1] == 'salida' )
		        {
		            var nuevo_saldo = parseFloat( $('#saldo_original').val() ) + parseFloat( $('#cantidad_original').val() ) - parseFloat( $('#cantidad').val() );

		            $('#existencia_actual').val( nuevo_saldo );
		        }
		        
		    }
			
			// Para ajustes desde inventario físico
			if( getParameterByName('doc_inv_fisico_id') != '' )
			{
				// se trae la cantidad de productos desde el controller
				$('#hay_productos').val( $('#hay_productos_aux').val() );
				

				//calcular_totales
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
			}else{
				// Solo se coloca la fecha de hoy si no es un ajuste desde inventarios físico
				$('#fecha').val( get_fecha_hoy() );
			}
			$('#total_costo_total').text("$"+sum.toFixed(2));
		}

	</script>
@endsection