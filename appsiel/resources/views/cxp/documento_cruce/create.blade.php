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
			{{ Form::open([ 'url' => $form_create['url'],'id'=>'form_create']) }}

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
				{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
				{{ Form::hidden( 'url_id_transaccion', Input::get( 'id_transaccion' ) ) }}

			{{ Form::close() }}
			<button type="button" class="btn btn-primary btn-xs" id="btn_continuar1"><i class="fa fa-btn fa-forward"></i> Continuar</button>

            <br/><br/>
            <div class="row">

            	<div class="col-md-6">
            		<div id="div_documentos_cartera" style="display: none;">
		        		<h4>Documentos pendientes de cartera</h4>
						<hr>
		            	<div id="div_cartera">

		            	</div>
		            </div>
            	</div>

            	<div class="col-md-6">
            		<div id="div_documentos_a_favor" style="display: none;">
		        		<h4>Documentos a favor</h4>
						<hr>
		            	<div id="div_afavor">

		            	</div>
		            </div>
            	</div>

            </div>

            <div class="row">
            	<div class="col-md-12">
            		<div id="div_documentos_a_cancelar" style="display: none;">
						<br/>
					    <h4>Documentos seleccionados</h4>
					    <hr>
			            <table class="table table-bordered" id="documentos_a_cancelar">
			            	<thead>
						        <tr>
						            <th data-override="movimiento_id"></th>
						            <th>Documento</th>
						            <th>Fecha</th>
						            <th data-override="saldo_pendiente">Saldo pend.</th>
						            <th data-override="valor_aplicar">Vlr. aplicar</th>
						            <th>&nbsp;</th>
						        </tr>
						    </thead>
						    <tbody>
						    </tbody>
					        <tfoot>
					            <tr>
					                <td colspan="4">&nbsp;</td>
					                <td> <div id="total_valor_total"></div> </td>
					                <td> &nbsp;</td>
					            </tr>
					        </tfoot>
						</table>						
					</div>
            	</div>            	
            </div>

            <div class="row">
            	<div class="col-md-2">
            	</div>
            	<div class="col-md-4">
            		<button type="button" class="btn btn-danger" id="btn_cancelar1" style="display: none;"><i class="fa fa-btn fa-remove"></i> Cancelar</button>
            	</div>
            	<div class="col-md-4">
            		<button type="button" class="btn btn-success" id="btn_guardar2" style="display: none;"><i class="fa fa-btn fa-save"></i> Guardar </button>
            	</div>
            	<div class="col-md-2">
            	</div>
            </div>			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	
	<script type="text/javascript">
		$(document).ready(function(){

			$('#fecha').val( get_fecha_hoy() );
			$('#fecha').focus();

			// PARA Elaborar documento CRUCE
			$('#btn_continuar1').click(function(event){
				
				if ( validar_requeridos() ) {
						
						$('#div_cargando').show();
								
						var url = '../cxp/get_cartera_tercero/' + $('#core_tercero_id').val() + '/' + $('#fecha').val();
						$.get( url, function( datos ) {
					        $('#div_cargando').hide();
					        //console.log(datos);
					        var tablas = datos.split('a3p0')

					        $('#btn_continuar1').hide();
					        $('#btn_cancelar1').show();

							$('#div_documentos_cartera').show();
							$('#div_documentos_a_favor').show();

							$('#div_cartera').html( tablas[0] );
							$('#div_afavor').html( tablas[1] );

							deshabilitar_campos_form_create();

							$("#div_cartera input:text").first().focus();
						});
				}
			});


			$(document).on('click', '.btn_agregar_documento_cartera', function(event) 
			{
				event.preventDefault();
				var fila = $(this).closest("tr");

				var celda = fila.find("input:text");

				if( validar_valor_aplicar( celda ) ){
					//var celda_borrar = "<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_documento'><i class='fa fa-btn fa-trash'></i></button> </td>";
					var celda_borrar = "<td> &nbsp; </td>";

					var valor = celda.val();
					fila.find("td:last").text( valor );
					fila.find("td:last").attr('class', 'valor_total' );

					//console.log( fila );

					var id_encabezado_documento = fila.attr('id');

					fila.prepend( "<td style='color: white;'> " + id_encabezado_documento + " </td>" );

					fila.append( celda_borrar );

					$('#div_documentos_a_cancelar').show();
					$('#documentos_a_cancelar').find('tbody:last').append( fila );

					$("#div_cartera input:text").first().focus();
					calcular_totales_cruce();		
				}		
			});


			$(document).on('click', '.btn_agregar_documento_afavor', function(event) 
			{
				event.preventDefault();
				var fila = $(this).closest("tr");

				var celda = fila.find("input:text");

				if( validar_valor_aplicar( celda ) )
				{
					//var celda_borrar = "<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar_documento'><i class='fa fa-btn fa-trash'></i></button> </td>";
					var celda_borrar = "<td> &nbsp; </td>";

					var valor = celda.val();
					fila.find("td:last").text( valor * -1 );
					fila.find("td:last").attr('class', 'valor_total' );

					//console.log( fila );

					var id_encabezado_documento = fila.attr('id');

					fila.prepend( "<td style='color: white;'> " + id_encabezado_documento + " </td>" );

					fila.append( celda_borrar );

					$('#div_documentos_a_cancelar').show();
					$('#documentos_a_cancelar').find('tbody:last').append( fila );

					$("#div_cartera input:text").first().focus();			
				}
				calcular_totales_cruce();
			});


			$('#btn_cancelar1').click(function(event){

				$('#div_cartera').html('');
				$('#div_afavor').html('');

				//Se resetean las filas del listado de pendiente por aplicar
				$('#documentos_a_cancelar').find('tbody').html( '' );
				
		        $('#btn_cancelar1').hide();
		        $('#div_documentos_cartera').hide();
		        $('#div_documentos_a_favor').hide();
		        $('#div_documentos_a_cancelar').hide();
		        $('#btn_guardar2').hide();
		        $('#btn_continuar1').show();

		        habilitar_campos_form_create();
			});

			// GUARDAR Doc. CRUCE
			$('#btn_guardar2').click(function(event){
				event.preventDefault();

				if ( validar_requeridos() )
				{
					// Desactivar el click del botón
					$( this ).off( event );

					// Se asigna la tabla de ingreso de registros a un campo hidden
					var tabla_documentos_a_cancelar = $('#documentos_a_cancelar').tableToJSON();
					$('#tabla_documentos_a_cancelar').val( JSON.stringify( tabla_documentos_a_cancelar ) );

					// Enviar formulario
					habilitar_campos_form_create();
					$('#form_create').submit();			
				}
					
			});

			function validar_valor_aplicar(celda)
			{
				var fila = celda.closest("tr");
				var ok;

				var valor = celda.val();

				if( $.isNumeric( valor ) ){
					valor = parseFloat( valor );
				}

				var saldo_pendiente = fila.find('td.col_saldo_pendiente').text();
				saldo_pendiente = parseFloat( saldo_pendiente.replace( /\./g, "" ) );

				console.log(valor);
				console.log(saldo_pendiente);


				if( valor > 0  && valor <= (saldo_pendiente + 1) )
				{
					celda.attr('style','background-color:white;');
					ok = true;
				}else{
					celda.attr('style','background-color:#FF8C8C;');
					celda.focus();
					ok = false;
				}

				return ok;
			}

			function calcular_totales_cruce(){
				var sum, cadena;
				sum = 0;
				$('.valor_total').each(function()
				{
				    
				    cadena = $(this).text();
				    //console.log( cadena );
				    sum+=parseFloat(cadena);
				});

				$('#total_valor_total').text( sum );
				$('#total_valor_aux').text( "$" + new Intl.NumberFormat("de-DE").format( sum ) );

				//console.log( sum );

				if ( sum == 0 ) {
			        $('#btn_guardar2').show();
			        $('#btn_guardar2').focus();
				}else{
					$('#btn_guardar2').hide();
				}
			}

			function deshabilitar_campos_form_create()
			{
				$('#fecha').attr('disabled','disabled');
				$('.custom-combobox-input').attr('disabled','disabled');
			}

			function habilitar_campos_form_create()
			{
				$('#fecha').removeAttr('disabled');
				$('.custom-combobox-input').removeAttr('disabled');
			}

		});
	</script>

@endsection