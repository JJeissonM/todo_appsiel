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

		.alert .close {
		    color: #574696 !important;
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
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

				<input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{Input::get('id_transaccion')}}" required="required">

				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux']) }}

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

				<div id="popup_alerta"> </div>

				<input type="hidden" name="lineas_registros_medios_recaudo" id="lineas_registros_medios_recaudo" value="0">
				
			{{ Form::close() }}

			<br/>

			@include('ventas.incluir.elementos_remisiones_pendientes')

			<br/>


			@if( Input::get('id_transaccion') == 41 )
				<h4 style="color: red;">¡¡¡Los precios serán almacenados con los precios de la lista de precios del cliente.!!!</h4>
			@endif
			{!! $tabla->dibujar() !!}


			Productos ingresados: <span id="numero_lineas"> 0 </span>
			
			<div style="text-align: right;">
				<div id="total_cantidad" style="display: none;"> 0 </div>
            	<table style="display: inline;">
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Descuento: &nbsp; </td> <td> <div id="descuento"> $ 0 </div> </td>
            		</tr>
					<tr>
            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> <td> <div id="total_impuestos"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> <td> <div id="total_factura"> $ 0 </div> </td>
            		</tr>
            	</table>
			</div>
			<div>
				@include('tesoreria.incluir.medios_recaudos')
			</div>

			<hr>
			<h4>Parámetros</h4>
			<div class="row">

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'permitir_venta_menor_costo', config('ventas.permitir_venta_menor_costo'), 'Permitir ventas menor que el costo', ['0'=>'No','1'=>'Si'], ['class'=>'permitir_venta_menor_costo','disabled'=>'disabled'] ) }}
					</div>
				</div>

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsSelect( 'permitir_inventarios_negativos', config('ventas.permitir_inventarios_negativos'), 'Permitir inventarios negativos', ['0'=>'No','1'=>'Si'], ['class'=>'permitir_inventarios_negativos','disabled'=>'disabled'] ) }}
					</div>
				</div>

			</div>
			
			<br><br>
		</div>
	</div>
	<br/><br/>

	{{ Form::open([ 'url' => 'vtas_totales_remisiones_seleccionadas', 'id'=>'form_remisiones_seleccionadas']) }}
		<input type="hidden" name="lineas_registros_remisiones" id="lineas_registros_remisiones" value="0">
		<input type="hidden" name="lista_precios_id2" id="lista_precios_id2" value="0">
		<input type="hidden" name="fecha2" id="fecha2" value="0">
		<input type="hidden" name="item_sugerencia_cliente" id="item_sugerencia_cliente" value="{{$item_sugerencia_cliente}}">
	{{ Form::close() }}

@endsection

@section('scripts')

	<script type="text/javascript">
		var url_raiz = "{{ url('/') }}";
		var dias_plazo;

		$.fn.actualizar_medio_recaudo = function(){
    
		    var texto_total_recaudos = this.html().substring(1);
		    
		    if( parseFloat( texto_total_recaudos ) == 0 )
		    {
		        return false;
		    }

		};

	</script>
	
	<script src="{{ asset( 'assets/js/ventas/create.js' ) }}"></script>
	<script type="text/javascript" src="{{asset('assets/js/tesoreria/medios_recaudos.js')}}"></script>
@endsection