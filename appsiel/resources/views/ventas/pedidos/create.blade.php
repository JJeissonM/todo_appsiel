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

		#existencia_actual, #tasa_impuesto, #tasa_descuento{
			width: 40px;
		}

	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">		    

			<h4>Nuevo registro</h4>
			<hr>
			{{ Form::open([ 'url' => $form_create['url'], 'id'=>'form_create']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }

				  $id_transaccion = Input::get('id_transaccion');
				  if( is_null( $id_transaccion ) )
				  {
				  	$id_transaccion = 42;
				  }				  

				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				<input type="hidden" name="url_id" id="url_id" value="{{Input::get('id')}}" required="required">

				<input type="hidden" name="url_id_modelo" id="url_id_modelo" value="{{Input::get('id_modelo')}}" required="required">
				
				<input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{$id_transaccion}}" required="required">

				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="forma_pago" id="forma_pago" value="" required="required">
				<input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="" required="required">
				<input type="hidden" name="inv_bodega_id" id="inv_bodega_id" value="1" required="required">

				<input type="hidden" name="cliente_id" id="cliente_id" value="" required="required">
				<input type="hidden" name="zona_id" id="zona_id" value="" required="required">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="" required="required">
				<input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="" required="required">

				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="" required="required">
				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="" required="required">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="" required="required">
				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

				<input type="hidden" name="tipo_transaccion"  id="tipo_transaccion" value="factura_directa">

				<input type="hidden" name="rm_tipo_transaccion_id"  id="rm_tipo_transaccion_id" value="{{config('ventas')['rm_tipo_transaccion_id']}}">
				<input type="hidden" name="dvc_tipo_transaccion_id"  id="dvc_tipo_transaccion_id" value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

				<input type="hidden" name="saldo_original" id="saldo_original" value="0">

				<input type="hidden" name="permitir_venta_menor_costo" id="permitir_venta_menor_costo" value="{{ config('ventas.permitir_venta_menor_costo') }}">
				<input type="hidden" name="permitir_inventarios_negativos" id="permitir_inventarios_negativos" value="{{ config('ventas.permitir_inventarios_negativos') }}">

				<div id="popup_alerta"> </div>
				
			{{ Form::close() }}


			<br/>


			{!! $tabla->dibujar() !!}

			@include('core.componentes.productos_y_cantidades_ingresadas')
			
			<div style="text-align: right;">
				<div id="total_cantidad" style="display: none;"> 0 </div>
            	<table style="display: inline;">
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ 0 </div> </td>
            		</tr>
					<tr>
            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> <td> <div id="total_impuestos"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> <td> <div id="total_factura"> $ 0 </div> </td>
            		</tr>
            	</table>
			</div>

		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script src="{{ asset( 'assets/js/ventas/create.js?aux=' . uniqid() )}}"></script>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#fecha_entrega').val( get_fecha_hoy() );
		});
	</script>
@endsection