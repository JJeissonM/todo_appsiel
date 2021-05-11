<!-- Formulario control cheque -->
<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" id="div_control_cheques" style="display: block; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">
			<h5>Registros de pagos en Cheques</h5>
			<hr>
			<div class="row">
				<div class="col-md-6" >
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<h6 style="width: 100%; text-align: center;">FORMULARIO</h6>
						<hr>
						<!-- <div class="row">
							<div class="col-md-12">
								<div class="row" style="padding:5px;">
									{ { Form::bsRadioBtn('tipo_cheque', 'cheque_de_tercero', 'Tipo cheque', '{"cheque_propio":"Cheque propio","cheque_de_tercero":"Cheque de Tercero"}', []) }}
								</div>
							</div>
						</div>-->
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('tipo_operacion_id_cheque', null, 'Tipo de operación', [ ''=>'', 'Recaudo cartera'=>'Recaudo cartera (CxC)','Anticipo'=>'Anticipo cliente (CxC a favor)', 'Otros recaudos'=>'Otros recaudos','Prestamo financiero'=>'Prestamo financiero (CxP)'], []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('teso_motivo_id_cheque', null, 'Motivo', [], []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('caja_id_cheque', null, 'Caja', $cajas, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'detalle_cheque', null, 'Detalle', []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsFecha( 'fecha_emision', date('Y-m-d'), 'Fecha emisión', [], []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsFecha( 'fecha_cobro', date('Y-m-d'), 'Fecha cobro', [], []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'numero_cheque', null, 'Número de cheque', []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'referencia_cheque', null, 'Referencia	', []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('entidad_financiera_id', null, 'Entidad financiera', $entidades_financieras, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'valor_cheque', null, 'Valor', []) }}
								</div>
							</div>
						</div>
						<p style="text-align: center;">
							<button class="btn btn-primary" id="btn_agregar_cheque"> Agregar </button>
						</p>
					</div>
					<br><br>
				</div>
				<div class="col-md-6">
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<div class="row">
							<div class="col-md-12">
								<h6>Cheques ingresados</h6>
								<hr>
								<table class="table table-striped table-bordered" id="tabla_registros_cheques">
									<thead>
										<tr>
											<th style="display: none;">tipo_operacion_id_cheque</th>
											<th style="display: none;">teso_motivo_id_cheque</th>
											<th style="display: none;">detalle_cheque</th>
											<th style="display: none;">caja_id_cheque</th>
											<th style="display: none;">entidad_financiera_id</th>
											<th style="display: none;">valor_cheque</th>
											<th data-override="fecha_emision">F. Emisión</th>
											<th data-override="fecha_cobro">F. cobro</th>
											<th data-override="numero_cheque">Núm.</th>
											<th data-override="referencia_cheque">Ref.</th>
											<th>Banco</th>
											<th>Valor</th>
											<th></th>
										</tr>
									</thead>
									<tbody>				
									</tbody>
									<tfoot>
										<tr>
											<td colspan="5">&nbsp;</td>
											<td id="valor_total_cheques" align="right">$ 0</td>
											<td><input type="hidden" name="input_valor_total_cheques" id="input_valor_total_cheques" value="0"></td>
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

@section('scripts9')
	<script type="text/javascript">

		$(document).ready(function(){

			$(document).on('change', '#tipo_operacion_id_cheque', function(event) 
			{
				$('#teso_motivo_id_cheque').html('<option value=""></option>');
				
				if ( $(this).val() == '') { return false; }
				
				$('#div_cargando').show();
				
				var url = "{{url('tesoreria/ajax_get_motivos')}}" + '/' + $('#tipo_operacion_id_cheque').val();
				
				$.get( url, function( datos ) {
			        $('#teso_motivo_id_cheque').html(datos);
			        $('#div_cargando').hide();
				});
			});

			$(document).on('click', '#btn_agregar_cheque', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_cheques').find('tbody:last').append('<tr class="linea_registro_cheque">' + string_fila + '</tr>');
				
				hay_cheques++;

				calcular_totales_cheques( $('#tipo_operacion_id_cheque').val(), parseFloat( $('#valor_cheque').val() ) );

				resetear_campos_cheque();

				$('#numero_cheque').focus();
			});

			$(document).on('click', '.btn_eliminar_cheque', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_cheques--;

				calcular_totales_cheques( fila.find('td.tipo_operacion_id_cheque').text(), parseFloat( fila.find('.valor_cheque').text() ) * -1 );

			});

			$('#valor_cheque').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_cheque').focus();
				}
			});

			$('#numero_cheque').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#referencia_cheque').focus();
				}
			});

			$('#referencia_cheque').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#valor_cheque').focus();
				}
			});

			function calcular_totales_cheques( tipo_operacion_id, valor_linea )
			{
				var valor_total_cheques = 0.0;
				$('.linea_registro_cheque').each(function()
				{
				    valor_total_cheques += parseFloat( $(this).find('.valor_cheque').text() );
				});

				$('#valor_total_cheques').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_cheques.toFixed(2) ) );
				$('#valor_total_cheques2').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_cheques.toFixed(2) ) );
				$('#input_valor_total_cheques').val(valor_total_cheques);


				if ( tipo_operacion_id != 'Recaudo cartera' )
				{
					var actual_valor_total_otras_operaciones = parseFloat( $('#input_valor_total_otras_operaciones').val() );

					var nuevo_valor_total_otras_operaciones = actual_valor_total_otras_operaciones + valor_linea;

					$('#valor_total_otras_operaciones').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_total_otras_operaciones.toFixed(2) ) );
					$('#input_valor_total_otras_operaciones').val( nuevo_valor_total_otras_operaciones );

					$.fn.actualizar_total_resumen_operaciones( valor_linea );
			    }

			    $.fn.actualizar_total_resumen_medios_pagos( valor_linea );

			    var lineas_registros_cheques = $('#tabla_registros_cheques').tableToJSON();
				$('#lineas_registros_cheques').val( JSON.stringify(lineas_registros_cheques) );
			}

			function resetear_campos_cheque()
			{
				$('#detalle_cheque').val('');
				$('#valor_cheque').val('');
				$('#numero_cheque').val('');
				$('#referencia_cheque').val('');
				$('#entidad_financiera_id').val('');
			}

			function validar_requeridos()
			{
				if( $('#caja_id_cheque').val() == '' )
				{
					alert('Debe seleccionar una Caja.')
					$('#caja_id_cheque').focus();
					return false;
				}

				if( $('#numero_cheque').val() == '' )
				{
					alert('El número del cheque es obligatorio.')
					$('#numero_cheque').focus();
					return false;
				}

				if( $('#valor_cheque').val() == '' || $('#valor_cheque').val() == '0' )
				{
					alert('Debe ingresar un valor válido para el cheque.')
					$('#valor_cheque').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_cheque') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;" class="tipo_operacion_id_cheque">'+ $('#tipo_operacion_id_cheque').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#teso_motivo_id_cheque').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#caja_id_cheque').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#detalle_cheque').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#entidad_financiera_id').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_cheque">'+ $('#valor_cheque').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#fecha_emision').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#fecha_cobro').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#numero_cheque').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#referencia_cheque').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#entidad_financiera_id option:selected').text() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_cheque').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_cheque'><i class='fa fa-btn fa-trash'></i></button>";
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