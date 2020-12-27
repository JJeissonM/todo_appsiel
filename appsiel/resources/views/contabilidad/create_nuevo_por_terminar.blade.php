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
				
				<div style="display: none;"> 
					
				</div>	
			{{ Form::close() }}

			<br/>

		    <a class="btn btn-default btn-xs" href="#"> Crear CxC </a>
		    <a class="btn btn-default btn-xs" href="#"> Aplicar CxC </a>
		    <a class="btn btn-default btn-xs" href="#"> Crear CxP </a>
		    <a class="btn btn-default btn-xs" href="#"> Aplicar CxP </a>
		    {!! $tabla->dibujar() !!}
		    
		</div>
	</div>
	<br/><br/>

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')
	
	<script src="{{asset('assets/js/autocompletar.js')}}"></script>

	<script type="text/javascript">


		// Al seleccionar un item en el autocompletar
		function procesar_item(item)
		{
			
			console.log( item );

			switch( item.attr('data-tipo_campo') )
			{
				case 'cuenta':
					$('#linea_ingreso_default').find('.cuenta_id').html( item.attr('data-cuenta_id') );

					// Se asigna por defecto el tercero del encabezado del documento
					$('#linea_ingreso_default').find('.tercero_id').html( $('#core_tercero_id').val() );

					$('#valor_db').focus();
					break;
				case 'tercero':
					$('#linea_ingreso_default').find('.tercero_id').html( item.attr('data-tercero_id') );
					break;
				default:
					console.log( item );
					break;
			}
		}


		$(document).ready(function(){

			$('#core_tercero_id').on('focus',function(){

				// Para Autocompletar, declaradas en layouts.principal
				campo_busqueda_texto = 'descripcion';
				campo_busqueda_numerico = 'numero_identificacion';
				url_consulta = '../core_consultar_terceros';

				//$(this).addClass('autocompletar');
				
				var celda = $(this).parent();
				console.log( celda );
				celda.append('<div class="autocompletar_sugerencias"></div>');

		    });

			$('#core_tercero_id').on('blur',function(){

				//$(this).removeClass('autocompletar');

				var celda = $(this).parent();
				celda.find('div.autocompletar_sugerencias').remove();
		    });


			$('#cuenta_id').on('focus',function(){

				if ( !validar_requeridos() ) { return false; }

				// Para Autocompletar, declaradas en layouts.principal
				campo_busqueda_texto = 'descripcion';
				campo_busqueda_numerico = 'codigo';
				url_consulta = '../contab_consultar_cuentas';

				//$(this).addClass('autocompletar');
				
				var celda = $(this).parent();
				celda.append('<div class="autocompletar_sugerencias"></div>');

		    });

			$('#cuenta_id').on('blur',function(){

				//$(this).removeClass('autocompletar');

				var celda = $(this).parent();
				celda.find('div.autocompletar_sugerencias').remove();
		    });

			
			$('#tercero_id').on('focus',function(){
				if ( !validar_requeridos() ) { return false; }
				// Para Autocompletar, declaradas en layouts.principal
				campo_busqueda_texto = 'descripcion';
				campo_busqueda_numerico = 'numero_identificacion';
				url_consulta = '../core_consultar_terceros';

				$(this).addClass('autocompletar');
				
				var celda = $(this).parent();
				celda.append('<div class="autocompletar_sugerencias"></div>');
		    });

			$('#tercero_id').on('blur',function(){

				$(this).removeClass('autocompletar');
				
				var celda = $(this).parent();
				celda.find('div.autocompletar_sugerencias').remove();
		    });
			/**/
			

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
			$('#fecha').focus();


			var sumas_iguales = 0;
			var LineaNum = 0;
			var debito, credito;

			$('#core_tipo_doc_app_id').change(function(){
				$('#fecha').focus();
			});

			$('#fecha').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#core_tercero_id').focus();				
				}		
			});



			$('#core_tercero_id').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#cuenta_id').focus();				
				}		
			});



			$('#tercero_id').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#detalle').focus();				
				}		
			});


			$('#detalle').keyup(function(event){

				var x = event.which || event.keyCode;
				if(x==13){
					$('#valor_db').focus();				
				}		
			});

			$('#valor_db').keyup(function(event){

				var x = event.which || event.keyCode;
				if(x==13){
					$('#valor_cr').focus();			
				}		
			});

			$('#valor_cr').keyup(function(event){

				var x = event.which || event.keyCode;
				if(x==13){
					agregar_nueva_linea();
				}		
			});

			

			function agregar_nueva_linea()
			{
				var fila = $(this).closest("tr");
				var ok = validar_linea();
				if( ok ) {
					var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
			        var cuenta = '<span style="color:white;">' + $('#combobox_cuentas').val() + '-</span>' + $( "#combobox_cuentas option:selected" ).text();
			        var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#combobox_terceros option:selected" ).text();
			        var detalle = $('#col_detalle').val();
			        var debito = $('#col_debito').val();
			        var credito = $('#col_credito').val();
			        
			        if(debito == ''){
			        	debito = 0; // Para no sumar una caja de texto vacía
			        }
			        
			        if( credito == ''){
			        	credito = 0;
			        }

			        $('#ingreso_registros').find('tbody:last').append('<tr id="fila_'+LineaNum+'" >' +
																	'<td id="cuenta_'+LineaNum+'">' + cuenta + '</td>'+
																	'<td id="tercero_'+LineaNum+'">' + tercero + '</td>'+
																	'<td id="detalle_'+LineaNum+'">' + detalle + '</td>'+
																	'<td id="debito_'+LineaNum+'"  class="debito">$' + debito + '</td>'+
																	'<td id="credito_'+LineaNum+'"  class="credito">$' + credito + '</td>'+
																	'<td>'+btn_borrar+'</td>'+
																	'</tr>');
			       	
			       	calcular_totales();
			       	fila.remove();
			       	nueva_linea_ingreso_datos();
				}

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
				$('#btn_nuevo').show();
				calcular_totales();
			});


			// GUARDAR
			$('#btn_guardar').click(function(event){
				event.preventDefault();		

				//$('#core_tercero_id').val( $('#ph_propiedad_id').val() );
				$('#codigo_referencia_tercero').val( 0 );
				
				$('#valor_total').val( $('#total_debito').text().substring(1) )

				// Se obtienen todos los datos del formulario y se envían
				// Se validan nuevamente los campos requeridos
				var control = 1;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = 0;
					  alert('Este campo es requerido. '+ $(this).attr('name') 	);
					  return false;
					}else{
					  control = 1;
					}
				});

				if (control==1) {
					if ( $('#sumas_iguales').text( ) == 0 ) {
						
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
				}else{
					alert('Faltan campos por llenar.');
				}
					
			});

			
			function validar_linea()
			{
				var control = true;

				// Se escogen los campos de la fila ingresada
				var fila = $('#linea_ingreso_default');

				console.log( fila.find('.cuenta_id').html() );

				if ( fila.find('.cuenta_id').html() == '' )
				{
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
				$('#sumas_iguales').text( sumas_iguales );
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
		});
	</script>
@endsection