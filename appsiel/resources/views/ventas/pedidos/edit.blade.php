@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
	<style>
		#suggestions {
		    position: absolute;
		    z-index: 9999;
		}
		#clientes_suggestions {
		    position: absolute;
		    z-index: 9999;
		}

		#existencia_actual, #tasa_impuesto{
			width: 35px;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">		    

			<h4>Modificando el registro</h4>
			<hr>
			{{ Form::model($registro, ['url' => ['vtas_pedidos'], 'method' => 'PUT','files' => true]) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="action" id="action" value="{{ Input::get('action') }}">

				<input type="hidden" name="inv_bodega_id" id="inv_bodega_id" value="{{$cliente->inv_bodega_id}}">

				<input type="hidden" name="cliente_id" id="cliente_id" value="{{$cliente->id}}">
				<input type="hidden" name="zona_id" id="zona_id" value="{{$cliente->zona_id}}">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="{{$cliente->clase_cliente_id}}">
				<input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="{{$cliente->equipo_ventas_id}}">

				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$cliente->core_tercero_id}}">
				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="{{$cliente->lista_precios_id}}">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="{{$cliente->lista_descuentos_id}}">

				<input type="hidden" name="permitir_venta_menor_costo" id="permitir_venta_menor_costo" value="{{ config('ventas.permitir_venta_menor_costo') }}">
				<input type="hidden" name="permitir_inventarios_negativos" id="permitir_inventarios_negativos" value="{{ config('ventas.permitir_inventarios_negativos') }}">
				
			{{ Form::close() }}

			<br/>
				

		    {!! $tabla->dibujar() !!}

			Productos ingresados: <span id="numero_lineas"> {{ count( $registros->toArray() ) }} </span>
			
			<div style="text-align: right;">
				<div id="total_cantidad" style="display: none;"> 0 </div>
            	<table style="display: inline;">
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ {{ number_format( $registros->sum('base_impuesto'), 0, ',', '.') }} </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> <td> <div id="total_impuestos"> $ {{ number_format( $registros->sum('valor_impuesto') * $registros->sum('cantidad'), 0, ',', '.') }} </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> <td> <div id="total_factura"> $ {{ $registros->sum('precio_total')}} </div> </td>
            		</tr>
            	</table>
			</div>
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script src="{{ asset( 'assets/js/ventas/create.js?aux=' . uniqid() ) }}"></script>
	<script src="{{ asset( 'assets/js/modificar_con_doble_click_sin_recargar.js?aux=' . uniqid() ) }}"></script>

	<script type="text/javascript">	

		hay_productos = $('.linea_registro').length

		function ejecutar_funcion_guardar_nuevo_valor_doble_click( campo_modificado, nuevo_valor, valor_actual )
	    {
	    	actualizar_valores_fila( campo_modificado )

			if( !validar_venta_menor_costo() )
			{
				campo_modificado.text( valor_actual )
	    		actualizar_valores_fila( campo_modificado )
			}
	    }

		function actualizar_valores_fila( campo_modificado )
		{
			var fila = campo_modificado.closest('tr');

			cantidad = parseFloat( fila.find('.elemento_modificar').eq(0).text() );
			precio_unitario = parseFloat( fila.find('.elemento_modificar').eq(1).text() )
			tasa_impuesto = parseFloat( fila.find('.tasa_impuesto').text() )
			costo_unitario = parseFloat( fila.find('.costo_unitario').text() )

			fila.find('.cantidad').text( cantidad );
			fila.find('.precio_unitario').text( precio_unitario )

			calcular_valor_descuento_aux( fila )			

			calcular_impuestos()

			fila.find('.base_impuesto').text( base_impuesto_unitario )
			fila.find('.base_impuesto_total').text( base_impuesto_unitario * cantidad )
			fila.find('.valor_impuesto').text( valor_impuesto_unitario )			

			calcular_precio_total_aux( fila )

			calcular_totales()
		}
		
		function calcular_valor_descuento_aux( fila )
		{
			tasa_descuento = parseFloat( fila.find('.tasa_descuento').text() );

			// El descuento se calcula cuando el precio tiene el IVA incluido
			valor_unitario_descuento = precio_unitario * tasa_descuento / 100;

			valor_total_descuento = valor_unitario_descuento * cantidad;
			
			fila.find('.valor_total_descuento').text(valor_total_descuento)
			fila.find('.lbl_valor_total_descuento').text( '$' + new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(2) ) )
		}

		function calcular_precio_total_aux( fila )
		{
			precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;
			
			fila.find('.precio_total').text( 0 );
			fila.find('.lbl_precio_total').text( 0 );

			if( $.isNumeric( precio_total ) && precio_total > 0 )
			{
				fila.find('.precio_total').text( precio_total );
				fila.find('.lbl_precio_total').text( '$' + new Intl.NumberFormat("de-DE").format( precio_total.toFixed(2) ) );
				return true;
			}else{
				precio_total = 0;
				return false;
			}
		}
	</script>
@endsection