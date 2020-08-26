<?php
$variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')

	{{ Form::bsBtnCreate( 'vtas_pedidos/create'.$variables_url ) }}
	
	@if($doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado=='Pendiente')
		<!--{{ Form::bsBtnEdit2(str_replace('id_fila', $id, 'vtas_pedidos/id_fila/edit'.$variables_url ),'Editar') }}-->
		<button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-btn fa-close"></i> Anular </button>
	@endif
	
	@if($doc_encabezado->estado=='Pendiente')
		<button onclick="enviar()" class="btn btn-success btn-xs" id="btn_remision"><i class="fa fa-send"></i> Crear Remisión </button>
	@endif

@endsection

@section('botones_imprimir_email')
Formato: {{ Form::select('formato_impresion_id',['1'=>'POS','2'=>'Estándar'],null, [ 'id' =>'formato_impresion_id' ]) }}
{{ Form::bsBtnPrint( 'vtas_pedidos_imprimir/'.$id.$variables_url.'&formato_impresion_id=1' ) }}
{{ Form::bsBtnEmail( 'vtas_pedidos_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=1' ) }}
@endsection

@section('botones_anterior_siguiente')
{!! $botones_anterior_siguiente->dibujar( 'vtas_pedidos/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
<br />
<b>Para:</b> {{ $doc_encabezado->tercero_nombre_completo }}
<br />
<b>NIT: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
@endsection

@section('filas_adicionales_encabezado')
&nbsp;
@endsection

@section('div_advertencia_anulacion')
<div class="alert alert-warning" style="display: none;">
	<a href="#" id="close" class="close">&times;</a>
	<strong>Advertencia!</strong>
	<br>
	La anulación no se puede revertir.
	<br>
	Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url( 'vtas_pedidos_anular/'.$id.$variables_url ) }}"> Anular </a> </small>
</div>
@endsection

@section('documento_vista')
<form id="remision" action="{{route('pedido.remision')}}" method="post">
	{{ csrf_field() }}
	<input type="hidden" name="id" value="{{$doc_encabezado->id}}" />
	<input type="hidden" name="lineas_registros" id="lineas_registros" />
	<input type="hidden" name="url_id" value="{{Input::get('id')}}" />
	<input type="hidden" name="cliente_id" value="{{$cliente->id}}" />
	<input type="hidden" name="core_tercero_id" value="{{$cliente->core_tercero_id}}" />
	<input type="hidden" name="inv_bodega_id" value="{{$cliente->inv_bodega_id}}" />
	<input type="hidden" name="core_empresa_id" value="{{$doc_encabezado->core_empresa_id}}" />
	<table class="table table-bordered table-striped">
		{{ Form::bsTableHeader(['Item','Producto','Cantidad','Vr. unitario','IVA','Total Bruto','Total']) }}
		<tbody>
			<?php
			$i = 1;
			$total_cantidad = 0;
			$subtotal = 0;
			$total_impuestos = 0;
			$total_factura = 0;
			$array_tasas = [];
			?>
			@foreach($doc_registros as $linea )
			<tr>
				<td> {{ $i }} </td>
				<td width="250px"> {{ $linea->producto_descripcion }} </td>
				@if($doc_encabezado->estado=='Cumplido')
				<td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
				<td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 0, ',', '.') }} </td>
				<td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
				<td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) * $linea->cantidad, 0, ',', '.') }} </td>
				<td> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
				@else
				<td> <input class="cant" type="text" onkeyup="calcular(this.id)" id="{{$linea->id}}" value="{{$linea->cantidad}}" style="width: 100%" name="dcantidad_{{$linea->id}}" /> </td>
				<td> <input class="preciou" type="text" onkeyup="calcular(this.id)" id="{{$linea->id}}" value="{{round($linea->precio_unitario / (1+$linea->tasa_impuesto/100),2,PHP_ROUND_HALF_UP)}}" style="width: 100%" name="dpreciounitario_{{$linea->id}}" /> </td>
				<td> <input readonly class="imp" type="text" onkeyup="calcular(this.id)" id="{{$linea->id}}" value="{{$linea->tasa_impuesto}}" style="width: 100%" name="dimpuesto_{{$linea->id}}" /></td>
				<td> <input readonly class="valor_bruto" type="text" id="{{$linea->id}}" value="{{round( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) * $linea->cantidad,2,PHP_ROUND_HALF_UP) }}" style="width: 100%" name="dprecio_bruto_{{$linea->id}}"> </td>
				<td> <input class="total" type="text" id="{{$linea->id}}" value="{{round( $linea->precio_total,2,PHP_ROUND_HALF_UP)}}" style="width: 100%" name="dpreciototal_{{$linea->id}}" readonly></td>
				@endif
			</tr>
			<?php
			$i++;
			$total_cantidad += $linea->cantidad;
			$subtotal += (float) $linea->base_impuesto * (float) $linea->cantidad;
			$total_impuestos += (float) $linea->valor_impuesto * (float) $linea->cantidad;
			$total_factura += $linea->precio_total;

			// Si la tasa no está en el array, se agregan sus valores por primera vez
			if (!isset($array_tasas[$linea->tasa_impuesto])) {
				// Clasificar el impuesto
				$array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA ' . $linea->tasa_impuesto . '%';
				if ($linea->tasa_impuesto == 0) {
					$array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA 0%';
				}
				// Guardar la tasa en el array
				$array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


				// Guardar el primer valor del impuesto y base en el array
				$array_tasas[$linea->tasa_impuesto]['precio_total'] = (float) $linea->precio_total;
				$array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float) $linea->base_impuesto * (float) $linea->cantidad;
				$array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float) $linea->valor_impuesto * (float) $linea->cantidad;
			} else {
				// Si ya está la tasa creada en el array
				// Acumular los siguientes valores del valor base y valor de impuesto según el tipo
				$precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
				$array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float) $linea->precio_total;
				$array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float) $linea->base_impuesto * (float) $linea->cantidad;
				$array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float) $linea->valor_impuesto * (float) $linea->cantidad;
			}
			?>
			@endforeach
		</tbody>
	</table>

	<table class="table table-bordered">
		<tr>
			<td width="75%"> <b> &nbsp; </b> <br> </td>
			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td>
			<td style="text-align: right; font-weight: bold;" id="tbstotal"> $ {{ round($subtotal,2,PHP_ROUND_HALF_UP) }} </td>
		</tr>

		@foreach( $array_tasas as $key => $value )
		<tr>
			<td width="75%"> <b> &nbsp; </b> <br> </td>
			<td style="text-align: right; font-weight: bold;"> {{ $value['tipo'] }} </td>
			<td style="text-align: right; font-weight: bold;" id="tbimpuesto"> ${{ round($value['valor_impuesto'],2,PHP_ROUND_HALF_UP) }} </td>
		</tr>
		@endforeach
		<tr>
			<td width="75%"> <b> &nbsp; </b> <br> </td>
			<td style="text-align: right; font-weight: bold;"> Total Pedido: &nbsp; </td>
			<td style="text-align: right; font-weight: bold;" id="tbtotal"> $ {{ round($total_factura,2,PHP_ROUND_HALF_UP) }} </td>
		</tr>
	</table>
</form>
@endsection
@section('otros_scripts')
<script type="text/javascript">
	var array_registros = [];
	var cliente = <?php echo $cliente; ?>;

	$(document).ready(function() {
		array_registros = <?php echo json_encode($doc_registros); ?>;
	});


	function calcular(id) {
		var arraytotal = [];
		var arrayimp = [];
		var arrayc = [];
		var arraytotalbruto = [];
		var nuevoimp = 0;
		var sbtotal = 0;
		var totalc = 0;
		var totalt = 0;
		var vu = $("input:text[name=dpreciounitario_" + id + "]").val();
		var cant = $("input:text[name=dcantidad_" + id + "]").val();
		var bruto = Math.round(parseFloat(vu) * parseFloat(cant));
		$("input:text[name=dprecio_bruto_" + id + "]").val(bruto);
		var iva = $("input:text[name=dimpuesto_" + id + "]").val();
		var total = Math.round(bruto + (bruto * (iva / 100)));
		$("input:text[name=dpreciototal_" + id + "]").val(total);
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
		$(".valor_bruto").each(function() {
			arraytotalbruto.push($(this).val());
		});
		arraytotal.forEach(function(value, index) {
			totalt = totalt + parseFloat(value);
			sbtotal = sbtotal + parseFloat(arraytotalbruto[index]);
			nuevoimp = nuevoimp + (arraytotalbruto[index] * (arrayimp[index] / 100));
		});
		$("#tbtotal").html("$ " + Math.round(totalt));
		$("#tbcant").html(totalc);
		$("#tbstotal").html("$ " + Math.round(sbtotal));
		$("#tbimpuesto").html("$ " + Math.round(nuevoimp));
	}


	function enviar() {
		var linea_reg = [];
		$(".total").each(function() {
			var prod = $(this).parent('td').prev().children('input').attr('id');
			linea_reg.push(llenar_objeto(prod));
		});
		$('#lineas_registros').val(JSON.stringify(linea_reg));
		$("#remision").submit();
	}

	function llenar_objeto(id) {
		var o = new Object();
		array_registros.forEach(function(value, index) {
			if (id == value.id) {
				o['inv_motivo_id'] = value.vtas_motivo_id;
				o['inv_bodega_id'] = cliente.inv_bodega_id;
				o['inv_producto_id'] = value.producto_id;
				var precio_unitario = $("input:text[name=dpreciounitario_" + id + "]").val();
				var cantidad = $("input:text[name=dcantidad_" + id + "]").val();
				var costo_unitario = parseFloat(precio_unitario) / (1 + (parseFloat(value.tasa_impuesto) / 100));
				o['costo_unitario'] = costo_unitario;
				o['cantidad'] = cantidad;
				o['costo_total'] = Math.round($("input:text[name=dpreciototal_" + id + "]").val());
			}
		});
		return o;
	}
</script>
@endsection