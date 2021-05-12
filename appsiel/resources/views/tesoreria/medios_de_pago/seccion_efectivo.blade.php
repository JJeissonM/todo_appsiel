<!-- Formulario forma de pago Efectivo -->
<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" style="display: block; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">

			<h5>Registros de pagos en Efectivo</h5>
			<hr>

			<div class="row">
				<div class="col-md-6" >
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<h6 style="width: 100%; text-align: center;">FORMULARIO</h6>
						<hr>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('tipo_operacion_id_efectivo', null, 'Tipo de operación', $tipos_operaciones, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('teso_motivo_id_efectivo', null, 'Motivo', [], []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('caja_id_efectivo', null, 'Caja', $cajas, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'valor_efectivo', null, 'Valor', []) }}
								</div>
							</div>
						</div>
						<p style="text-align: center;">
							<button class="btn btn-primary" id="btn_agregar_efectivo"> Agregar </button>
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
								<table class="table table-striped table-bordered" id="tabla_registros_efectivo">
									<thead>
										<tr>
											<th style="display: none;">tipo_operacion_id_efectivo</th>
											<th style="display: none;">teso_motivo_id_efectivo</th>
											<th style="display: none;">caja_id_efectivo</th>
											<th style="display: none;">valor_efectivo</th>
											<th>Operación</th>
											<th>Motivo</th>
											<th>Caja</th>
											<th>Valor</th>
											<th></th>
										</tr>
									</thead>
									<tbody>				
									</tbody>
									<tfoot>
										<tr>
											<td colspan="3">&nbsp;</td>
											<td id="valor_total_efectivo" align="right">$ 0</td>
											<td><input type="hidden" name="input_valor_total_efectivo" id="input_valor_total_efectivo" value="0"></td>
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

@section('scripts4')
	<script type="text/javascript">

		$(document).ready(function(){

	
			$(document).on('change', '#tipo_operacion_id_efectivo', function(event) 
			{
				$('#teso_motivo_id_efectivo').html('<option value=""></option>');
				
				if ( $(this).val() == '') { return false; }
				
				$('#div_cargando').show();
				
				var url = "{{url('tesoreria/ajax_get_motivos')}}" + '/' + $('#tipo_operacion_id_efectivo').val();
				
				$.get( url, function( datos ) {
			        $('#teso_motivo_id_efectivo').html(datos);
			        $('#div_cargando').hide();
				});
			});

			$(document).on('click', '#btn_agregar_efectivo', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_efectivo').find('tbody:last').append('<tr class="linea_registro_efectivo">' + string_fila + '</tr>');
				
				hay_efectivo++;

				calcular_totales_efectivo( $('#tipo_operacion_id_efectivo').val(), parseFloat( $('#valor_efectivo').val() ) );

				resetear_campos_efectivo();

				$('#tipo_operacion_id_efectivo').focus();
			});

			$(document).on('click', '.btn_eliminar_efectivo', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_efectivo--;

				calcular_totales_efectivo( fila.find('td.tipo_operacion_id_efectivo').text(), parseFloat( fila.find('.valor_efectivo').text() ) * -1 );

			});

			$('#valor_efectivo').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_efectivo').focus();
				}
			});

			function calcular_totales_efectivo( tipo_operacion_id, valor_linea )
			{
				var valor_total_efectivo = 0.0;
				$('.linea_registro_efectivo').each(function()
				{
				    valor_total_efectivo += parseFloat( $(this).find('.valor_efectivo').text() );
				});

				$('#valor_total_efectivo').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_efectivo.toFixed(2) ) );
				$('#input_valor_total_efectivo').val(valor_total_efectivo);

				// Para la tabla de resumen
				$('#valor_total_efectivo2').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_efectivo.toFixed(2) ) );

				if ( tipo_operacion_id != 'Recaudo cartera' && tipo_operacion_id != 'Pago proveedores' )
				{
					var actual_valor_total_otras_operaciones = parseFloat( $('#input_valor_total_otras_operaciones').val() );

					var nuevo_valor_total_otras_operaciones = actual_valor_total_otras_operaciones + valor_linea;

					$('#valor_total_otras_operaciones').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_total_otras_operaciones.toFixed(2) ) );
					$('#input_valor_total_otras_operaciones').val( nuevo_valor_total_otras_operaciones );

					$.fn.actualizar_total_resumen_operaciones( valor_linea );
			    }

			    $.fn.actualizar_total_resumen_medios_pagos( valor_linea );

			    var lineas_registros_efectivo = $('#tabla_registros_efectivo').tableToJSON();
				$('#lineas_registros_efectivo').val( JSON.stringify(lineas_registros_efectivo) );
			}

			function resetear_campos_efectivo()
			{
				$('#tipo_operacion_id_efectivo').val('');
				$('#teso_motivo_id_efectivo').val('');
				$('#caja_id_efectivo').val('');
				$('#valor_efectivo').val('');
			}

			function validar_requeridos()
			{
				if( $('#tipo_operacion_id_efectivo').val() == '' || $('#teso_motivo_id_efectivo').val() == '' || $('#caja_id_efectivo').val() == '' )
				{
					alert('Faltan datos por llenar.')
					$('#tipo_operacion_id_efectivo').focus();
					return false;
				}

				if( $('#valor_efectivo').val() == '' || $('#valor_efectivo').val() == '0' )
				{
					alert('Faltan datos por llenar.')
					$('#valor_efectivo').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_efectivo') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;" class="tipo_operacion_id_efectivo">'+ $('#tipo_operacion_id_efectivo').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#teso_motivo_id_efectivo').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#caja_id_efectivo').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_efectivo">'+ $('#valor_efectivo').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#tipo_operacion_id_efectivo option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#teso_motivo_id_efectivo option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#caja_id_efectivo option:selected').text() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_efectivo').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_efectivo'><i class='fa fa-btn fa-trash'></i></button>";
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