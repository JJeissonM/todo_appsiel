<!-- Formulario forma de pago Tarjeta Crédito -->
<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" style="display: block; border: 1px solid #ddd; border-radius: 4px;">

			<h5>Registros de pagos por Tarjeta Crédito</h5>
			<hr>

			<div class="row">
				<div class="col-md-6" >
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<h6 style="width: 100%; text-align: center;">FORMULARIO</h6>
						<hr>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('tipo_operacion_id_tarjeta_debito', null, 'Tipo Ope.', $tipos_operaciones, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('teso_motivo_id_tarjeta_debito', null, 'Motivo', [], []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'numero_comprobante_tarjeta_debito', null, 'N° Comp.', []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('banco_id_tarjeta_debito', null, 'Cta. Bancaria', $cuentas_bancarias, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'valor_tarjeta_debito', null, 'Valor', []) }}
								</div>
							</div>
						</div>
						<p style="text-align: center;">
							<button class="btn btn-primary" id="btn_agregar_tarjeta_debito"> Agregar </button>
						</p>
					</div>
					<br><br>
				</div>
				<div class="col-md-6">
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<div class="row">
							<div class="col-md-12">
								<h6 style="width: 100%; text-align: center;">PAGOS INGRESADAS</h6>
								<hr>
								<table class="table table-striped table-bordered" id="tabla_registros_tarjeta_debito">
									<thead>
										<tr>
											<th style="display: none;">tipo_operacion_id_tarjeta_debito</th>
											<th style="display: none;">teso_motivo_id_tarjeta_debito</th>
											<th style="display: none;">banco_id_tarjeta_debito</th>
											<th style="display: none;">valor_tarjeta_debito</th>
											<th>Operación</th>
											<th>Motivo</th>
											<th data-override="numero_comprobante_tarjeta_debito">Núm. comprobante</th>
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
											<td id="valor_total_tarjeta_debito" align="right">$ 0</td>
											<td><input type="hidden" name="input_valor_total_tarjeta_debito" id="input_valor_total_tarjeta_debito" value="0"></td>
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

@section('scripts6')
	<script type="text/javascript">

		$(document).ready(function(){

	
			$(document).on('change', '#tipo_operacion_id_tarjeta_debito', function(event) 
			{
				$('#teso_motivo_id_tarjeta_debito').html('<option value=""></option>');
				
				if ( $(this).val() == '') { return false; }
				
				$('#div_cargando').show();
				
				var url = "{{url('tesoreria/ajax_get_motivos')}}" + '/' + $('#tipo_operacion_id_tarjeta_debito').val();
				
				$.get( url, function( datos ) {
			        $('#teso_motivo_id_tarjeta_debito').html(datos);
			        $('#div_cargando').hide();
				});
			});

			$(document).on('click', '#btn_agregar_tarjeta_debito', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_tarjeta_debito').find('tbody:last').append('<tr class="linea_registro_tarjeta_debito">' + string_fila + '</tr>');
				
				hay_tarjeta_debito++;

				calcular_totales_tarjeta_debito( $('#tipo_operacion_id_tarjeta_debito').val(), parseFloat( $('#valor_tarjeta_debito').val() ) );

				resetear_campos_tarjeta_debito();

				$('#tipo_operacion_id_tarjeta_debito').focus();
			});

			$(document).on('click', '.btn_eliminar_tarjeta_debito', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_tarjeta_debito--;

				calcular_totales_tarjeta_debito( fila.find('td.tipo_operacion_id_tarjeta_debito').text(), parseFloat( fila.find('.valor_tarjeta_debito').text() ) * -1 );

			});

			$('#valor_tarjeta_debito').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_tarjeta_debito').focus();
				}
			});

			function calcular_totales_tarjeta_debito( tipo_operacion_id, valor_linea )
			{
				var valor_total_tarjeta_debito = 0.0;
				$('.linea_registro_tarjeta_debito').each(function()
				{
				    valor_total_tarjeta_debito += parseFloat( $(this).find('.valor_tarjeta_debito').text() );
				});

				$('#valor_total_tarjeta_debito').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_tarjeta_debito.toFixed(2) ) );
				$('#input_valor_total_tarjeta_debito').val(valor_total_tarjeta_debito);

				var actual_valor_total_cuentas_bancarias = parseFloat( $('#input_valor_total_cuentas_bancarias').val() );
				var nuevo_valor_total_cuentas_bancarias = actual_valor_total_cuentas_bancarias + valor_linea;
				$('#valor_total_cuentas_bancarias').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_total_cuentas_bancarias.toFixed(2) ) );
				$('#input_valor_total_cuentas_bancarias').val( nuevo_valor_total_cuentas_bancarias );

				if ( tipo_operacion_id != 'Recaudo cartera' && tipo_operacion_id != 'Pago proveedores' )
				{
					var actual_valor_total_otras_operaciones = parseFloat( $('#input_valor_total_otras_operaciones').val() );

					var nuevo_valor_total_otras_operaciones = actual_valor_total_otras_operaciones + valor_linea;

					$('#valor_total_otras_operaciones').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_total_otras_operaciones.toFixed(2) ) );
					$('#input_valor_total_otras_operaciones').val( nuevo_valor_total_otras_operaciones );

					$.fn.actualizar_total_resumen_operaciones( valor_linea );
			    }

			    $.fn.actualizar_total_resumen_medios_pagos( valor_linea );

			    var lineas_registros_tarjeta_debito = $('#tabla_registros_tarjeta_debito').tableToJSON();
				$('#lineas_registros_tarjeta_debito').val( JSON.stringify(lineas_registros_tarjeta_debito) );
			}

			function resetear_campos_tarjeta_debito()
			{
				$('#tipo_operacion_id_tarjeta_debito').val('');
				$('#teso_motivo_id_tarjeta_debito').val('');
				$('#numero_comprobante_tarjeta_debito').val('');
				$('#banco_id_tarjeta_debito').val('');
				$('#valor_tarjeta_debito').val('');
			}

			function validar_requeridos()
			{
				if( $('#tipo_operacion_id_tarjeta_debito').val() == '' || $('#teso_motivo_id_tarjeta_debito').val() == '' || $('#banco_id_tarjeta_debito').val() == '' )
				{
					alert('Faltan datos por llenar.')
					$('#tipo_operacion_id_tarjeta_debito').focus();
					return false;
				}

				if( $('#valor_tarjeta_debito').val() == '' || $('#valor_tarjeta_debito').val() == '0' )
				{
					alert('Faltan datos por llenar.')
					$('#valor_tarjeta_debito').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_tarjeta_debito') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;" class="tipo_operacion_id_tarjeta_debito">'+ $('#tipo_operacion_id_tarjeta_debito').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#teso_motivo_id_tarjeta_debito').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#banco_id_tarjeta_debito').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_tarjeta_debito">'+ $('#valor_tarjeta_debito').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#tipo_operacion_id_tarjeta_debito option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#teso_motivo_id_tarjeta_debito option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#numero_comprobante_tarjeta_debito').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#banco_id_tarjeta_debito option:selected').text() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_tarjeta_debito').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_tarjeta_debito'><i class='fa fa-btn fa-trash'></i></button>";
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