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
			{{ Form::open( ['url'=>$form_create['url'],'id'=>'form_create']) }}

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


			<div id="div_ingreso_registros">
				<br/>
			    <h4>Ingreso de valores</h4>
			    <hr>
			    <div class="table-responsive" id="table_content">
					<table class="table table-striped" id="ingreso_registros">
				        <thead>
				            <tr>
				                <th data-override="teso_motivo_id" width="200px">Motivo</th>
				                <th data-override="linea_tercero_id" width="200px">Tercero</th>
				                <th data-override="detalle">Detalle</th>
				                <th data-override="valor">Valor</th>
				                <th width="10px">&nbsp;</th>
				            </tr>
				        </thead>
				        <tbody>
				            <tr>
				                <td></td>
				                <td width="200px"></td>
				                <td></td>
				                <td></td>
				                <td></td>
				            </tr>
				        </tbody>
				        <tfoot>
				            <tr>
				                <td>
				                	<button id="btn_nuevo" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar registro</button>
				                </td>
				                <td width="200px"></td>
				                <td></td>
				                <td></td>
				                <td></td>
				            </tr>
				            <tr>
				                <td colspan="3">&nbsp;</td>
				                <td> <div id="total_valor">$0</div> </td>
				                <td> &nbsp;</td>
				            </tr>
				            <tr>
				                <td colspan="2">&nbsp;</td>
				                <td> <div id="lbl_total_pendiente" style="color: red;"></div></td>
				                <td> <div id="total_pendiente" style="color: red;"></div> </td>
				                <td> &nbsp;</td>
				            </tr>
				        </tfoot>
				    </table>
				</div>
		    </div>
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){
			$('#teso_caja_id').parent().hide();
			$('#teso_cuenta_bancaria_id').parent().hide();


			$('#fecha').val( get_fecha_hoy() );
			$('#fecha').focus();


			var LineaNum = 0;

			$('#teso_tipo_motivo').change(function(){
				$('#linea_ingreso_default').remove();
		        nueva_linea_ingreso_datos();
			});



			$('#teso_medio_recaudo_id').change(function(){
				var valor = $(this).val().split('-');
				if (valor!='') {
					if (valor[1]=='Tarjeta bancaria'){
						$('#teso_caja_id').parent().hide();
						$('#teso_cuenta_bancaria_id').parent().show();
					}else{
						$('#teso_cuenta_bancaria_id').parent().hide();
						$('#teso_caja_id').parent().show();
					}
				}else{
					$('#teso_cuenta_bancaria_id').parent().hide();
					$('#teso_caja_id').parent().hide();
					$(this).focus();
				}			
			});


			$("#btn_nuevo").click(function(event){
				event.preventDefault();
		        nueva_linea_ingreso_datos();
		    });


		    function nueva_linea_ingreso_datos(){
		    	$('#div_cargando').fadeIn();

				var url = '../pagos/ajax_get_fila/' + $('#teso_tipo_motivo').val();
				$.get( url, function( datos ) {
			        $('#div_cargando').hide();

			        $('#ingreso_registros').find('tbody:first').append( datos );

			        $('#combobox_motivos').focus();

			        $('#btn_nuevo').hide();
				});
		    }

			$(document).on('click', '.btn_confirmar', function(event) {
				event.preventDefault();
				LineaNum++;
				var fila = $(this).closest("tr");
				var ok = validar_linea();
				if( ok ) {
					var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
			        var cuenta = '<span style="color:white;">' + $('#combobox_motivos').val() + '-</span>' + $( "#combobox_motivos option:selected" ).text();
			        var tercero = '<span style="color:white;">' + $('#combobox_terceros').val() + '-</span>' + $( "#combobox_terceros option:selected" ).text();
			        var detalle = $('#col_detalle').val();
			        var valor = $('#col_valor').val();

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
				
				var valor_total = parseFloat( $('#valor_total').val() );

				var total_valor = parseFloat( $('#total_valor').text().substring(1) );

				if ( valor_total != total_valor) {
					alert('El VALOR TOTAL PAGO no coincide con el valor total de los registros ingresados.');
					return false;
				}

				// Se obtienen todos los datos del formulario y se envían
				// Se validan nuevamente los campos requeridos
				

				if ( validar_requeridos() ) {

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
				}else{
					alert('Faltan campos por llenar.');
				}
					
			});


			function calcular_totales(){
				var sum = 0.0;
				sum = 0.0;
				$('.valor').each(function()
				{
				    var cadena = $(this).text();
				    sum += parseFloat(cadena.substring(1));
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
					alert('Debe seleccionar una motivo.');
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


				$('#teso_tipo_motivo').attr('disabled','disabled');
				
			}

			function habilitar_campos_form_create()
			{
				$('#fecha').removeAttr('disabled');
				
				//$('.custom-combobox').show();

				//$('#core_tercero_id').hide();
				$('#core_tercero_id').removeAttr('disabled');
				
				$('#teso_tipo_motivo').removeAttr('disabled');
			}
		});
	</script>
@endsection