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
			{{ Form::open(['url'=>$form_create['url'],'id'=>'form_create']) }}

				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
				{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
				{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

				{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}


				<input type="hidden" name="lineas_registros" id="lineas_registros" value="">

			{{ Form::close() }}

			<!-- Documentos pendientes de cartera -->
            <div class="row">
            	<div class="col-md-12">
            		<div id="div_aplicacion_cartera" style="display: none;">
		            	<div id="div_documentos_pendientes">

		            	</div>
		            </div>
            	</div>
            </div>

			<!-- Documentos seleccionados -->
            <div class="row">
            	<div class="col-md-12">
            		<div id="div_documentos_a_cancelar" style="display: none;">
            			<h3 style="width: 100%; text-align: center;"> Documentos seleccionados </h3>
						<hr>

						<table class="table table-striped" id="tabla_registros_documento">
						    <thead>
						        <tr>
						            <th style="display: none;" data-override="id_doc"> ID Doc. Pendiente </th>
						            <th> Proveedor </th>
						            <th> Documento interno </th>
						            <th> Factura del proveedor </th>
						            <th> Fecha </th>
						            <th> Fecha vencimiento </th>
						            <th> Valor Documento </th>
						            <th> Valor pagado </th>
						            <th> Saldo pendiente </th>
						            <th data-override="abono"> Abono </th>
						        </tr>
						    </thead>
						    <tbody>
						    </tbody>
						    <tfoot>
						        <tr>
						            <td style="display: none;"> &nbsp; </td>
						            <td colspan="8"> &nbsp; </td>
						            <td> <div id="total_valor">$0</div> </td>
						        </tr>						    	
						    </tfoot>
						</table>
		            </div>
            	</div>
            </div>
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){
			
			asignar_fecha_hoy();

			var LineaNum = 0;

			$('#teso_caja_id').parent().parent().hide();
			$('#teso_cuenta_bancaria_id').parent().parent().hide();

			$('#proveedor_input').focus();

			$('#proveedor_input').on('focus',function(){
		    	$(this).select();
		    });

			$("#proveedor_input").after('<div id="proveedores_suggestions"> </div>');

			// Al ingresar código, descripción o código de barras del producto
		    $('#proveedor_input').on('keyup',function(){

		    	var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x === 27 ) // 27 = ESC
				{
					$('#proveedores_suggestions').html('');
		        	$('#proveedores_suggestions').hide();
		        	return false;
				}


				/*
					Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
			    */
				if ( x === 40) // Flecha hacia abajo
				{
					var item_activo = $("a.list-group-item.active");					
					item_activo.next().attr('class','list-group-item list-group-item-proveedor active');
					item_activo.attr('class','list-group-item list-group-item-proveedor');
					$('#proveedor_input').val( item_activo.next().html() );
					return false;

				}
				
				if ( x === 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-proveedor active');
					item_activo.attr('class','list-group-item list-group-item-proveedor');
					$('#proveedor_input').val( item_activo.prev().html() );
					return false;
				}

				// Al presionar Enter
				if( x === 13 )
				{
					var item = $('a.list-group-item.active');
					
					if( item.attr('data-tercero_id') === undefined )
					{
						alert('El tercero ingresado no existe.');
		            	return false;
					}else{
						seleccionar_proveedor( item );
		            	return false;
					}
				}

				var campo_busqueda = 'descripcion';
				if( $.isNumeric( $(this).val() ) ){
		    		var campo_busqueda = 'numero_identificacion';
		    	}

		    	// Si la longitud es menor a dos, todavía no busca
			    if ( $(this).val().length < 2 ) { return false; }

		    	//var url = '../../compras_consultar_proveedores';
		    	var url = "{{ url('core_consultar_terceros') }}";

				$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arooja la consulta
		                $('#proveedores_suggestions').show().html(data);
		                $('a.list-group-item.active').focus();
					});
		    });


		    //Al hacer click en alguna de las sugerencias (escoger un producto)
		    $(document).on('click','.list-group-item-autocompletar', function(){
		    	seleccionar_proveedor( $(this) );
		    	return false;
		    });


		    function seleccionar_proveedor(item_sugerencia)
		    {

				// Asignar descripción al TextInput
		        $('#proveedor_input').val( item_sugerencia.html() );
		        $('#proveedor_input').css( 'background-color','white ' );

		        // Asignar Campos ocultos
		        $('#proveedor_id').val( item_sugerencia.attr('data-tercero_id') );
		        $('#referencia_tercero_id').val( item_sugerencia.attr('data-tercero_id') );
		        $('#core_tercero_id').val( item_sugerencia.attr('data-tercero_id') );

		        /*
		        var forma_pago = 'contado';
		        var dias_plazo = parseInt( item_sugerencia.attr('data-dias_plazo') );
		        if ( dias_plazo > 0 ) { forma_pago = 'credito'; }

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
				*/

		        //Hacemos desaparecer el resto de sugerencias
		        $('#proveedores_suggestions').html('');
		        $('#proveedores_suggestions').hide();

		        $('#tabla_registros_documento').find('tbody').html( '' );
		        $('#total_valor').text( "$0" );
		        get_documentos_pendientes_cxp( item_sugerencia.attr('data-tercero_id') );

		        $('#teso_medio_recaudo_id').focus();

		        return false;
		    }


		    function get_documentos_pendientes_cxp( core_tercero_id )
		    {
		    	var url = '../../tesoreria/get_documentos_pendientes_cxp';

				$.get( url, { core_tercero_id: core_tercero_id } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arroja la consulta
		                $('#div_aplicacion_cartera').show();
		                $('#div_documentos_pendientes').html(data);
		                $('.td_boton').show();
		                $('.btn_agregar_documento').show();
					});
		    }


			$(document).on('click', '.btn_agregar_documento', function(event) 
			{
				event.preventDefault();
				var fila = $(this).closest("tr");

				var input_valor_agregar = fila.find("input:text");

				//$('#tabla_registros_documento').find('tbody:last').append( '<tr><td colspan="9"> vamos </td></tr>' );

				if( validar_valor_aplicar( input_valor_agregar ) )
				{
					var celda_borrar = "<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_documento'><i class='fa fa-btn fa-trash'></i></button> </td>";
					//var celda_borrar = "<td> &nbsp; </td>";

					var valor = input_valor_agregar.val();
					fila.find("td:last").text( valor );
					fila.find("td:last").attr('class', 'valor_total' );
					
					$('#div_documentos_a_cancelar').show();
					$('#tabla_registros_documento').find('tbody:last').append( fila );

					$("#div_documentos_pendientes input:text").first().select();
					calcular_totales();	

					//fila.remove();	
				}		
			});

			function validar_valor_aplicar(input_valor_agregar){
				var fila = input_valor_agregar.closest("tr");
				var respuesta;

				var valor = input_valor_agregar.val();

				if( !validar_input_numerico( input_valor_agregar ) )
				{
					return false;
				}
				
				valor = parseFloat( valor );

				var saldo_pendiente = fila.find('td.col_saldo_pendiente').attr('data-saldo_pendiente');
				//console.log( "aja: " + saldo_pendiente );
				saldo_pendiente = parseFloat( saldo_pendiente );

				//console.log(valor);


				if( valor > 0  && valor <= saldo_pendiente) {
					input_valor_agregar.attr('style','background-color:white;');
					respuesta = true;
				}else{
					input_valor_agregar.attr('style','background-color:#FF8C8C;');
					input_valor_agregar.focus();
					respuesta = false;
				}

				return respuesta;
			}

		    
			$('#core_tipo_doc_app_id').change(function(){
				$('#fecha').focus();
			});

			$('#teso_medio_recaudo_id').change(function(){
				var valor = $(this).val().split('-');
				if (valor!='') {
					if (valor[1]=='Tarjeta bancaria'){
						$('#teso_caja_id').parent().parent().hide();
						$('#teso_cuenta_bancaria_id').parent().parent().show();
					}else{
						$('#teso_cuenta_bancaria_id').parent().parent().hide();
						$('#teso_caja_id').parent().parent().show();
					}
				}else{
					$('#teso_cuenta_bancaria_id').parent().parent().hide();
					$('#teso_caja_id').parent().parent().hide();
					$(this).focus();
				}			
			});

			/*
			** Al eliminar una fila
			*/
			// Se utiliza otra forma con $(document) porque el $('#btn_eliminar') no funciona pues
			// es un elemento agregadi despues de que se cargó la página
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				fila.remove();
				$('#btn_nuevo').show();
				calcular_totales();
			});

			// Al introducir valor en la caja de texto
			$(document).on('keyup', '.col_valor', function() {
				var celda = $(this);
				//console.log( celda );
				validar_valor( celda );

				var x = event.which || event.keyCode;
				if( x === 13 ){
					celda.next('input:button').focus();
				}
			});

			// GUARDAR 
			$('#btn_guardar').click(function(event){
				event.preventDefault();				

				var total_valor = parseFloat( $('#total_valor').text().substring(1) );

				if ( total_valor <= 0 ) {
					alert('No ha seleccionado documentos a pagar.');
					return false;
				}

				// Se obtienen todos los datos del formulario y se envían
				// Se validan nuevamente los campos requeridos
				

				if ( validar_requeridos() )
				{
					// Desactivar el click del botón
					$( this ).off( event );
					
					// Eliminar fila de ingreso de registro vacia
					var object = $('#combobox_motivos').val();	
					if( typeof object == typeof undefined){
						// Si no hay linea de ingreso de registros
						// Todo bien
						//alert('Todo bien.');
					}else{
						var fila = $('#combobox_motivos').closest("tr");
						fila.remove();
					}

					// Se asigna la tabla de ingreso de registros a un campo hidden
					var lineas_registros = $('#tabla_registros_documento').tableToJSON();
					$('#lineas_registros').val( JSON.stringify(lineas_registros) );

					// Enviar formulario
					habilitar_campos_form_create();
					$('#form_create').submit();

				}else{
					alert('Faltan campos por llenar.');
				}
					
			});


			function calcular_totales(){
				var sum = 0.0;
				sum = 0.0;
				$('.valor_total').each(function()
				{
				    var cadena = $(this).text();
				    sum += parseFloat(cadena);
				});

				$('#total_valor').text("$"+sum.toFixed(2));
			}


			function validar_linea(){
				var ok;

				if ( $('#combobox_motivos').val() != '' ) {
					var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#combobox_terceros option:selected" ).text();

					var detalle = $('#col_detalle').val();

					var valor = $('#col_valor').val();
					
					if ( valor != '' ) {
						if ( $.isNumeric(valor)  && valor > 0 ) {
							ok = true;
						}else{
							$('#col_valor').attr('style','background-color:#FF8C8C;');
							$('#col_valor').focus();
							ok = false;
						}
					}else{
						$('#col_valor').attr('style','background-color:#FF8C8C;');
						$('#col_valor').focus();
						ok = false;
					}
				}else{
					alert('Debe seleccionar una concepto.');
					$('#combobox_motivos').focus();
					ok = false;
				}
				return ok;
			}

			function validar_valor(celda){
				var fila = celda.closest("tr");
				//console.log(fila);

				var ok;

				var valor = celda.val();

				if( $.isNumeric( valor ) ){
					valor = parseFloat( valor );
				}		

				if( $.isNumeric( valor ) && valor > 0 ) {
					celda.attr('style','background-color:white;');
					ok = true;
				}else{
					celda.attr('style','background-color:#FF8C8C;');
					celda.focus();
					ok = false;
				}

				return ok;
			}

			function habilitar_text($control){
				$control.removeAttr('disabled');
				$control.attr('style','background-color:white;');
			}

			function deshabilitar_text($control){
				$control.attr('style','background-color:#ECECE5;');
				$control.attr('disabled','disabled');
			}
			
			function validar_requeridos(){
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = false;
					  alert('Este campo es requerido.' + $(this).prev('label').text() );
					  return false;
					}else{
					  control = true;
					}
				});
				return control;
			}

			function deshabilitar_campos_form_create()
			{

				$('#fecha').attr('disabled','disabled');

				$('.custom-combobox').hide();

				$('#core_tercero_id').show();
				$('#core_tercero_id').attr('disabled','disabled');
				
			}

			function habilitar_campos_form_create()
			{
				$('#fecha').removeAttr('disabled');
				
				//$('.custom-combobox').show();

				//$('#core_tercero_id').hide();
				$('#core_tercero_id').removeAttr('disabled');
			}

			function asignar_fecha_hoy()
			{
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
			}
		});
	</script>
@endsection