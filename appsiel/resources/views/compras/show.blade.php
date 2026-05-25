<?php

use Illuminate\Support\Facades\Input;

$variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion');
?>

@extends('transaccion.show')

@section('botones_acciones')
	@if($doc_encabezado->estado != 'Anulado')
        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
		@if($mostrar_boton_confirmar)
			<button class="btn-gmail" id="btn_confirmar_documento" title="Confirmar"><i class="fa fa-check"></i></button>
		@endif
		<a href="{{ url('tesoreria/pagos_cxp/create?id=3&id_modelo=150&id_transaccion=33') }}" target="_blank" class="btn-gmail" title="Hacer abono"><i class="fa fa-btn fa-money"></i></a>	
        @if(!$docs_relacionados[1])
        	<!-- WARNING: Solo se hacen notas para facturas con una sola éntrada de almacén -->
        	<a class="btn-gmail" href="{{ url('compras_notas_credito/create?factura_id='.$id.'&id='.Input::get('id').'&id_modelo=166&id_transaccion=36') }}" title="Nota crédito"><i class="fa fa-file-text"></i></a>
			
        	<a class="btn-gmail" href="{{ url('compras_notas_credito_valor/create?factura_id='.$id.'&id='.Input::get('id').'&id_modelo=332&id_transaccion=61') }}" title="Nota crédito por valor"><i class="fa fa-file-text-o"></i></a>
        @endif
		
        @can('compras_recontabilizar_un_documento')
        	<a class="btn-gmail" href="{{ url( 'compras_recontabilizar_un_documento/'.$id.$variables_url ) }}" title="Recontabilizar"><i class="fa fa-cog"></i></a>
        @endcan
    @endif
	@include('compras.doc_soporte.acciones_doc_soporte_electronico')
@endsection

@section('botones_imprimir_email')
Formato: {{ Form::select('formato_impresion_id',['pos'=>'POS','estandar'=>'Estándar','estandar2'=>'Estándar v2'],null, [ 'id' =>'formato_impresion_id' ]) }}
{{ Form::bsBtnPrint( 'compras_imprimir/'.$id.$variables_url.'&formato_impresion_id=pos' ) }}
@endsection

@section('botones_anterior_siguiente')
{!! $botones_anterior_siguiente->dibujar( 'compras/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
<br />
<b>Entrada(s) almacén: </b> {!! $docs_relacionados[0] !!}
<br />
<b>Orden de compras: &nbsp;&nbsp;</b> {{ $doc_encabezado->orden_compras }}
@endsection

@section('filas_adicionales_encabezado')
<tr>
	<td style="border: solid 1px #ddd;">
		<b>Proveedor:</b> {{ $doc_encabezado->tercero_nombre_completo }}
		<br />
		<b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
		@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }} @else {{ $doc_encabezado->numero_identificacion}} @endif
		<br />
		<b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
		<br />
		<b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
	</td>
	<td style="border: solid 1px #ddd;">
		<b>Fact. del proveedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->doc_proveedor_prefijo }} - {{ $doc_encabezado->doc_proveedor_consecutivo }}
		<br />
		<b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
		<br />
		<b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}

	</td>
</tr>
<tr>
	<td colspan="2" style="border: solid 1px #ddd;">
		<b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
	</td>
</tr>
@endsection

@section('div_advertencia_anulacion')
{{ Form::open(['url'=>'compras_anular_factura', 'id'=>'form_anular']) }}
<div class="alert alert-warning" style="display: none;">
	<a href="#" id="close" class="close">&times;</a>
	<strong>Advertencia!</strong>
	<br>
	Al anular el documento se eliminan los registros de la Entrada de Almacén, Cuentas por Pagar y el movimiento contable relacionado.
	<br>
	¿Desea eliminar también la(s) entrada(s) de almacén?
	<label class="radio-inline"> <input type="radio" name="anular_entrada_almacen" value="1" id="opcion1">Si</label>
	<label class="radio-inline"> <input type="radio" name="anular_entrada_almacen" value="0" id="opcion2">No</label>
	<br>
	Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="#" id="enlace_anular" data-url="{{ url('compras_anular_factura/'.$id.$variables_url ) }}"> Anular </a> </small>
</div>

{{ Form::hidden('url_id', Input::get('id')) }}
{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

{{ Form::hidden( 'factura_id', $id ) }}

	{{ Form::close() }}

	@if($mostrar_boton_confirmar)
		{{ Form::open(['url' => 'compras_confirmar_factura', 'id' => 'form_confirmar_documento']) }}
			{{ Form::hidden('url_id', Input::get('id')) }}
			{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
			{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
			{{ Form::hidden('factura_id', $id) }}
			{{ Form::hidden('lineas_registros_medios_recaudo', '0', ['id' => 'lineas_registros_medios_recaudo_confirmacion']) }}
		{{ Form::close() }}

		<div class="modal fade" id="modal_confirmar_documento" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Confirmar documento</h4>
					</div>
					<div class="modal-body">
						<div class="alert alert-warning">
							<strong>Advertencia!</strong>
							<br>
							Se validará la entrada de almacén vinculada o se generará una nueva si no existe; además se crearán los movimientos de compras, las contabilizaciones y los registros de pago del documento actual.
							<br><br>
							Esta operación debe ejecutarse una sola vez.
						</div>
						@if($factura_es_contado)
							<div class="panel panel-default">
								<div class="panel-heading">
									<strong>Medio de pago</strong>
								</div>
								<div class="panel-body">
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Medio</label>
												<select class="form-control" id="confirmar_teso_medio_recaudo_id">
													@foreach($medios_recaudo_confirmacion as $value => $label)
														@if($value !== '')
															<option value="{{ $value }}">{{ $label }}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Motivo</label>
												<select class="form-control" id="confirmar_teso_motivo_id">
													@foreach($motivos_pago_confirmacion as $value => $label)
														<option value="{{ $value }}">{{ $label }}</option>
													@endforeach
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-6" id="confirmar_div_caja">
											<div class="form-group">
												<label>Caja</label>
												<select class="form-control" id="confirmar_teso_caja_id">
													@foreach($cajas_confirmacion as $value => $label)
														<option value="{{ $value }}">{{ $label }}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-sm-6" id="confirmar_div_cuenta_bancaria" style="display:none;">
											<div class="form-group">
												<label>Cuenta bancaria</label>
												<select class="form-control" id="confirmar_teso_cuenta_bancaria_id">
													@foreach($cuentas_bancarias_confirmacion as $value => $label)
														<option value="{{ $value }}">{{ $label }}</option>
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label>Valor</label>
												<input type="text" class="form-control" id="confirmar_valor_medio_pago" value="{{ number_format($valor_neto_confirmacion, 2, '.', '') }}">
												<p class="help-block">Valor neto a pagar: ${{ number_format($valor_neto_confirmacion, 2, ',', '.') }}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						@endif
						<p>¿Desea continuar con la confirmación del documento?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
						<button type="button" class="btn btn-primary" id="btn_confirmar_modal_accion">
							<i class="fa fa-check" id="icon_confirmar_modal"></i>
							<i class="fa fa-spinner fa-spin" id="icon_confirmar_modal_spin" style="display: none;"></i>
							<span id="texto_confirmar_modal">Confirmar</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection

@section('documento_vista')
@if(!empty($mensaje_advertencia_retencion))
    <div class="alert alert-danger" style="margin-top:20px;">
        <i class="fa fa-warning"></i> <strong>{{ $mensaje_advertencia_retencion }}</strong>
    </div>
@endif
@include('compras.incluir.documento_vista')
@endsection

@section('registros_otros_documentos')
{!! $medios_pago !!}
@include('compras.incluir.registros_abonos')
@include('compras.incluir.notas_credito')

{{-- Entradas de Almacén vinculadas: visible siempre que la factura tenga proveedor --}}
@if($doc_encabezado->proveedor_id)
@include('compras.incluir.asignar_ea_factura', [
    'doc_encabezado' => $doc_encabezado,
    'ea_asignadas'   => $ea_asignadas,
])
@endif

{{-- Mapeo productos XML: solo para facturas sincronizadas por el BOT --}}
@if($doc_encabezado->sincronizado_bot)
@include('compras.incluir.mapeo_productos_xml', [
'doc_encabezado' => $doc_encabezado,
'doc_registros' => $doc_registros,
'pivot_items_xml' => $pivot_items_xml,
'productos_para_select' => $productos_para_select,
])
@endif
@endsection


@section('otros_scripts')
<script type="text/javascript">
	$(document).ready(function() {

		$(".btn_editar_registro").click(function(event) {
			$('#contenido_modal').html('');
			$("#myModal").modal({
				backdrop: "static"
			});
			$("#div_spin").show();
			$(".btn_edit_modal").hide();

			var url = '../compras_get_formulario_edit_registro';

			$.get(url, {
					linea_registro_id: $(this).attr('data-linea_registro_id'),
					id: getParameterByName('id'),
					id_modelo: getParameterByName('id_modelo'),
					id_transaccion: getParameterByName('id_transaccion')
				})
				.done(function(data) {

					$('#contenido_modal').html(data);

					$("#div_spin").hide();

					$('#precio_unitario').select();

				});
		});

		// Al modificar el precio de compra
		$(document).on('keyup', '#precio_unitario', function(event) {

			if (validar_input_numerico($(this))) {

				var x = event.which || event.keyCode;
				if (x == 13) {
					$('#cantidad').select();
				}

				calcular_valor_descuento();

				calcular_precio_total();

			} else {
				$(this).focus();
				return false;
			}

		});

		// Al modificar el precio de compra
		$(document).on('keyup', '#cantidad', function(event) {
			if (validar_input_numerico($(this)) && $(this).val() > 0) {
				calcula_nuevo_saldo_a_la_fecha();

				var x = event.which || event.keyCode;

				if (x == 13) {
					if (!validar_saldo_a_la_fecha()) {
						return false;
					}
					$('#tasa_descuento').select();
				}

				calcular_valor_descuento();

				calcular_precio_total();

			} else {
				$(this).focus();
				return false;
			}

		});


		$(document).on('keyup', '#tasa_descuento', function(event) {
			if (validar_input_numerico($(this))) {
				// máximo valor de 100
				if ($(this).val() > 100) {
					$(this).val(100);
				}

				var x = event.which || event.keyCode;
				if (x == 13) {
					$('.btn_save_modal').focus();
					return true;
				}

				calcular_valor_descuento();

				calcular_precio_total();

			} else {

				$(this).focus();
				return false;
			}
		});

		function calcular_valor_descuento() {
			var valor_total_descuento = $('#precio_unitario').val() * $('#tasa_descuento').val() / 100 * $('#cantidad').val();

			$('#valor_total_descuento_no').val(valor_total_descuento);
			$('#valor_total_descuento').val(valor_total_descuento);
		}



		function calcular_precio_total() {
			var valor_total_descuento = parseFloat($('#valor_total_descuento').val());

			var precio_unitario = parseFloat($('#precio_unitario').val());

			var cantidad = parseFloat($('#cantidad').val());

			var precio_total = precio_unitario * cantidad - valor_total_descuento;

			$('#precio_total').val(precio_total);
		}

		$('.btn_save_modal').click(function() {
			if ($.isNumeric($('#precio_total').val()) && $('#precio_total').val() > 0) {
				if (!validar_saldo_a_la_fecha()) {
					return false;
				}
				validacion_saldo_movimientos_posteriores();
			} else {
				alert('El precio total es incorrecto. Verifique lo valores ingresados.');
			}
		});

		$("#myModal").on('hide.bs.modal', function() {
			$('#popup_alerta_danger').hide();
		});

		function validacion_saldo_movimientos_posteriores() {
			var url = '../inv_validacion_saldo_movimientos_posteriores/' + $('#bodega_id').val() + '/' + $('#producto_id').val() + '/' + $('#fecha').val() + '/' + $('#cantidad').val() + '/' + $('#saldo_a_la_fecha2').val() + '/entrada';

			$.get(url)
				.done(function(data) {
					if (data != 0) {
						$('#popup_alerta_danger').show();
						$('#popup_alerta_danger').text(data);
					} else {
						$('.btn_save_modal').off('click');
						$('#form_edit').submit();
						//console.log('a guardar');
						$('#popup_alerta_danger').hide();
					}
				});

		}

		$('#enlace_anular').click(function() {

			if (!$("#opcion1").is(":checked") && !$("#opcion2").is(":checked")) {
				alert('Debe escoger una opción.');
				$("#opcion1").focus();
				return false;
			}

			$('#form_anular').submit();

            });

			$('#btn_confirmar_documento').click(function(e){
				e.preventDefault();
				actualizar_destino_medio_pago_confirmacion();
				$('#modal_confirmar_documento').modal({backdrop: "static"});
			});

			$('#confirmar_teso_medio_recaudo_id').change(function(){
				actualizar_destino_medio_pago_confirmacion();
			});

			$('#btn_confirmar_modal_accion').click(function(e){
				e.preventDefault();
				if (!preparar_medios_pago_confirmacion()) {
					return false;
				}
				$(this).prop('disabled', true);
				$('#icon_confirmar_modal').hide();
				$('#icon_confirmar_modal_spin').show();
				$('#texto_confirmar_modal').text('Procesando...');
				$('#form_confirmar_documento').submit();
			});

			function actualizar_destino_medio_pago_confirmacion() {
				var medio = ($('#confirmar_teso_medio_recaudo_id').val() || '').split('-');
				if (medio.length > 1 && medio[1] == 'Tarjeta bancaria') {
					$('#confirmar_div_caja').hide();
					$('#confirmar_div_cuenta_bancaria').show();
					return;
				}

				$('#confirmar_div_cuenta_bancaria').hide();
				$('#confirmar_div_caja').show();
			}

			function preparar_medios_pago_confirmacion() {
				@if(!$factura_es_contado)
					return true;
				@endif

				var valorNeto = parseFloat('{{ number_format($valor_neto_confirmacion, 2, '.', '') }}');
				var valor = parseFloat(($('#confirmar_valor_medio_pago').val() || '').replace(',', '.'));

				if (!$.isNumeric(valor) || valor <= 0) {
					alert('Debe ingresar un valor válido para el medio de pago.');
					$('#confirmar_valor_medio_pago').focus();
					return false;
				}

				if (valor.toFixed(2) != valorNeto.toFixed(2)) {
					alert('El valor del medio de pago debe ser igual al valor neto a pagar.');
					$('#confirmar_valor_medio_pago').focus();
					return false;
				}

				if (($('#confirmar_teso_medio_recaudo_id').val() || '') == '') {
					alert('Debe seleccionar un medio de pago.');
					$('#confirmar_teso_medio_recaudo_id').focus();
					return false;
				}

				if (($('#confirmar_teso_motivo_id').val() || '') == '') {
					alert('Debe seleccionar un motivo de tesorería.');
					$('#confirmar_teso_motivo_id').focus();
					return false;
				}

				var usaCuenta = $('#confirmar_div_cuenta_bancaria').is(':visible');
				var cajaId = usaCuenta ? '0' : ($('#confirmar_teso_caja_id').val() || '');
				var cuentaId = usaCuenta ? ($('#confirmar_teso_cuenta_bancaria_id').val() || '') : '0';

				if (!usaCuenta && cajaId == '') {
					alert('Debe seleccionar una caja.');
					$('#confirmar_teso_caja_id').focus();
					return false;
				}

				if (usaCuenta && cuentaId == '') {
					alert('Debe seleccionar una cuenta bancaria.');
					$('#confirmar_teso_cuenta_bancaria_id').focus();
					return false;
				}

				var medioLabel = $('#confirmar_teso_medio_recaudo_id option:selected').text();
				var cajaLabel = usaCuenta ? '' : $('#confirmar_teso_caja_id option:selected').text();
				var cuentaLabel = usaCuenta ? $('#confirmar_teso_cuenta_bancaria_id option:selected').text() : '';

				var lineas = [
					{
						teso_medio_recaudo_id: $('#confirmar_teso_medio_recaudo_id').val().split('-')[0] + '-' + medioLabel,
						teso_motivo_id: $('#confirmar_teso_motivo_id').val(),
						teso_caja_id: cajaId + '-' + cajaLabel,
						teso_cuenta_bancaria_id: cuentaId + '-' + cuentaLabel,
						valor: '$' + valor.toFixed(2)
					},
					{
						teso_medio_recaudo_id: '',
						teso_motivo_id: '',
						teso_caja_id: '',
						teso_cuenta_bancaria_id: '',
						valor: '$' + valor.toFixed(2)
					}
				];

				$('#lineas_registros_medios_recaudo_confirmacion').val(JSON.stringify(lineas));
				return true;
			}

		/*
			validar_existencia_actual
			WARNING: Es diferente al validación de ventas o movimientos de salida de inventarios
		*/
		function validar_saldo_a_la_fecha() {
			if ($('#tipo').val() == 'servicio') {
				return true;
			}

			if (parseFloat($('#saldo_a_la_fecha').val()) < 0) {
				alert('Saldo negativo a la fecha.');
				$('#cantidad').val('');
				$('#cantidad').focus();
				return false;
			}
			return true;
		}

		function calcula_nuevo_saldo_a_la_fecha() {
			var saldo_actual = parseFloat($('#saldo_a_la_fecha').val());
			var cantidad_anterior = parseFloat($('#cantidad_anterior').val());
			var nuevo_saldo = saldo_actual - cantidad_anterior + parseFloat($('#cantidad').val());

			$('#saldo_a_la_fecha').val(nuevo_saldo);
			$('#saldo_a_la_fecha2').val(nuevo_saldo);
			$('#cantidad_anterior').val($('#cantidad').val());
		}

	});
</script>
@if($doc_encabezado->sincronizado_bot)
<script src="{{ asset('assets/js/compras/mapeo_productos_xml.js?aux=' . uniqid()) }}"></script>
@endif

<script type="text/javascript">
$(document).ready(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var baseUrl   = '{{ url('/') }}';

    // ── Buscar EAs pendientes del proveedor ──────────────────────────
    $('#btn_buscar_ea_pendientes').on('click', function () {
        var provId    = $(this).data('proveedor');
        var facturaId = $(this).data('factura');

        $('#ea_spinner').show();
        $('#contenedor_ea_pendientes').hide().html('');

        $.get(baseUrl + '/compras_ea_pendientes_proveedor', {
            proveedor_id: provId,
            compras_doc_encabezado_id: facturaId
        })
        .done(function (html) {
            $('#contenedor_ea_pendientes').html(html).slideDown();
            $('#chk_ea_all').on('change', function () {
                $('.chk_ea_item').prop('checked', this.checked);
            });
        })
        .fail(function (xhr) {
            $('#contenedor_ea_pendientes').html(
                '<p class="text-danger"><i class="fa fa-exclamation-circle"></i> Error al cargar las EAs. (' + xhr.status + ')</p>'
            ).show();
        })
        .always(function () { $('#ea_spinner').hide(); });
    });

    // ── Asignar EAs seleccionadas ────────────────────────────────────
    $(document).on('click', '#btn_confirmar_asignar_ea', function () {
        var facturaId = $(this).data('factura');
        var ids = [];
        $('.chk_ea_item:checked').each(function () { ids.push($(this).val()); });

        if (ids.length === 0) {
            alert('Seleccione al menos una Entrada de Almacén.');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Asignando...');

        $.ajax({
            url:  baseUrl + '/compras_asignar_ea',
            type: 'POST',
            data: { ea_ids: ids, compras_doc_encabezado_id: facturaId, _token: csrfToken },
            success: function (resp) {
                if (resp.ok) { location.reload(); }
                else { alert(resp.msg); }
            },
            error: function () { alert('Error al asignar las EAs.'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-link"></i> Asignar seleccionadas a esta factura');
            }
        });
    });

    // ── Desasignar una EA ────────────────────────────────────────────
    $(document).on('click', '.btn_desasignar_ea', function () {
        var eaId      = $(this).data('ea_id');
        var facturaId = $(this).data('factura');
        var $fila     = $(this).closest('tr');

        if (!confirm('¿Desvincular esta EA? Volverá al estado Pendiente.')) return;

        $.ajax({
            url:  baseUrl + '/compras_desasignar_ea',
            type: 'POST',
            data: { ea_id: eaId, compras_doc_encabezado_id: facturaId, _token: csrfToken },
            success: function (resp) {
                if (resp.ok) {
                    $fila.fadeOut(300, function () {
                        $fila.remove();
                        if ($('#tabla_ea_asignadas tbody tr').length === 0) {
                            $('#tabla_ea_asignadas').html(
                                '<p class="text-muted"><i class="fa fa-info-circle"></i> Aún no hay Entradas de Almacén vinculadas a esta factura.</p>'
                            );
                        }
                    });
                } else { alert(resp.msg); }
            },
            error: function () { alert('Error al desvincular la EA.'); }
        });
    });

});
</script>
@endsection
