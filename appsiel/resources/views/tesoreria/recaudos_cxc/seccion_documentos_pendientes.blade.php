<!-- Documentos pendientes de cartera -->
<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" style="display: block; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">

			<div class="row">
				<div class="col-md-12">
					<br>
					<button class="btn btn-primary" id="btn_cargar_documentos_pendientes">
						<i class="fa fa-level-up"></i> Cargar documentos
					</button>
					{{ Form::Spin(48) }}
					<div id="div_aplicacion_cartera" style="display: none;">
			        	<div id="div_documentos_pendientes">

			        	</div>
			        </div>
					<br><br>
					<input type="hidden" name="input_total_valor_documentos_cxc" id="input_total_valor_documentos_cxc" value="0">
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
</div>

@section('scripts1')
	<script type="text/javascript">

		$(document).ready(function(){			

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

			function validar_valor_aplicar(input_valor_agregar)
			{
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

				var lineas_registros = $('#tabla_registros_documento').tableToJSON();
				$('#lineas_registros').val( JSON.stringify(lineas_registros) );
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

		});

	</script>
@endsection