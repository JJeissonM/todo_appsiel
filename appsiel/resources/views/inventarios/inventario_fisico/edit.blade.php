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

			{{ Form::model($registro, ['url' => [ $form_create['url'] ], 'method' => 'PUT','files' => true,'id'=>'form_create']) }}
			
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

				{{ Form::hidden('hora_inicio', null, ['id'=>'hora_inicio']) }}
				{{ Form::hidden('hora_finalizacion', null, ['id'=>'hora_finalizacion']) }}
				
			{{ Form::close() }}

			<br><br>
			<div class="row">
            	<div class="col-md-6">
					{{ Form::bsSelect('grupo_inventario_id', null, 'Grupo de inventarios', $grupos, []) }}
				</div>
				<div class="col-md-6">
					<button type="button" class="btn btn-warning btn-xs" id="btn_cargar_grupo_inventario"><i class="fa fa-btn upload"></i> Cargar productos</button>
				</div>
			</div>

			<br/>
		    <h4>Ingreso de productos</h4>
		    <table class="table table-striped" id="ingreso_productos">
		        <thead>
		            <tr>
		                <th data-override="inv_producto_id">Cod.</th>
		                <th width="280px">Producto</th>
		                <th width="200px" data-override="motivo">Motivo</th>
		                <th data-override="costo_unitario"> Costo Unit. </th>
		                <th data-override="cantidad">Cantidad real</th>
		                <th data-override="costo_total">Costo Total</th>
		                <th width="10px">&nbsp;</th>
		            </tr>
		        </thead>
		        <tbody>
		        	{!! $lineas_registros !!}
		        </tbody>
		        <tfoot>
		        	<tr id="linea_ingreso_default">
		        		<td class="inv_producto_id"></td>
		        		<td> 
                            {{ Form::text('inv_producto_id', null, ['id'=>'inv_producto_id', 'data-toggle'=>'tooltip', 'autocomplete'=>'off', 'title'=>'Presione dos veces ESC para terminar.']) }}
                            <div id="suggestions"></div>
                        </td>
                        <td> {{ Form::select('inv_motivo_id',$motivos,null,['id'=>'inv_motivo_id']) }} </td>
                        <td class="costo_unitario"> </td>
                        <td> {{ Form::text('cantidad', null, [ 'id'=>'cantidad' ]) }} </td>
                        <td class="costo_total"> </td>
                        <td></td>
                    </tr>
		            <tr>
		                <td colspan="4">&nbsp;</td>
		                <td> <div id="total_cantidad"> 0 </div> </td>
		                <td> <div id="total_costo_total"> $0</div> </td>
		                <td> &nbsp;</td>
		            </tr>
		        </tfoot>
		    </table>
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){
			$('#core_empresa_id').val(1);

			$('#fecha').focus();

			$('#movimiento').removeAttr('disabled');

			$('#total_cantidad').text( {{ $cantidad_total }} );
			$('#total_costo_total').text( {{ $costo_total }} );

			$('#hora_inicio').val( get_hora_actual() );

			$('#movimiento').removeAttr('required');

			$('#fecha').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('.custom-combobox-input').focus();				
				}		
			});

		    $('#inv_bodega_id').change(function(){
				$('#grupo_inventario_id').focus();
			});
			

		    $('#grupo_inventario_id').change(function(){
				$('#btn_cargar_grupo_inventario').focus();
			});

			$("#btn_cargar_grupo_inventario").click(function(event){
				event.preventDefault();
				
				var grupo_id = $('#grupo_inventario_id').val();
		    	
		    	if ( grupo_id == '')
		    	{
		    		alert('Debe seleccionar un grupo para cargar sus productos.');
		    		$('#grupo_inventario_id').focus();
		    		return false;
		    	}
		    	
		    	cargar_grupo_inventario();
		    });


		    function cargar_grupo_inventario( grupo_id )
		    {
		    	if ( !validar_requeridos() )
				{
					return false;	
				}

		    	var url = "{{ url('inv_get_productos_del_grupo') }}";

		    	var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

				$.get( url, { bodega_id: $('#inv_bodega_id').val(), grupo_id: $('#grupo_inventario_id').val() } )
					.done(function( data ) {

						var cantidad = data.length;						

						if ( confirm("Se van a cargar " + cantidad + " productos. ¿Desea continuar?") )
						{
						  	var mov = $('#inv_motivo_id').val().split('-');
							var motivo = $( "#inv_motivo_id option:selected" ).text();

							switch(mov[1]){
								case 'entrada':
									var estilo = 'style="color:green;"';
									break;
								case 'salida':
									var estilo = 'style="color:red;"';
									break;
								default:
									break;
							}


							for(i=0; i<cantidad;i++)
							{

								$('#ingreso_productos').find('tbody:last').append('<tr id="'+data[i].producto_id+'">'+
																'<td class="text-center">'+data[i].producto_id+'</td>'+
																'<td class="nom_prod">'+data[i]['producto_descripcion']+'</td>'+
																'<td><span style="color:white;">'+mov[0]+'-</span><span '+estilo+'>'+motivo+'</span><input type="hidden" class="movimiento" value="'+mov[1]+'"></td>'+
																'<td class="lbl_costo_unitario">'+data[i]['costo_unitario']+'</td>'+
																'<td class="lbl_cantidad"><input type="text" name="cantidad_'+data[i]['producto_id']+'" class="input_cantidad" autocomplete="off"></td>'+
																'<td class="lbl_costo_total"></td>'+
																'<td>'+btn_borrar+'</td>'+
																'</tr>');
							}


							$('.input_cantidad').first().select();
						}else{
						  return false;
						  $('#grupo_inventario_id').focus();
						}

							
					});
		    }


		    $(document).on('keyup','.input_cantidad',function(event){

		    	var fila = $(this).closest("tr");
		    	var fila_siguiente = fila.next('tr');

				var x = event.which || event.keyCode;

				if ( validar_input_numerico( $(this) ) )
				{
					if( x == 13 )
					{
						cambiar_input_cantidad_a_texto( fila, $(this).val() );

						fila_siguiente.find('input.input_cantidad').select();
					}
					var costo_unitario = fila.find("td.lbl_costo_unitario").html();
					
					fila.find('td.lbl_costo_total').html( parseFloat(costo_unitario) * parseFloat( $(this).val() ) );

					calcular_totales();
				}else{
					fila.find('td.lbl_costo_total').html( 0 );
				}

			});



		    function cambiar_input_cantidad_a_texto( fila, control_input )
		    {
		    	fila.find('input.input_cantidad').parent().html( '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + control_input + '</div> </div>' );
		    }


		    $(document).on('blur','.input_cantidad',function(){

		    	if ( $(this).val() != '' ) {

		    		var fila = $(this).closest("tr");

					if ( validar_input_numerico( $(this) ) )
					{

						var costo_unitario = fila.find("td.lbl_costo_unitario").html();
						
						fila.find('td.lbl_costo_total').html( parseFloat(costo_unitario) * parseFloat( $(this).val() ) );

						calcular_totales();

						cambiar_input_cantidad_a_texto( fila, $(this).val() );

					}else{
						$(this).select();
						fila.find('td.lbl_costo_total').html( 0 );
						return false;
					}
				}

			});



			/*
			** Al eliminar una fila
			*/
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
		    	var fila_siguiente = fila.next('tr');

				fila.remove();
				fila_siguiente.find('input.input_cantidad').select();

				calcular_totales();
			});




			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;	
				}
				  	
				var total_cantidad = parseFloat( $('#total_cantidad').text() );

				if(  !$.isNumeric(total_cantidad) || total_cantidad <= 0  )
				{
					alert('No ha ninguna cantidad.');
					$('#inv_producto_id').focus();
				}else{

					// Desactivar el click del botón
					$( this ).off( event );

					$('#hora_finalizacion').val( get_hora_actual() );
					
					var table = $('#ingreso_productos').tableToJSON();
					
					$('#movimiento').val(JSON.stringify(table));

					$('#linea_ingreso_default').remove();

					habilitar_campos_encabezado();

					$('#form_create').submit();
				}
					
			});

			function habilitar_campos_encabezado()
			{
				$('#inv_bodega_id').removeAttr('disabled');
			}
			

			function calcular_totales()
			{
				var sum = 0.0;

				
				$('.lbl_cantidad').each(function()
				{
					if ( $(this).text() != '' ){
					    var cantidad = $(this).text();
					    sum += parseFloat(cantidad);
					}
				});
				

				var texto = sum.toFixed(2);

				$('#total_cantidad').text(texto);

				sum = 0.0;
				$('.lbl_costo_total').each(function()
				{
				    if ( $(this).text() != '0' && $(this).text() != '' ){
					    var cadena = $(this).text();
				    	sum += parseFloat(cadena);
				    }
				});

				$('#total_costo_total').text( '$ ' + new Intl.NumberFormat("de-DE").format( sum.toFixed(2) ) );
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
					$('#cliente_input').val( item_activo.html() );
					return false;

				}
	 			if ( x == 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-productos active');
					item_activo.attr('class','list-group-item list-group-item-productos');
					$('#cliente_input').val( item_activo.html() );
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
		    	var url = "{{ url('inv_consultar_productos') }}";

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




			/*
			** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
			*/
			$('#cantidad').keyup(function(event){

				// El registro se agrega al presionas Enter, si pasa las validaciones
				var x = event.which || event.keyCode;
				if( x==13)
				{
					if( validar_input_numerico( $(this) ) )
					{
						agregar_nueva_linea();
						return true;						
					}else{
						return false;
					}
					
				}

				//calcular_totales();

			});


			function agregar_nueva_linea()
			{
				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

				if( fila.find('td.inv_producto_id').html() == "" )
				{
					$('#inv_producto_id').select();
					alert('Producto mal ingresado.');
					return false;
				}

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

				var mov = $('#inv_motivo_id').val().split('-');
				var motivo = $( "#inv_motivo_id option:selected" ).text();

				switch(mov[1]){
					case 'entrada':
						var estilo = 'style="color:green;"';
						break;
					case 'salida':
						var estilo = 'style="color:red;"';
						break;
					default:
						break;
				}

				$('#ingreso_productos').find('tbody:last').append('<tr id="'+fila.find('.inv_producto_id').html()+'">'+
																'<td class="inv_producto_id">'+fila.find('.inv_producto_id').html()+'</td>'+
																'<td class="nom_prod">'+$('#inv_producto_id').val()+'</td>'+
																'<td><span style="color:white;">'+mov[0]+'-</span><span '+estilo+'>'+motivo+'</span><input type="hidden" class="movimiento" value="'+mov[1]+'"></td>'+
																'<td class="lbl_costo_unitario">'+ fila.find('.costo_unitario').html() +'</td>'+
																'<td class="lbl_cantidad">' + '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + $('#cantidad').val() + '</div> </div>' + ' </td>'+
																'<td class="lbl_costo_total">' + parseFloat( fila.find('.costo_unitario').html() ) * parseFloat( $('#cantidad').val() ) + '</td>'+
																'<td>'+btn_borrar+'</td>'+
																'</tr>');
				
				// Se calculan los totales
				calcular_totales();

				reset_linea_ingreso_default();
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
                $('#inv_producto_id').val( item_sugerencia.attr('data-descripcion') );
                //Hacemos desaparecer el resto de sugerencias
                $('#suggestions').html('');
                $('#suggestions').hide();
            }

            // Asignar valores de existecia_actual y costo_unitario
            function consultar_existencia(bodega_id, producto_id)
            {
            	$('#div_cargando').show();
            	var url = "{{ url('vtas_consultar_existencia_producto') }}";

				$.get( url, { transaccion_id: $('#core_tipo_transaccion_id').val(), bodega_id: bodega_id, producto_id: producto_id, fecha: $('#fecha').val() } )
					.done(function( respuesta ) {

						$('#div_cargando').hide();

						// Asignar datos a columnas invisibles (cantidades sin formatear)
						$('#linea_ingreso_default').attr('id', respuesta.producto_id);
						$('#linea_ingreso_default').find('.costo_unitario').html( respuesta.costo_promedio );

						// Se pasa a ingresar las cantidades
						$('#cantidad').focus();

						return true;
					});
            }


			function reset_linea_ingreso_default()
			{
				$('#linea_ingreso_default td.inv_producto_id').html('');

				$('#linea_ingreso_default input[type="text"]').val('');
				$('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
				$('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');


				$('#inv_motivo_id').removeAttr('style');
				$('#inv_motivo_id').removeAttr('disabled');

				$('#cantidad').removeAttr('style');
				$('#cantidad').removeAttr('disabled');

				$('#inv_producto_id').removeAttr('style');
				$('#inv_producto_id').removeAttr('disabled');				
				$('#inv_producto_id').focus();
				$("[data-toggle='tooltip']").tooltip('show');
			}


			var valor_actual, elemento_modificar, elemento_padre;
			
			// Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
			$(document).on('dblclick','.elemento_modificar',function(){

				elemento_modificar = $(this);

				elemento_padre = elemento_modificar.parent();

				valor_actual = $(this).html();

				elemento_modificar.hide();

				elemento_modificar.after('<input type="text" name="valor_nuevo" id="valor_nuevo" class="form-control">');

				document.getElementById('valor_nuevo').value = valor_actual;
				document.getElementById('valor_nuevo').select();

			});

			// Si la caja de texto pierde el foco
			$(document).on('blur','#valor_nuevo',function(){
				guardar_valor_nuevo();
			});

			// Al presiona teclas en la caja de texto
			$(document).on('keyup','#valor_nuevo',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				// Abortar la edición
				if( x == 27 ) // 27 = ESC
				{
					quitar_caja_texto_valor_nuevo();
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
					quitar_caja_texto_valor_nuevo();					
					elemento_modificar.show();
					return false;
				}

				elemento_modificar.html( valor_nuevo );
				elemento_modificar.show();

				quitar_caja_texto_valor_nuevo();

				recalcular_linea_editada( elemento_padre.closest('tr') );
				calcular_totales();
			}

			function recalcular_linea_editada( fila )
			{
				var costo_unitario = fila.find("td.lbl_costo_unitario").html();
				var cantidad = fila.find("div.elemento_modificar").html();
				
				fila.find('td.lbl_costo_total').html( parseFloat(costo_unitario) * parseFloat( cantidad ) );
			}


			function quitar_caja_texto_valor_nuevo()
			{
				if ( document.getElementById('valor_nuevo') !== null )
				{
					elemento_padre.find('#valor_nuevo').remove();
				}
			}


		});
	</script>
@endsection