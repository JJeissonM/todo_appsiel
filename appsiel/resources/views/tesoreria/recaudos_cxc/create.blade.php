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

				<div class="alert alert-warning" id="div_documento_descuadrado" style="display: none;">
				  <strong>¡Advertencia!</strong> Documento está descuadrado.
				</div>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
				{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion')) }}

				{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}


				<input type="hidden" name="lineas_registros" id="lineas_registros" value="">
				<input type="hidden" name="lineas_registros_retenciones" id="lineas_registros_retenciones" value="">
				<input type="hidden" name="lineas_registros_asientos_contables" id="lineas_registros_asientos_contables" value="">
				<input type="hidden" name="lineas_registros_efectivo" id="lineas_registros_efectivo" value="">
				<input type="hidden" name="lineas_registros_transferencia_consignacion" id="lineas_registros_transferencia_consignacion" value="">
				<input type="hidden" name="lineas_registros_tarjeta_debito" id="lineas_registros_tarjeta_debito" value="">
				<input type="hidden" name="lineas_registros_tarjeta_credito" id="lineas_registros_tarjeta_credito" value="">
				<input type="hidden" name="lineas_registros_cheques" id="lineas_registros_cheques" value="">

			{{ Form::close() }}


			<div class="marco_formulario">
				<h5>
					Resumen de operaciones 
					<small>
						<button style="border: 0; background: transparent; display: none;" title="Mostrar" id="btn_mostrar_resumen_operaciones">
							<i class="fa fa-eye"></i>
						</button>
						<button style="border: 0; background: transparent;" title="Ocultar" id="btn_ocultar_resumen_operaciones">
							<i class="fa fa-eye-slash"></i>
						</button>
					</small>
				</h5>
				<div id="div_resumen_operaciones">
					<hr>
					<table class="table table-bordered">
						<tbody>
							<tr>
								<td><b>Efectivo:</b></td>
								<td id="valor_total_efectivo2" align="right" width="200px;">$ 0</td>
								<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
								<td><b>Documentos de CxC:</b></td>
								<td id="total_valor_documentos_cxc" align="right" width="200px;">$ 0</td>
							</tr>
							<tr>
								<td><b>Ctas. Bancarias:</b></td>
								<td id="valor_total_cuentas_bancarias" align="right">
									$ 0
								</td>
									<input type="hidden" name="input_valor_total_cuentas_bancarias" id="input_valor_total_cuentas_bancarias" value="0">
								<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
								<td><b>Acreditaciones:</b></td>
								<td id="valor_total_acreditaciones" align="right">$ 0</td>
							</tr>
							<tr>
								<td><b>Cheques:</b></td>
								<td id="valor_total_cheques2" align="right">$ 0</td>
								<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
								<td><b>Otras operaciones:</b></td>
								<td id="valor_total_otras_operaciones" align="right">
									$ 0
								</td>
									<input type="hidden" name="input_valor_total_otras_operaciones" id="input_valor_total_otras_operaciones" value="0">
							</tr>
							<tr>
								<td><b>Retenciones:</b></td>
								<td id="valor_total_retencion2" align="right">$ 0</td>
								<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
								<td colspan="2" style="background-color: #ddd;"> &nbsp; </td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td>&nbsp;</td>
								<td id="valor_total_resumen_medios_pagos" align="right">$ 0</td>
								<td width="10px;" style="border-top: 1px solid white; border-bottom: 1px solid white;">&nbsp;</td>
								<td>&nbsp;</td>
								<td id="valor_total_resumen_operaciones" align="right">
									$ 0
								</td>
									<input type="hidden" name="input_valor_total_resumen_medios_pagos" id="input_valor_total_resumen_medios_pagos" value="0">
									<input type="hidden" name="input_valor_total_resumen_operaciones" id="input_valor_total_resumen_operaciones" value="0">
							</tr>
						</tfoot>
					</table>

					<table class="table table-bordered">
						<tr>
							<td align="right" colspan=""><b>Diferencia:</b></td>
							<td id="valor_diferencia" align="right" width="200px;">$ 0</td>
							<input type="hidden" name="input_valor_diferencia" id="input_valor_diferencia" value="0">
						</tr>
					</table>
					<div></div>

				</div>
			</div>

			<div class="marco_formulario">
				<h5>
					Operaciones de recaudo 
					<small>
						<button style="border: 0; background: transparent;" title="Mostrar" id="btn_mostrar_operaciones">
							<i class="fa fa-eye"></i>
						</button>
						<button style="border: 0; background: transparent; display: none;" title="Ocultar" id="btn_ocultar_operaciones">
							<i class="fa fa-eye-slash"></i>
						</button>
					</small>
				</h5>
				<div id="div_operaciones" style="display: none;">
					<hr>
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab1"> Recaudo cartera (CxC) </a></li>
						<li><a data-toggle="tab" href="#tab2"> Retenciones </a></li>
						<li><a data-toggle="tab" href="#tab3"> Asientos contables </a></li>
				    </ul>

				    <div class="tab-content">
				    	<div id="tab1" class="tab-pane fade in active">
					        @include('tesoreria.recaudos_cxc.seccion_documentos_pendientes')
					    </div>
					    <div id="tab2" class="tab-pane fade">
					        @include('tesoreria.recaudos_cxc.seccion_retenciones')
		            	</div>
					    <div id="tab3" class="tab-pane fade">
					        @include('tesoreria.recaudos_cxc.seccion_asientos_contables')
		            	</div>
				    </div>
				</div>
			</div>

			<div class="marco_formulario">
				<h5>
					Medios de pago
					<small>
						<button style="border: 0; background: transparent;" title="Mostrar" id="btn_mostrar_medios_pago">
							<i class="fa fa-eye"></i>
						</button>
						<button style="border: 0; background: transparent; display: none;" title="Ocultar" id="btn_ocultar_medios_pago">
							<i class="fa fa-eye-slash"></i>
						</button>
					</small>
				</h5>
				<div id="div_medios_pago" style="display: none;">
					<hr>
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#tab_mp_1"> Efectivo </a></li>
						<li><a data-toggle="tab" href="#tab_mp_2"> Transferencia/Consignación </a></li>
						<li><a data-toggle="tab" href="#tab_mp_3"> Tarj. Débito </a></li>
						<li><a data-toggle="tab" href="#tab_mp_4"> Tarj. Crédito </a></li>
						<li><a data-toggle="tab" href="#tab_mp_5"> Cheque </a></li>
						<!-- <li><a data-toggle="tab" href="#tab_mp_6"> PSE </a></li> -->
				    </ul>

				    <div class="tab-content">
				    	<div id="tab_mp_1" class="tab-pane fade in active">
					        @include('tesoreria.medios_de_pago.seccion_efectivo')
					    </div>
					    <div id="tab_mp_2" class="tab-pane fade">
					        @include('tesoreria.medios_de_pago.seccion_transferencia_consignacion')
		            	</div>
					    <div id="tab_mp_3" class="tab-pane fade">
					        @include('tesoreria.medios_de_pago.seccion_tarjeta_debito')
		            	</div>
					    <div id="tab_mp_4" class="tab-pane fade">
					        @include('tesoreria.medios_de_pago.seccion_tarjeta_credito')
		            	</div>
					    <div id="tab_mp_5" class="tab-pane fade">
					        @include('tesoreria.medios_de_pago.seccion_cheque')
		            	</div>
					    <!-- <div id="tab_mp_6" class="tab-pane fade">
					        @ include('tesoreria.medios_de_pago.seccion_pse')
		            	</div> -->
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
		var hay_efectivo = 0;
		var hay_retencion = 0;
		var hay_transferencia_consignacion = 0;
		var hay_tarjeta_debito = 0;
		var hay_tarjeta_credito = 0;
		var hay_asiento_contable = 0;
		
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

		    $(document).on('click','#btn_mostrar_resumen_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_ocultar_resumen_operaciones').show();
		    	$('#div_resumen_operaciones').fadeIn(500);
		    });

		    $(document).on('click','#btn_ocultar_resumen_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_mostrar_resumen_operaciones').show();
		    	$('#div_resumen_operaciones').fadeOut(500);
		    });

		    $(document).on('click','#btn_mostrar_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_ocultar_operaciones').show();
		    	$('#div_operaciones').fadeIn(500);
		    });

		    $(document).on('click','#btn_ocultar_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_mostrar_operaciones').show();
		    	$('#div_operaciones').fadeOut(500);
		    });

		    $(document).on('click','#btn_mostrar_medios_pago', function(){
		    	$(this).hide();
		    	$('#btn_ocultar_medios_pago').show();
		    	$('#div_medios_pago').fadeIn(500);
		    });

		    $(document).on('click','#btn_ocultar_medios_pago', function(){
		    	$(this).hide();
		    	$('#btn_mostrar_medios_pago').show();
		    	$('#div_medios_pago').fadeOut(500);
		    });

		    $(document).on('click','#btn_cargar_documentos_pendientes', function(){
		    	if ($('#core_tercero_id').val() == 0 )
		    	{
		    		alert('Debe ingresar un Tercero.');
		    		$('#cliente_input').focus();
		    		return false;
		    	}

		    	$('#div_cargando').show();
		    	$('#div_spin').show();

		        $('#tabla_registros_documento').find('tbody').html( '' );
		        $('#total_valor').text( "$0" );
                $('#div_aplicacion_cartera').hide();
                $('#div_documentos_pendientes').html('');
		    	
		    	get_documentos_pendientes_cxc( $('#core_tercero_id').val() );

		    	$('#total_valor_documentos_cxc').text('$ 0');

		    	$.fn.actualizar_total_resumen_operaciones( parseFloat( $('#input_total_valor_documentos_cxc').val() * -1 ) );
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
		        //get_documentos_pendientes_cxc( item_sugerencia.attr('data-tercero_id') );

		        return false;
		    }

		    function get_documentos_pendientes_cxc( core_tercero_id )
		    {
		    	var url = '../../tesoreria/get_documentos_pendientes_cxc';

				$.get( url, { core_tercero_id: core_tercero_id } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arroja la consulta
		                $('#div_aplicacion_cartera').show();
		                $('#div_documentos_pendientes').html(data);
		                $('.td_boton').show();
		                $('.btn_agregar_documento').show();
		                $('#div_cargando').hide();
		                $('#div_spin').hide();
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

					$.fn.actualizar_total_resumen_operaciones( parseFloat(valor) );

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

				// RETENCIONES
				var lineas_registros_retenciones = $('#tabla_registros_retenciones').tableToJSON();
				$('#lineas_registros_retenciones').val( JSON.stringify(lineas_registros_retenciones) );

				// ASIENTOS CONTABLES
				var lineas_registros_asientos_contables = $('#tabla_registros_asientos_contables').tableToJSON();
				$('#lineas_registros_asientos_contables').val( JSON.stringify(lineas_registros_asientos_contables) );

				// EFECTIVO
				var lineas_registros_efectivo = $('#tabla_registros_efectivo').tableToJSON();
				$('#lineas_registros_efectivo').val( JSON.stringify(lineas_registros_efectivo) );

				// TRANSFERENCIAS O CONSIGNACIONES
				var lineas_registros_transferencia_consignacion = $('#tabla_registros_transferencia_consignacion').tableToJSON();
				$('#lineas_registros_transferencia_consignacion').val( JSON.stringify(lineas_registros_transferencia_consignacion) );

				// TARJETA DÉBITO
				var lineas_registros_tarjeta_debito = $('#tabla_registros_tarjeta_debito').tableToJSON();
				$('#lineas_registros_tarjeta_debito').val( JSON.stringify(lineas_registros_tarjeta_debito) );

				// TARJETA CRÉDITO
				var lineas_registros_tarjeta_credito = $('#tabla_registros_tarjeta_credito').tableToJSON();
				$('#lineas_registros_tarjeta_credito').val( JSON.stringify(lineas_registros_tarjeta_credito) );

				// CHEQUES
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
				$('#total_valor_documentos_cxc').text("$"+sum.toFixed(2));
				$('#input_total_valor_documentos_cxc').val( sum );
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

			$.fn.actualizar_total_resumen_medios_pagos = function ( valor_linea )
			{
			    // Total resumen
			    var actual_valor_resumen_medios_pagos = parseFloat( $('#input_valor_total_resumen_medios_pagos').val() );
				var nuevo_valor_resumen_medios_pagos = actual_valor_resumen_medios_pagos + valor_linea;
			    $('#valor_total_resumen_medios_pagos').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_resumen_medios_pagos.toFixed(2) ) );
				$('#input_valor_total_resumen_medios_pagos').val( nuevo_valor_resumen_medios_pagos );

				var valor_diferencia = parseFloat( $('#input_valor_total_resumen_medios_pagos').val() ) - parseFloat( $('#input_valor_total_resumen_operaciones').val() );
				$('#valor_diferencia').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_diferencia.toFixed(2) ) );
				$('#input_valor_diferencia').val( valor_diferencia );

				if ( valor_diferencia == 0 )
				{
					$('#btn_guardar').show();
					$('#div_documento_descuadrado').hide();
					$('#valor_diferencia').removeAttr('style');
				}else{
					$('#btn_guardar').hide();
					$('#div_documento_descuadrado').show();
					$('#valor_diferencia').attr('style','background-color: #ffa3a3;');
				}
			};

			$.fn.actualizar_total_resumen_operaciones = function ( valor_linea )
			{
			    // Total resumen
			    var actual_valor_resumen_operaciones = parseFloat( $('#input_valor_total_resumen_operaciones').val() );
				var nuevo_valor_resumen_operaciones = actual_valor_resumen_operaciones + valor_linea;
			    $('#valor_total_resumen_operaciones').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_resumen_operaciones.toFixed(2) ) );
				$('#input_valor_total_resumen_operaciones').val( nuevo_valor_resumen_operaciones );

				var valor_diferencia = parseFloat( $('#input_valor_total_resumen_medios_pagos').val() ) - parseFloat( $('#input_valor_total_resumen_operaciones').val() );
				$('#valor_diferencia').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_diferencia.toFixed(2) ) );
				$('#input_valor_diferencia').val( valor_diferencia );

				if ( valor_diferencia == 0 )
				{
					$('#btn_guardar').show();
					$('#div_documento_descuadrado').hide();
					$('#valor_diferencia').removeAttr('style');
				}else{
					$('#btn_guardar').hide();
					$('#div_documento_descuadrado').show();
					$('#valor_diferencia').attr('style','background-color: #ffa3a3;');
				}

			};
		});


	</script>
@endsection