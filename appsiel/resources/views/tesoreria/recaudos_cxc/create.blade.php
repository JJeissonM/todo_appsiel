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
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion')) }}

				{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}


				<input type="hidden" name="lineas_registros" id="lineas_registros" value="">
				<input type="hidden" name="lineas_registros_cheques" id="lineas_registros_cheques" value="">


			{{ Form::close() }}

			<!-- Formulario control cheque -->
			<div class="row">
				<div class="col-md-12">
					<div class="container-fluid" id="div_control_cheques" style="display: block; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">
            				@include('tesoreria.control_cheques.form_create')
			        </div>
				</div>
			</div>

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
						            <th> Cliente </th>
						            <th> Documento interno </th>
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
						            <td colspan="7"> &nbsp; </td>
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
			var hay_cheques = 0;
		$(document).ready(function(){
			
			asignar_fecha_hoy();

			var LineaNum = 0;

			ocultar_campo_formulario( $('#teso_caja_id'), false );
			ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );

			$('#cliente_input').focus();

			$('#cliente_input').on('focus',function(){
		    	$(this).select();
		    });

			$("#cliente_input").after('<div id="clientes_suggestions"> </div>');

			// Al ingresar código, descripción o código de barras del producto
		    $('#cliente_input').on('keyup',function(){

		    	var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x === 27 ) // 27 = ESC
				{
					$('#clientes_suggestions').html('');
		        	$('#clientes_suggestions').hide();
		        	return false;
				}


				/*
					Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
			    */
				if ( x === 40) // Flecha hacia abajo
				{
					var item_activo = $("a.list-group-item.active");					
					item_activo.next().attr('class','list-group-item list-group-item-cliente active');
					item_activo.attr('class','list-group-item list-group-item-cliente');
					$('#cliente_input').val( item_activo.next().html() );
					return false;

				}
				
				if ( x === 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-cliente active');
					item_activo.attr('class','list-group-item list-group-item-cliente');
					$('#cliente_input').val( item_activo.prev().html() );
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
						seleccionar_cliente( item );
		            	return false;
					}
				}

				var campo_busqueda = 'descripcion';
				if( $.isNumeric( $(this).val() ) ){
		    		var campo_busqueda = 'numero_identificacion';
		    	}

		    	// Si la longitud es menor a dos, todavía no busca
			    if ( $(this).val().length < 2 ) { return false; }

		    	//var url = '../../vtas_consultar_clientes';
		    	var url = "{{ url('core_consultar_terceros') }}";

				$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arooja la consulta
		                $('#clientes_suggestions').show().html(data);
		                $('a.list-group-item.active').focus();
					});
		    });


		    //Al hacer click en alguna de las sugerencias (escoger un producto)
		    $(document).on('click','.list-group-item-autocompletar', function(){
		    	seleccionar_cliente( $(this) );
		    	return false;
		    });

		    function seleccionar_cliente(item_sugerencia)
		    {
				// Asignar descripción al TextInput
		        $('#cliente_input').val( item_sugerencia.html() );
		        $('#cliente_input').css( 'background-color','white ' );

		        // Asignar Campos ocultos
		        $('#cliente_id').val( item_sugerencia.attr('data-tercero_id') );
		        $('#referencia_tercero_id').val( item_sugerencia.attr('data-tercero_id') );
		        $('#core_tercero_id').val( item_sugerencia.attr('data-tercero_id') );

		        //Hacemos desaparecer el resto de sugerencias
		        $('#clientes_suggestions').html('');
		        $('#clientes_suggestions').hide();

		        $('#tabla_registros_documento').find('tbody').html( '' );
		        $('#total_valor').text( "$0" );
		        get_documentos_pendientes_cxc( item_sugerencia.attr('data-tercero_id') );

		        $('#teso_medio_recaudo_id').focus();

		        return false;
		    }

		    function get_documentos_pendientes_cxc( core_tercero_id )
		    {
		    	var url = '../../tesoreria/get_documentos_pendientes_cxc';

				$.get( url, { core_tercero_id: core_tercero_id } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arooja la consulta
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

				if( validar_valor_aplicar( input_valor_agregar ) )
				{
					// Se reemplaza al input caja de texto por el valor ingresado en ella misma 
					var valor = input_valor_agregar.val();
					fila.find("td:last").text( valor );
					fila.find("td:last").attr('class', 'valor_total' );
					
					// Se agrega la final al final de la tabla de documentos seleccionados
					$('#div_documentos_a_cancelar').show();
					$('#tabla_registros_documento').find('tbody:last').append( fila );

					$("#div_documentos_pendientes input:text").first().select();
					calcular_totales();
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

				saldo_pendiente = parseFloat( saldo_pendiente );

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

			$('#teso_medio_recaudo_id').change(function()
			{
				if ( $(this).val() == '' )
				{
					ocultar_campo_formulario( $('#teso_caja_id'), false );
					ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );
					$(this).focus();
					return false;
				}

				var valor = $(this).val().split('-');

				if (valor[1]=='Tarjeta bancaria')
				{
					ocultar_campo_formulario( $('#teso_caja_id'), false );
					mostrar_campo_formulario( $('#teso_cuenta_bancaria_id'), '*Cuenta bancaria:', true );
				}else{
					ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );
					mostrar_campo_formulario( $('#teso_caja_id'), '*Caja:', true );
				}

				if ( valor[1] == 'cheque_de_tercero' || valor[1] == 'cheque_propio' )
				{
					$('#div_control_cheques').fadeIn(500);
				}else{
					$('#div_control_cheques').hide();
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

				if ( !validar_requeridos() )
				{
					return false;
				}		

				var total_valor = parseFloat( $('#total_valor').text().substring(1) );

				if ( total_valor <= 0 )
				{
					alert('No ha seleccionado documentos a pagar.');
					return false;
				}

				var teso_medio_recaudo_id = $('#teso_medio_recaudo_id').val().split('-');
				if ( teso_medio_recaudo_id[1] == 'cheque_propio' || teso_medio_recaudo_id[1] == 'cheque_de_tercero' )
				{
					if ( hay_cheques == 0 )
					{
						alert('Debe ingresar al menos un cheque para el medio de pago seleccionado.');
						return false;
					}

					if ( parseFloat( $('#input_valor_total_cheques').val() ) != total_valor )
					{
						alert('El valor total de cheques ingresados debe ser igual al valor total de documentos a pagar.');
						return false;
					}

					
				}

				// Se obtienen todos los datos del formulario y se envían
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

				// Se asigna la tabla de ingreso de registros a un campo hidden
				var lineas_registros_cheques = $('#tabla_registros_cheques').tableToJSON();
				$('#lineas_registros_cheques').val( JSON.stringify(lineas_registros_cheques) );

				// Enviar formulario
				habilitar_campos_form_create();
				$('#form_create').submit();
					
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