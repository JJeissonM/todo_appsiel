@extends('layouts.principal')

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

		    @if( is_null($registro) )
				{{ Form::open( ['url'=>$form_create['url'],'id'=>'form_create']) }}
					{{ Form::hidden( 'editando', 0, ['id'=>'editando'] ) }}
			@else
				{{ Form::model($registro, ['url' => [ $form_create['url'] ], 'method' => 'PUT','files' => true, 'id' => 'form_create']) }}
					{{ Form::hidden( 'editando', 1, ['id'=>'editando'] ) }}
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

				{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
				{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
				{{ Form::hidden( 'url_id_transaccion', Input::get( 'id_transaccion' ) ) }}

				{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}

			{{ Form::close() }}

			<br/>
			@include('tesoreria.pagos.tabla_ingreso_registros', [ 'lineas_tabla_ingreso_registros' => $lineas_tabla_ingreso_registros ] )

		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script type="text/javascript">

		function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
        {
        	obj_text_input.parent().next().find(':input').focus();
        }

		$(document).ready(function(){

			ocultar_campo_formulario( $('#teso_caja_id'), false );
			ocultar_campo_formulario( $('#teso_cuenta_bancaria_id'), false );

			calcular_totales();

			if ( $('#editando').val() == 0 )
			{
				$('#fecha').val( get_fecha_hoy() );
			}
			
			$('#fecha').focus();

			$('#fecha').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#core_tercero_id').focus();				
				}		
			});

			$(document).on('keyup', '.text_input_sugerencias', function(){

				var codigo_tecla_presionada = event.which || event.keyCode;

				if ( codigo_tecla_presionada == 13 && $(this).val() == '' )
			    {
			    	$(this).parent().next().find(':input').focus();
			    }
			});

			$('#valor_total').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#btn_nuevo').focus();				
				}		
			});


			$(document).on('keyup','#col_detalle',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x == 13 ) // 13 = ENTER
				{
		        	$(this).parent().next().find(':input').focus();
				}
			});


			$(document).on('keyup','#col_valor',function(){

				var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x == 13 ) // 13 = ENTER
				{
		        	$('.btn_confirmar').focus();
				}
			});

			var LineaNum = 0;

			$('#teso_tipo_motivo').change(function(){
				$('#linea_ingreso_default').remove();
		        nueva_linea_ingreso_datos();
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


			$("#btn_nuevo").click(function(event){
				event.preventDefault();
		        nueva_linea_ingreso_datos();
		    });		    


		    function nueva_linea_ingreso_datos(){
		    	$('#div_cargando').fadeIn();

				var url = "{{ url('tesoreria/pagos/ajax_get_fila/') }}" + "/" + $('#teso_tipo_motivo').val();
				$.get( url, function( datos ) {
			        $('#div_cargando').hide();

			        $('#ingreso_registros').find('tbody:first').append( datos );

			        $('#motivo_input').focus();

			        $('#btn_nuevo').hide();
				});
		    }

			$(document).on('click', '.btn_confirmar', function(event) {
				event.preventDefault();
				LineaNum++;
				var fila = $(this).closest("tr");
				var ok = validar_linea();
				if( ok )
				{
					var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";

			        var cuenta = '<span style="color:white;">' + $('#combobox_motivos').val() + '-</span>' + $( "#combobox_motivos option:selected" ).text();

			        var cuenta = '<span style="color:white;">' + $('#combobox_motivos').val() + '-</span>' + $( "#motivo_input" ).val();
			        
			        var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#tercero_input" ).val();

			        var detalle = '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + $('#col_detalle').val() + '</div> </div>';

			        var valor = '<div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + $('#col_valor').val() + '</div> </div>';

			        $('#ingreso_registros').find('tbody:last').append('<tr id="fila_'+LineaNum+'" >' +
																	'<td id="cuenta_'+LineaNum+'">' + cuenta + '</td>'+
																	'<td id="tercero_'+LineaNum+'">' + tercero + '</td>'+
																	'<td id="detalle_'+LineaNum+'">' + detalle + '</td>'+
																	'<td id="valor_'+LineaNum+'"  class="valor">$' + valor + '</td>'+
																	'<td>'+btn_borrar+'</td>'+
																	'</tr>');
			       	
			       	calcular_totales();
			       	fila.remove();
			       	nueva_linea_ingreso_datos();
                
					// Bajar el Scroll hasta el final de la página
					$("html, body").animate( { scrollTop: $(document).height()+"px"} );
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
				
				var valor_total = parseFloat( $('#valor_total').val() );

				var total_valor = parseFloat( $('#total_valor').text().substring(1) );

				if ( valor_total != total_valor) {
					alert('El VALOR TOTAL PAGO no coincide con el valor total de los registros ingresados.');
					return false;
				}

				// Se obtienen todos los datos del formulario y se envían

						// Desactivar el click del botón
						$( this ).off( event );

						// Eliminar fila(s) de ingreso de registro vacia
						$('.linea_ingreso_default').remove();						

						// Se asigna la tabla de ingreso de registros a un campo hidden
						var tabla_registros_documento = $('#ingreso_registros').tableToJSON();
						$('#tabla_registros_documento').val( JSON.stringify(tabla_registros_documento) );

						// Enviar formulario
						habilitar_campos_form_create();
						$('#form_create').submit();	
					
			});


			function calcular_totales()
			{
				var sum = 0.0;
				sum = 0.0;
				$('.valor').each(function()
				{
				    var cadena = $(this).text();
				    sum += parseFloat(cadena.substring(1));
				});

				$('#total_valor').text("$"+sum.toFixed(2));
			}


			function validar_linea()
			{
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
					alert('Debe ingresar una motivo.');
					$('#combobox_motivos').focus();
					ok = false;
				}
				return ok;
			}

			function validar_valor(celda){
				var fila = celda.closest("tr");

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


				$('#teso_tipo_motivo').attr('disabled','disabled');
				
			}

			function habilitar_campos_form_create()
			{
				$('#fecha').removeAttr('disabled');
				$('#core_tercero_id').removeAttr('disabled');
				
				$('#teso_tipo_motivo').removeAttr('disabled');
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
		});
	</script>
@endsection