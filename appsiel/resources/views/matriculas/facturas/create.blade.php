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

		#popup_alerta{
			display: none;/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right:10px; /*A la izquierda deje un espacio de 0px*/
			bottom:10px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index:999999;
			float: right;
    		text-align: center;
    		padding: 5px;
    		opacity: 0.7;
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

				<input type="hidden" name="estudiante_id" id="estudiante_id" value="{{ Input::get('estudiante_id') }}" required="required">
				<input type="hidden" name="cartera_id" id="cartera_id" value="{{ Input::get('cartera_id') }}" required="required">


				<input type="text" name="matricula_id" id="matricula_id" value="{{ $estudiante->matriculas->where('estado','Activo')->first()->id }}">
				<input type="text" name="cartera_estudiante_id" id="cartera_estudiante_id" value="{{ $estudiante->matriculas->where('estado','Activo')->first()->id }}">

				<input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{Input::get('id_transaccion')}}" required="required">

				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux']) }}

				<input type="hidden" name="cliente_id" id="cliente_id" value="{{ $cliente->id }}" required="required">
				<input type="hidden" name="zona_id" id="zona_id" value="{{ $cliente->zona_id }}" required="required">
				<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="{{ $cliente->clase_cliente_id }}" required="required">


				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{ $cliente->tercero->id }}" required="required">
				<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="{{ $cliente->lista_precios_id }}" required="required">
				<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="{{ $cliente->lista_descuentos_id }}" required="required">
				<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="{{ $cliente->liquida_impuestos }}" required="required">

				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">
				<input type="hidden" name="lineas_registros_medios_recaudo" id="lineas_registros_medios_recaudo" value="0">

				<input type="hidden" name="tipo_transaccion"  id="tipo_transaccion" value="factura_directa">

				<input type="hidden" name="rm_tipo_transaccion_id"  id="rm_tipo_transaccion_id" value="{{config('ventas')['rm_tipo_transaccion_id']}}">
				<input type="hidden" name="dvc_tipo_transaccion_id"  id="dvc_tipo_transaccion_id" value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

				<input type="hidden" name="saldo_original" id="saldo_original" value="0">

				<div id="popup_alerta"> </div>		            

				Responsable finanaciero: {{ $responsable_financiero_estudiante->tercero->descripcion }}
				<br>
				C.C.: {{ $responsable_financiero_estudiante->tercero->numero_identificacion }}
			{{ Form::close() }}

			<br/>

			@include( 'ventas.incluir.tabla_lineas_registros')


			Productos ingresados: <span id="numero_lineas"> 0 </span>
			
			<div style="text-align: right;">
				<div id="total_cantidad" style="display: none;"> 0 </div>
            	<table style="display: inline;">
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td> <td> <div id="subtotal"> $ {{ number_format( Input::get('valor_cartera'), 0, ',', '.' ) }} </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Descuento: &nbsp; </td> <td> <div id="descuento"> $ 0 </div> </td>
            		</tr>
					<tr>
            			<td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td> <td> <div id="total_impuestos"> $ 0 </div> </td>
            		</tr>
            		<tr>
            			<td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td> <td> <div id="total_factura"> $ {{ number_format( Input::get('valor_cartera'), 0, ',', '.' ) }} </div> </td>
            		</tr>
            	</table>
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

	@include( 'components.design.ventana_modal',['titulo'=>'Asignar formula a la factura','texto_mensaje'=>'','contenido_modal' => ''] )
	@include( 'components.design.ventana_modal2',['titulo2'=>'Consulta del exámen','texto_mensaje2'=>'','contenido_modal' => ''] )

	<br/><br/>
@endsection

@section('scripts')
	
	<!-- <script src="{ { asset( 'assets/js/ventas/create.js' ) }}"></script> -->

	<script type="text/javascript">


		hay_productos = 1;

		$(document).ready(function(){

			$('#fecha_vencimiento').val( get_fecha_hoy() );
			$('#fecha').val( get_fecha_hoy() );
			$('#linea_ingreso_default').hide();

			// GUARDAR EL FORMULARIO
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;	
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#linea_ingreso_default').remove();

				// Se transfoma la tabla a formato JSON a través de un plugin JQuery
				var table = $('#ingreso_registros').tableToJSON();

				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_registros').val(JSON.stringify(table));

		 		// Enviar formulario
				$('#form_create').submit();					
			});

		});
	</script>

@endsection