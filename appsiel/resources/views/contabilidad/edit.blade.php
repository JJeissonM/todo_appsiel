@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:0;
		}

		#ingreso_registros select {
			width: 150px;
		}

	</style>
@endsection

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	{!! $mensaje_duplicado !!}

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>
			{{ Form::model($registro, ['url' => [ $form_create['url'] ], 'method' => 'PUT','files' => true, 'id' => 'form_create']) }}
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

			{{ Form::close() }}

			<br/>
			@include('contabilidad.incluir.tabla_ingreso_registros', [ 'lineas_documento' => $lineas_documento])
		</div>
	</div>
	<br/><br/>

	@include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')
	
	<!--<script src="{ { asset('assets/js/input_lista_sugerencias.js') }}"></script>  -->

	<script type="text/javascript">
		
		var LineaNum = {{ $linea_num }};

		calcular_totales();
		
        function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
        {
        	obj_text_input.parent().next().find(':input').focus();
        }
		
		$(document).ready(function(){
			$('#fecha').focus();

			var sumas_iguales = 0;
			var debito, credito;

			var direccion = location.href;


			// Al presiona teclas en la caja de texto
			$(document).on('keyup','#col_detalle,#col_debito',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				// Guardar
				if( x == 13 ) // 13 = ENTER
				{
		        	$(this).parent().next().find(':input').focus();
				}
			});

			$(document).on('keyup','#col_credito',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				// Guardar
				if( x == 13 ) // 13 = ENTER
				{
		        	$('.btn_confirmar').focus();
				}
			});

			$('#core_tipo_doc_app_id').change(function(){
				$('#fecha').focus();
			});

			$('#fecha').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#core_tercero_id').focus();				
				}		
			});

			/*
			**	Agregar NUEVA línea de ingreso de datos
			*/
			$("#btn_nuevo").click(function(event){
				event.preventDefault();
				nueva_linea_ingreso_datos();
		    });

		    function nueva_linea_ingreso_datos(){
		    	
		    	$('#div_cargando').fadeIn();
				
				var url = '../contab_get_fila/' + 0;

				// Si se está en la url de editar 
				if( direccion.search("edit") >= 0 ) 
				{
					var url = '../../contab_get_fila/' + 0;
				}

				$.get( url, function( datos ) {
			        $('#div_cargando').hide();

			        $('#ingreso_registros').find('tbody:first').append( datos );

			        $('#cuenta_input').focus();

			        $('#btn_nuevo').hide();

				});
		    }


			$(document).on('click', '.btn_confirmar', function(event) {
				event.preventDefault();
				
				var fila = $(this).closest("tr");

				if( validar_linea() )
				{
					agregar_linea_registro();
			       	fila.remove();
			       	nueva_linea_ingreso_datos();
				}

			});

			function agregar_linea_registro()
			{
				LineaNum ++;
				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-trash'></i></button>";
		        var cuenta = '<span style="color:white;">' + $('#combobox_cuentas').val() + '-</span>' + $( "#cuenta_input" ).val();
		        var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#tercero_input" ).val();
		        var detalle = $('#col_detalle').val();


		        var tipo_transaccion_linea = $('#tipo_transaccion_linea').val();
		        var fecha_vencimiento = $('#fecha_vencimiento').val();
		        var documento_soporte_tercero = $('#documento_soporte_tercero').val();

		        var debito = $('#col_debito').val();
		        var credito = $('#col_credito').val();
		        
		        if(debito == '')
		        {
		        	debito = 0; // Para no sumar una caja de texto vacía
		        }
		        
		        if( credito == '')
		        {
		        	credito = 0;
		        }

		        var celda_debito = '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + debito + '</div> </div>';
		        var celda_credito = '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + credito + '</div> </div>';
		        var celda_detalle = '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + detalle + '</div> </div>';

		        $('#ingreso_registros').find('tbody:last').append('<tr id="fila_'+LineaNum+'" >' +
		        												'<td style="display: none;">' + fecha_vencimiento + '</td>' + 
		        												'<td style="display: none;">' + documento_soporte_tercero + '</td>' + 
		        												'<td>' + tipo_transaccion_linea + '</td>' + 
																'<td id="cuenta_'+LineaNum+'">' + cuenta + '</td>'+
																'<td id="tercero_'+LineaNum+'">' + tercero + '</td>'+
																'<td id="detalle_'+LineaNum+'">' + celda_detalle + '</td>'+
																'<td id="debito_'+LineaNum+'"  class="debito">$' + celda_debito + '</td>'+
																'<td id="credito_'+LineaNum+'"  class="credito">$' + celda_credito + '</td>'+
																'<td>'+btn_borrar+'</td>'+
																'</tr>');
		       	
		       	calcular_totales();
			}


			/*
			** Al eliminar una fila
			*/
			// Se utiliza otra forma con $(document) porque el $('#btn_eliminar') no funciona pues
			// es un elemento agregado por ajax (despues de que se cargó la página)
			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				fila.remove();
				LineaNum --;
				$('#btn_nuevo').show();
				calcular_totales();
				$('#btn_nuevo').focus();
			});


			// GUARDAR
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( LineaNum <= 0 )
				{
					alert('No se han ingresado registros.')
					return false;
				}
				

				//$('#core_tercero_id').val( $('#ph_propiedad_id').val() );
				$('#codigo_referencia_tercero').val( 0 );
				
				$('#valor_total').val( $('#total_debito').text().substring(1) )

				if ( !validar_requeridos() )
				{
					return false;
				}

				if ( $('#sumas_iguales').text( ) == 0 )
				{
					// Desactivar el click del botón
					$( this ).off( event );

					// Eliminar fila de ingreso de registro vacia
					var object = $('#combobox_cuentas').val();	
					if( typeof object == typeof undefined){
						// Si no hay linea de ingreso de registros
						// Todo bien
						//alert('Todo bien.');
					}else{
						var fila = $('#combobox_cuentas').closest("tr");
						fila.remove();
					}

					// Se asigna la tabla de ingreso de registros a un campo hidden
					var tabla_registros_documento = $('#ingreso_registros').tableToJSON();
					$('#tabla_registros_documento').val( JSON.stringify(tabla_registros_documento) );

					// Enviar formulario
					$('#form_create').submit();
				}else{
					alert('El asiento contable está descuadrado.')
				}
					
			});

			
			function validar_linea(){
				var ok;

				if ( $('#combobox_cuentas').val() != '' ) {
					var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#combobox_terceros option:selected" ).text();

					var detalle = $('#col_detalle').val();

					var debito = $('#col_debito').val();
					
					if ( debito == '' ) {
						debito = 0;
						var credito = $('#col_credito').val();
						if ( credito == '' ) {

							alert('Debe ingresar un valor Débito o Crédito');
							$('#col_debito').focus();
							ok = false;
						}else{
							if ( $.isNumeric(credito)  && credito > 0 ) {
								ok = true;
							}else{
								$('#col_credito').attr('style','background-color:#FF8C8C;');
								$('#col_credito').focus();
								ok = false;
							}	
						}
					}else{
						credito = 0;
						if ( $.isNumeric(debito) && debito > 0 ) {
							ok = true;
						}else{
							$('#col_debito').attr('style','background-color:#FF8C8C;');
							$('#col_debito').focus();
							ok = false;
						}
					}
				}else{
					alert('Debe seleccionar una cuenta.');
					$('#combobox_cuentas').focus();
					ok = false;
				}
				return ok;
			}

			function calcular_totales(){
				var sum = 0.0;

				// Sumar columna de los débitos
				sum = 0.0;
				$('.debito').each(function()
				{
				    var cadena = $(this).text();
				    sum += parseFloat( cadena.substring(1) );
				});
				$('#total_debito').text("$"+sum.toFixed(2));
				sumas_iguales = sum;

				// Sumar columna de los créditos
				sum = 0.0;
				$('.credito').each(function()
				{
				    var cadena = $(this).text();
				    sum += parseFloat( cadena.substring(1) );
				});
				$('#total_credito').text("$"+sum.toFixed(2));


				sumas_iguales = sumas_iguales - sum;
				$('#sumas_iguales').text( sumas_iguales.toFixed(0) );
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
			});

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
				if ( valor_nuevo == valor_actual) { return false; }

				elemento_modificar.html( valor_nuevo );
				elemento_modificar.show();

				elemento_padre.find('#valor_nuevo').remove();

				calcular_totales();
			}


			$("#btn_crear_cxc").click(function(event){

				var control_cuenta = $('#combobox_cuentas').val();

				if( control_cuenta === undefined )
				{
					nueva_linea_ingreso_datos();
				}

		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = "{{url('contab_get_formulario_cxc')}}";

				$.get( url )
					.done(function( data ) {

		                $('#contenido_modal').html(data);

		                $("#div_spin").hide();

		                $('#fecha_vencimiento_aux').focus( );
		                $('#fecha_vencimiento_aux').val( get_fecha_hoy() );

					});		        
		    });


			$("#btn_crear_cxp").click(function(event){

				var control_cuenta = $('#combobox_cuentas').val();

				if( control_cuenta === undefined )
				{
					nueva_linea_ingreso_datos();
				}

		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = "{{url('contab_get_formulario_cxp')}}";

				$.get( url )
					.done(function( data ) {

		                $('#contenido_modal').html(data);

		                $("#div_spin").hide();

		                $('#fecha_vencimiento_aux').focus( );
		                $('#fecha_vencimiento_aux').val( get_fecha_hoy() );

					});		        
		    });


            $('.btn_save_modal').click(function(event){

		        $('#fecha_vencimiento').val( $('#fecha_vencimiento_aux').val() );

		        $('#cuenta_input').val( $('#cuenta_input_aux').val() );
		        $('#combobox_cuentas').val( $('#combobox_cuentas_aux').val() );

		        $('#tercero_input').val( $('#tercero_input_aux').val() );
		        $('#combobox_terceros').val( $('#combobox_terceros_aux').val() );

		        $('#documento_soporte_tercero').val( $('#documento_soporte_tercero_aux').val() );

		        $('#tipo_transaccion_linea').val( $('#tipo_transaccion_linea_aux').val() );

		        $('#col_detalle').val( $('#detalle_aux').val() );
		        $('#col_debito').val( $('#valor_debito_aux').val() );
		        $('#col_credito').val( $('#valor_credito_aux').val() );

		        if( validar_linea() )
				{
	                $('#contenido_modal').html( ' ' );
	                $('#myModal').modal("hide");
					agregar_linea_registro();

					// Se retira la línea vieja y se agrega una nueva
					$('#linea_ingreso_datos').remove();
					nueva_linea_ingreso_datos();
				}
	                

            });

		});

		function calcular_totales()
		{
			var sum = 0.0;

			// Sumar columna de los débitos
			sum = 0.0;
			$('.debito').each(function()
			{
			    var cadena = $(this).text();
			    sum += parseFloat( cadena.substring(1) );
			    console.log( sum );
			});
			$('#total_debito').text("$"+sum.toFixed(2));
			sumas_iguales = sum;

			// Sumar columna de los créditos
			sum = 0.0;
			$('.credito').each(function()
			{
			    var cadena = $(this).text();
			    sum += parseFloat( cadena.substring(1) );
			});
			$('#total_credito').text("$"+sum.toFixed(2));


			sumas_iguales = sumas_iguales - sum;
			$('#sumas_iguales').text( sumas_iguales.toFixed(0) );
		}

	</script>
@endsection