<?php
$variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	@if($doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado=='Pendiente')
		<button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-close"></i> Anular </button>
	@endif
	
	@if( $doc_encabezado->estado!='Cumplida' && $doc_encabezado->estado!='Anulado')
		<button onclick="enviar()" class="btn btn-success btn-xs" id="btn_entradaalmacen"><i class="fa fa-send"></i> Crear Entrada Almacén </button>
	@endif
@endsection

@section('botones_imprimir_email')
Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
{{ Form::bsBtnPrint( 'compras_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
{!! $botones_anterior_siguiente->dibujar( 'orden_compra/', $variables_url ) !!}
@endsection

@section('filas_adicionales_encabezado')
<tr>
	<td style="border: solid 1px #ddd;">
		<b>Proveedor:</b> {{ $doc_encabezado->tercero_nombre_completo }}
		<br />
		<b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
		<br />
		<b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
		<br />
		<b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
	</td>
	<td style="border: solid 1px #ddd;">
		<b>Factura del proveedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->doc_proveedor_prefijo }} - {{ $doc_encabezado->doc_proveedor_consecutivo }}
		<br />
		<b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
		<br />
		<b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}
		<br />
		<b>Entrada de Almacén: &nbsp;&nbsp;</b> @if($entrada!=null) <a target="_blank" href="{{url('inventarios/'.$entrada->id.'?id='.Input::get('id').'&id_modelo=165&id_transaccion='.$entrada->core_tipo_transaccion_id)}}"> {{$entrada->documento_transaccion_prefijo_consecutivo}} </a> @else - @endif
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
@endsection

@section('documento_vista')
<div>
	<form id="entrada_almacen" action="{{route('almacen.entrada')}}" method="post">
		{{ csrf_field() }}
		<input type="hidden" name="id" value="{{$doc_encabezado->id}}" />
		<input type="hidden" name="lineas_registros" id="lineas_registros" />
		<input type="hidden" name="url_id" value="{{Input::get('id')}}" />
		<input type="hidden" name="inv_bodega_id" value="{{$proveedor->inv_bodega_id}}" />
		<table class="table table-bordered table-striped">
			{{ Form::bsTableHeader(['Cód.','Producto','Precio','IVA','Cantidad','Total']) }}
			<tbody>
				<?php

				$total_cantidad = 0;
				$subtotal = 0;
				$total_impuestos = 0;
				$total_factura = 0;
				?>
				@foreach($doc_registros as $linea )
				<tr>
					<td> {{ $linea->producto_id }} </td>
					<td> {{ $linea->producto_descripcion }} </td>
					@if($doc_encabezado->estado=='Cumplida')
					<td> $ {{$linea->precio_unitario}}</td>
					<td> {{$linea->tasa_impuesto}} %</td>
					<td> {{$linea->cantidad}}</td>
					<td> $ {{$linea->precio_total}}</td>
					@else
					<td> <input class="preciou" type="text" onkeyup="calcular(this.id)" id="{{$linea->id}}" value="{{$linea->precio_unitario}}" style="width: 100%" name="dpreciounitario_{{$linea->id}}" /> </td>
					<td> <input readonly class="imp" type="text" onkeyup="calcular(this.id)" id="{{$linea->id}}" value="{{$linea->tasa_impuesto}}" style="width: 100%" name="dimpuesto_{{$linea->id}}" /> </td>
					<td> <input class="cant" type="text" onkeyup="calcular(this.id)" id="{{$linea->id}}" value="{{$linea->cantidad}}" style="width: 100%" name="dcantidad_{{$linea->id}}" /> </td>
					<td> <input class="total" type="text" id="{{$linea->id}}" value="{{$linea->precio_total}}" style="width: 100%" name="dpreciototal_{{$linea->id}}" readonly> </td>
					@endif
				</tr>
				<?php
				$total_cantidad += $linea->cantidad;
				$subtotal += (float) $linea->base_impuesto;
				$total_impuestos += (float) $linea->valor_impuesto;
				$total_factura += $linea->precio_total;
				?>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">&nbsp;</td>
					<td id="tbcant"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tfoot>
		</table>

		<table class="table table-bordered">
			<tr>
				<td id="tbstotal"> <span style="text-align: right; font-weight: bold;"> Subtotal: </span> $ {{ number_format($subtotal, 0, ',', '.') }}</td>
				<td id="tbimp"> <span style="text-align: right; font-weight: bold;"> Impuestos: </span> $ {{ number_format($total_impuestos, 0, ',', '.') }}</td>
				<td id="tbtotal"> <span style="text-align: right; font-weight: bold;"> Total factura: </span> $ {{ number_format($total_factura, 0, ',', '.') }}</td>
			</tr>
		</table>
		{{ Form::close() }}
</div>
@endsection

@section('otros_scripts')
<script type="text/javascript">
	var array_registros = [];
	var proveedor = <?php echo $proveedor; ?>;
	$(document).ready(function() {

		array_registros = <?php echo json_encode($doc_registros); ?>;

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

				$('#precio_total').val(parseFloat($('#precio_unitario').val()) * parseFloat($('#cantidad').val()));

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
					$('.btn_save_modal').focus();
				}

				$('#precio_total').val(parseFloat($('#precio_unitario').val()) * parseFloat($('#cantidad').val()));

			} else {
				$(this).focus();
				return false;
			}

		});

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

		/*
			validar_existencia_actual
			WARNING: Es diferente al validación de ventas o movimientos de salida de inventarios
		*/
		function validar_saldo_a_la_fecha() {

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


	function calcular(id) {
		var arraytotal = [];
		var arrayimp = [];
		var arrayc = [];
		var nuevoimp = 0;
		var sbtotal = 0;
		var totalc = 0;
		var totalt = 0;
		var vu = $("input:text[name=dpreciounitario_" + id + "]").val();
		var cant = $("input:text[name=dcantidad_" + id + "]").val();
		$("input:text[name=dpreciototal_" + id + "]").val(vu * cant);
		$(".cant").each(function() {
			arrayc.push($(this).val());
			totalc = totalc + parseFloat($(this).val());
		});
		$(".total").each(function() {
			arraytotal.push($(this).val());
		});
		$(".imp").each(function() {
			arrayimp.push($(this).val());
		});
		arraytotal.forEach(function(value, index) {
			totalt = totalt + parseFloat(value);
			var sbt = parseFloat(value) / (1 + (arrayimp[index] / 100));
			sbtotal = sbtotal + sbt;
			nuevoimp = nuevoimp + (parseFloat(value) - sbt);
		});
		$("#tbtotal").html("<span style='text-align: right; font-weight: bold;'> Total factura: </span> $ " + Math.round(totalt));
		$("#tbcant").html(totalc);
		$("#tbstotal").html("<span style='text-align: right; font-weight: bold;'> Subtotal: </span> $" + Math.round(sbtotal));
		$("#tbimp").html("<span style='text-align: right; font-weight: bold;'> Impuestos: </span> $" + Math.round(nuevoimp));
	}

	function enviar() {
		var linea_reg = [];
		$(".total").each(function() {
			var prod = $(this).parent('td').prev().children('input').attr('id');
			linea_reg.push(llenar_objeto(prod));
		});
		$('#lineas_registros').val(JSON.stringify(linea_reg));
		$("#entrada_almacen").submit();
	}

	function llenar_objeto(id) {
		var o = new Object();
		array_registros.forEach(function(value, index) {
			if (id == value.id) {
				o['inv_motivo_id'] = value.inv_motivo_id;
				o['inv_bodega_id'] = proveedor.inv_bodega_id;
				o['inv_producto_id'] = value.inv_producto_id;
				var precio_unitario = $("input:text[name=dpreciounitario_" + id + "]").val();
				var cantidad = $("input:text[name=dcantidad_" + id + "]").val();
				var costo_unitario = parseFloat(precio_unitario) / (1 + (parseFloat(value.tasa_impuesto) / 100));
				o['costo_unitario'] = costo_unitario;
				o['precio_unitario'] = value.precio_unitario;
				o['base_impuesto'] = costo_unitario * cantidad;
				o['tasa_impuesto'] = value.tasa_impuesto;
				o['valor_impuesto'] = parseFloat(precio_unitario) - parseFloat(costo_unitario);
				o['cantidad'] = cantidad;
				o['costo_total'] = parseFloat(cantidad) * parseFloat(costo_unitario);
				o['precio_total'] = parseFloat(cantidad) * parseFloat(precio_unitario);
			}
		});
		return o;
	}
</script>
@endsection