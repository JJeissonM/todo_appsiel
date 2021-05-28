<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" style="display: block; border: 1px solid #ddd; border-radius: 4px;">
			<h5 class="text-center">Registros de Dctos. o deducciones</h5>
			<hr>
			<div class="row">
				<div class="col-md-6" >
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<h6 style="width: 100%; text-align: center;">FORMULARIO</h6>
						<hr>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('descuento_pronto_pago_id', null, 'Tipo Dscto.', $descuentos_pronto_pago, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'valor_descuento_pronto_pago', null, 'Valor', []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="row" style="padding:5px;">
									{{ Form::bsTextArea('observacion_descuento_pronto_pago', null, 'Observación', []) }}
								</div>
							</div>
						</div>
						<p style="text-align: center;">
							<button class="btn btn-primary" id="btn_agregar_descuento_pronto_pago"> Agregar </button>
						</p>
					</div>
					<br><br>
				</div>
				<div class="col-md-6">
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<div class="row">
							<div class="col-md-12">
								<h6 style="width: 100%; text-align: center;">DESCUENTOS INGRESADOS</h6>
								<hr>
								<table class="table table-striped table-bordered" id="tabla_registros_descuento_pronto_pago">
									<thead>
										<tr>
											<th style="display: none;">descuento_pronto_pago_id</th>
											<th style="display: none;">valor_descuento_pronto_pago</th>
											<th>Tipo descuento</th>
											<th>Observación</th>
											<th>Valor</th>
											<th></th>
										</tr>
									</thead>
									<tbody>				
									</tbody>
									<tfoot>
										<tr>
											<td colspan="2">&nbsp;</td>
											<td id="valor_total_descuento_pronto_pago" align="right">$ 0</td>
											<td><input type="hidden" name="input_valor_total_descuento_pronto_pago" id="input_valor_total_descuento_pronto_pago" value="0"></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
        </div>
	</div>
</div>

@section('scripts10')
	<script type="text/javascript">

		$(document).ready(function(){

			$(document).on('click', '#btn_agregar_descuento_pronto_pago', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_descuento_pronto_pago').find('tbody:last').append('<tr class="linea_registro_descuento_pronto_pago">' + string_fila + '</tr>');
				
				hay_descuento_pronto_pago++;

				calcular_totales_descuento_pronto_pago( parseFloat( $('#valor_descuento_pronto_pago').val() ) );

				resetear_campos_descuento_pronto_pago();

				$('#descuento_pronto_pago_id').focus();
			});

			$(document).on('click', '.btn_eliminar_descuento_pronto_pago', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_descuento_pronto_pago--;

				calcular_totales_descuento_pronto_pago( parseFloat( fila.find('.valor_descuento_pronto_pago').text() ) * -1 );

			});

			$('#valor_descuento_pronto_pago').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_descuento_pronto_pago').focus();
				}
			});

			function calcular_totales_descuento_pronto_pago( valor_linea )
			{
				var valor_total_descuento_pronto_pago = 0.0;
				$('.linea_registro_descuento_pronto_pago').each(function()
				{
				    valor_total_descuento_pronto_pago += parseFloat( $(this).find('.valor_descuento_pronto_pago').text() );
				});

				$('#valor_total_descuento_pronto_pago').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_descuento_pronto_pago.toFixed(2) ) );
				$('#valor_total_descuento_pronto_pago2').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_descuento_pronto_pago.toFixed(2) ) );
				$('#input_valor_total_descuento_pronto_pago').val(valor_total_descuento_pronto_pago);

				$.fn.actualizar_total_resumen_medios_pagos( valor_linea );

				var lineas_registros_descuento_pronto_pagos = $('#tabla_registros_descuento_pronto_pago').tableToJSON();
				$('#lineas_registros_descuento_pronto_pagos').val( JSON.stringify(lineas_registros_descuento_pronto_pagos) );
			}

			function resetear_campos_descuento_pronto_pago()
			{
				$('#descuento_pronto_pago_id').val('');
				$('#valor_descuento_pronto_pago').val('');
				$('#observacion_descuento_pronto_pago').val('');
			}

			function validar_requeridos()
			{
				if( $('#descuento_pronto_pago_id').val() == '' )
				{
					alert('Debe seleccionar un tipo de descuento.')
					$('#descuento_pronto_pago_id').focus();
					return false;
				}

				if( $('#valor_descuento_pronto_pago').val() == '' || $('#valor_descuento_pronto_pago').val() == '0' )
				{
					alert('Debe ingresar un valor de descuento.')
					$('#valor_descuento_pronto_pago').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_descuento_pronto_pago') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#descuento_pronto_pago_id').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_descuento_pronto_pago">'+ $('#valor_descuento_pronto_pago').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#descuento_pronto_pago_id option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#observacion_descuento_pronto_pago').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_descuento_pronto_pago').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_descuento_pronto_pago'><i class='fa fa-btn fa-trash'></i></button>";
				celdas[ num_celda ] = '<td>' + btn_borrar + '</td>';

				var cantidad_celdas = celdas.length;
				var string_celdas = '';
				for (var i = 0; i < cantidad_celdas; i++)
				{
					string_celdas = string_celdas + celdas[i];
				}

				return string_celdas;
			}

		});
	</script>
@endsection