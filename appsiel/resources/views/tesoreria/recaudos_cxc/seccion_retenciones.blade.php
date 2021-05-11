<!-- Formulario forma de pago Efectivo -->
<br>
<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" style="display: block; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">
			<h5>Registros de Retenciones</h5>
			<hr>
			<div class="row">
				<div class="col-md-6" >
					<div style="border-radius: 4px; border: solid 1px #848484; padding: 5px;">
						<h6 style="width: 100%; text-align: center;">FORMULARIO</h6>
						<hr>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect('contab_retencion_id', null, 'Tipo de retención', $retenciones, []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'valor_retencion', null, 'Valor', []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsFecha( 'fecha_certificado', date('Y-m-d'), 'Fecha certificado', [], []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsFecha( 'fecha_recepcion_certificado', date('Y-m-d'), 'Fecha recepción certificado', [], []) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'numero_certificado', null, 'Número certificado', []) }}
								</div>
							</div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsText( 'numero_doc_identidad_agente_retencion', null, config('configuracion.tipo_identificador') . ' agente de retención', []) }}
								</div>
							</div>
						</div>
						<p style="text-align: center;">
							<button class="btn btn-primary" id="btn_agregar_retencion"> Agregar </button>
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
								<table class="table table-striped table-bordered" id="tabla_registros_retenciones">
									<thead>
										<tr>
											<th style="display: none;">contab_retencion_id</th>
											<th style="display: none;">valor_retencion</th>
											<th>Tipo Retención</th>
											<th data-override="fecha_certificado">F. certificado</th>
											<th data-override="fecha_recepcion_certificado">F. recepción cert.</th>
											<th data-override="numero_certificado">Núm. cert.</th>
											<th data-override="numero_doc_identidad_agente_retencion"> {{ config('configuracion.tipo_identificador') }} agente Ret.</th>
											<th>Valor</th>
											<th></th>
										</tr>
									</thead>
									<tbody>				
									</tbody>
									<tfoot>
										<tr>
											<td colspan="5">&nbsp;</td>
											<td id="valor_total_retencion" align="right">$ 0</td>
											<td><input type="hidden" name="input_valor_total_retencion" id="input_valor_total_retencion" value="0"></td>
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

@section('scripts2')
	<script type="text/javascript">

		$(document).ready(function(){

			$(document).on('click', '#btn_agregar_retencion', function(event) 
			{
				event.preventDefault();

				if( !validar_requeridos() )
				{
					return false;
				}

				var string_fila = generar_string_celdas();

				$('#tabla_registros_retenciones').find('tbody:last').append('<tr class="linea_registro_retencion">' + string_fila + '</tr>');
				
				hay_retencion++;

				calcular_totales_retencion( parseFloat( $('#valor_retencion').val() ) );

				resetear_campos_retencion();

				$('#tipo_operacion_id_retencion').focus();
			});

			$(document).on('click', '.btn_eliminar_retencion', function(event) 
			{
				var fila = $(this).closest("tr");

				fila.remove();
				
				hay_retencion--;

				calcular_totales_retencion( parseFloat( fila.find('.valor_retencion').text() ) * -1 );

			});

			$('#valor_retencion').on('keyup', function () {
				var codigo_tecla_presionada = event.which || event.keyCode;

				if( codigo_tecla_presionada == 13 )
				{
					$('#btn_agregar_retencion').focus();
				}
			});

			function calcular_totales_retencion( valor_linea )
			{
				var valor_total_retencion = 0.0;
				$('.linea_registro_retencion').each(function()
				{
				    valor_total_retencion += parseFloat( $(this).find('.valor_retencion').text() );
				});

				$('#valor_total_retencion').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_retencion.toFixed(2) ) );
				$('#valor_total_retencion2').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_retencion.toFixed(2) ) );
				$('#input_valor_total_retencion').val(valor_total_retencion);

				$.fn.actualizar_total_resumen_medios_pagos( valor_linea );

				var lineas_registros_retenciones = $('#tabla_registros_retenciones').tableToJSON();
				$('#lineas_registros_retenciones').val( JSON.stringify(lineas_registros_retenciones) );
			}

			function resetear_campos_retencion()
			{
				$('#contab_retencion_id').val('');
				$('#numero_certificado').val('');
				$('#numero_doc_identidad_agente_retencion').val('');
				$('#valor_retencion').val('');
			}

			function validar_requeridos()
			{
				if( $('#contab_retencion_id').val() == '' || $('#numero_certificado').val() == '' || $('#numero_doc_identidad_agente_retencion').val() == '' )
				{
					alert('Faltan datos por llenar.')
					$('#contab_retencion_id').focus();
					return false;
				}

				if( $('#valor_retencion').val() == '' || $('#valor_retencion').val() == '0' )
				{
					alert('Faltan datos por llenar.')
					$('#valor_retencion').focus();
					return false;
				}

				if( !validar_input_numerico( $('#valor_retencion') ) )
				{
					return false;
				}

				return true;
			}

			function generar_string_celdas()
			{
				var celdas = [];
				var num_celda = 0;

				celdas[ num_celda ] = '<td style="display: none;">'+ $('#contab_retencion_id').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td style="display: none;"> <div class="valor_retencion">'+ $('#valor_retencion').val() +'</div></td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#contab_retencion_id option:selected').text() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#fecha_certificado').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#fecha_recepcion_certificado').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#numero_certificado').val() +'</td>';
				
				num_celda++;
				
				celdas[ num_celda ] = '<td>'+ $('#numero_doc_identidad_agente_retencion').val() +'</td>';
				
				num_celda++;

				celdas[ num_celda ] = '<td align="right"> '+ '$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_retencion').val() ) + '</td>';
				
				num_celda++;

				var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_retencion'><i class='fa fa-btn fa-trash'></i></button>";
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