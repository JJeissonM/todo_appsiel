<!-- Formulario forma de pago Tarjeta Débito -->
<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" style="display: block; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">

			<h5>Registros de pagos por Tarjeta Débito</h5>
			<hr>

			<div class="row">
				<div class="col-md-6" >
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<h6 style="width: 100%; text-align: center;">FORMULARIO</h6>
						<hr>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('tipo_operacion_id_tarjeta_credito', null, 'Tipo de operación', [''=>'','Recaudo cartera'=>'Recaudo cartera (CxC)','Anticipo'=>'Anticipo cliente (CxC a favor)','Otros recaudos'=>'Otros recaudos','Prestamo financiero'=>'Prestamo financiero (CxP)'], []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('teso_motivo_id_tarjeta_credito', null, 'Motivo', [], []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'numero_comprobante_tarjeta_credito', null, 'Núm. comprobante', []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('banco_id_tarjeta_credito', null, 'Cta. Bancaria', $cuentas_bancarias, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'valor_tarjeta_credito', null, 'Valor', []) }}
								</div>
							</div>
						</div>
						<p style="text-align: center;">
							<button class="btn btn-primary" id="btn_agregar_tarjeta_credito"> Agregar </button>
						</p>
					</div>
					<br><br>
				</div>
				<div class="col-md-6">
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<div class="row">
							<div class="col-md-12">
								<h6 style="width: 100%; text-align: center;">LÍNEAS INGRESADAS</h6>
								<hr>
								<table class="table table-striped table-bordered" id="tabla_registros_tarjeta_credito">
									<thead>
										<tr>
											<th style="display: none;">tipo_operacion_id_tarjeta_credito</th>
											<th style="display: none;">teso_motivo_id_tarjeta_credito</th>
											<th style="display: none;">banco_id_tarjeta_credito</th>
											<th style="display: none;">valor_tarjeta_credito</th>
											<th>Operación</th>
											<th>Motivo</th>
											<th data-override="numero_comprobante_tarjeta_credito">Núm. comprobante</th>
											<th>Caja</th>
											<th>Valor</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="4">&nbsp;</td>
											<td id="valor_total_tarjeta_credito" align="right">$ 0</td>
											<td><input type="hidden" name="input_valor_total_tarjeta_credito" id="input_valor_total_tarjeta_credito" value="0"></td>
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

@section('scripts7')
	<script type="text/javascript">

		$(document).ready(function(){

	
			$(document).on('change', '#tipo_operacion_id_tarjeta_credito', function(event) 
			{
				$('#teso_motivo_id_tarjeta_credito').html('<option value=""></option>');
				
				if ( $(this).val() == '') { return false; }
				
				$('#div_cargando').show();
				
				var url = "{{url('tesoreria/ajax_get_motivos')}}" + '/' + $('#tipo_operacion_id_tarjeta_credito').val();
				
				$.get( url, function( datos ) {
			        $('#teso_motivo_id_tarjeta_credito').html(datos);
			        $('#div_cargando').hide();
				});
			});

			$(document).on('click', '#btn_agregar_tarjeta_credito', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_tarjeta_credito').find('tbody:last').append('<tr class="linea_registro_tarjeta_credito">' + string_fila + '</tr>');
				
				hay_tarjeta_credito++;

				calcular_totales_tarjeta_credito( $('#tipo_operacion_id_tarjeta_credito').val(), parseFloat( $('#valor_tarjeta_credito').val() ) );

				resetear_campos_tarjeta_credito();

				$('#tipo_operacion_id_tarjeta_credito').focus();
			});

			$(document).on('click', '.btn_eliminar_tarjeta_credito', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_tarjeta_credito--;

				calcular_totales_tarjeta_credito( fila.find('td.tipo_operacion_id_tarjeta_credito').text(), parseFloat( fila.find('.valor_tarjeta_credito').text() ) * -1 );

			});

			$('#valor_tarjeta_credito').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_tarjeta_credito').focus();
				}
			});

			function calcular_totales_tarjeta_credito( tipo_operacion_id, valor_linea )
			{
				var valor_total_tarjeta_credito = 0.0;
				$('.linea_registro_tarjeta_credito').each(function()
				{
				    valor_total_tarjeta_credito += parseFloat( $(this).find('.valor_tarjeta_credito').text() );
				});

				$('#valor_total_tarjeta_credito').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_tarjeta_credito.toFixed(2) ) );
				$('#input_valor_total_tarjeta_credito').val(valor_total_tarjeta_credito);

				var actual_valor_total_cuentas_bancarias = parseFloat( $('#input_valor_total_cuentas_bancarias').val() );
				var nuevo_valor_total_cuentas_bancarias = actual_valor_total_cuentas_bancarias + valor_linea;
				$('#valor_total_cuentas_bancarias').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_total_cuentas_bancarias.toFixed(2) ) );
				$('#input_valor_total_cuentas_bancarias').val( nuevo_valor_total_cuentas_bancarias );

				if ( tipo_operacion_id != 'Recaudo cartera' )
				{
					var actual_valor_total_otras_operaciones = parseFloat( $('#input_valor_total_otras_operaciones').val() );

					var nuevo_valor_total_otras_operaciones = actual_valor_total_otras_operaciones + valor_linea;

					$('#valor_total_otras_operaciones').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_total_otras_operaciones.toFixed(2) ) );
					$('#input_valor_total_otras_operaciones').val( nuevo_valor_total_otras_operaciones );

					$.fn.actualizar_total_resumen_operaciones( valor_linea );
			    }

			    $.fn.actualizar_total_resumen_medios_pagos( valor_linea );

			}

			function resetear_campos_tarjeta_credito()
			{
				$('#tipo_operacion_id_tarjeta_credito').val('');
				$('#teso_motivo_id_tarjeta_credito').val('');
				$('#numero_comprobante_tarjeta_credito').val('');
				$('#banco_id_tarjeta_credito').val('');
				$('#valor_tarjeta_credito').val('');
			}

			function validar_requeridos()
			{
				if( $('#tipo_operacion_id_tarjeta_credito').val() == '' || $('#teso_motivo_id_tarjeta_credito').val() == '' || $('#banco_id_tarjeta_credito').val() == '' )
				{
					alert('Faltan datos por llenar.')
					$('#tipo_operacion_id_tarjeta_credito').focus();
					return false;
				}

				if( $('#valor_tarjeta_credito').val() == '' || $('#valor_tarjeta_credito').val() == '0' )
				{
					alert('Faltan datos por llenar.')
					$('#valor_tarjeta_credito').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_tarjeta_credito') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;" class="tipo_operacion_id_tarjeta_credito">'+ $('#tipo_operacion_id_tarjeta_credito').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#teso_motivo_id_tarjeta_credito').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#banco_id_tarjeta_credito').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_tarjeta_credito">'+ $('#valor_tarjeta_credito').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#tipo_operacion_id_tarjeta_credito option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#teso_motivo_id_tarjeta_credito option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#numero_comprobante_tarjeta_credito').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#banco_id_tarjeta_credito option:selected').text() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_tarjeta_credito').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_tarjeta_credito'><i class='fa fa-btn fa-trash'></i></button>";
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