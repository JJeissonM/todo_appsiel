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


				<h4>Facturación de formula</h4>
		        <hr>
		        <button class="btn btn-primary btn-xs" id="agregar_examen"><i class="fa fa-btn fa-plus"></i> Agregar fórmula </button>

		        <table class="table table-striped" id="tabla_formula" style="display: none;">
	                <thead>
	                    <tr>
	                        <th>Fecha</th>
	                        <th>Consulta</th>
	                        <th>Formula</th>
	                        <th>Exámen</th>
	                        <th></th>
	                    </tr>
	                </thead>
	                <tbody>
	                </tbody>
	            </table>

	            <div class="container-fluid" id="tabla_formula_no_paciente" style="display: none;">
	            	<h2> Ingrese los datos de la formula a facturar </h2>
	            	<table class="table table-bordered">
	                    <thead>
	                        <tr>
	                            <th>&nbsp;</th>
	                            <th> Esfera </th>
	                            <th> Cilindro </th>
	                            <th> Eje </th>
	                            <th> Adición </th>
	                            <th> Agudeza Visual </th>
	                            <th> Distancia Pupilar </th>
	                        </tr>
	                    </thead>
	                    <tbody>
	                    	<tr>
	                    		<td> O. D. </td>
	                    		<td> <input class="form-control" name="esfera_ojo_derecho" id="esfera_ojo_derecho" type="text"> </td>
	                    		<td> <input class="form-control" name="cilindro_ojo_derecho" type="text"> </td>
	                    		<td> <input class="form-control" name="eje_ojo_derecho" type="text"> </td>
	                    		<td> <input class="form-control" name="adicion_ojo_derecho" type="text"> </td>
	                    		<td> <input class="form-control" name="agudeza_visual_ojo_derecho" type="text"> </td>
	                    		<td> <input class="form-control" name="distancia_pupilar_ojo_derecho" type="text"> </td>
	                    	</tr>
	                    	<tr>
	                    		<td> O. I. </td>
	                    		<td> <input class="form-control" name="esfera_ojo_izquierdo" type="text"> </td>
	                    		<td> <input class="form-control" name="cilindro_ojo_izquierdo" type="text"> </td>
	                    		<td> <input class="form-control" name="eje_ojo_izquierdo" type="text"> </td>
	                    		<td> <input class="form-control" name="adicion_ojo_izquierdo" type="text"> </td>
	                    		<td> <input class="form-control" name="agudeza_visual_ojo_izquierdo" type="text"> </td>
	                    		<td> <input class="form-control" name="distancia_pupilar_ojo_izquierdo" type="text"> </td>
	                    	</tr>
	                    </tbody>
	                </table>
	                <input type="hidden" name="no_es_paciente" value="0" id="no_es_paciente">
	            </div>
		            

				
			{{ Form::close() }}

			<br/>

			@include('ventas.incluir.elementos_remisiones_pendientes')

			<br/>



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
	
	<script src="{{ asset( 'assets/js/ventas/create.js' ) }}"></script>

	<script type="text/javascript">

		$(document).ready(function(){

			$("#agregar_examen").click(function(event){

				event.preventDefault();

				if ( $("#cliente_input").val() == '' && $("#core_tercero_id").val() == '' )
				{
					alert('Debe ingresar un tercero.');
					return false;
				}

				$('#tabla_formula').find('tbody').html('');
				$('#tabla_formula').fadeOut();
				$('#tabla_formula_no_paciente').fadeOut();

		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();
		        $(".btn_save_modal").hide();

		        var url = "{{url('form_agregar_formula_factura')}}" + "?core_tercero_id=" + $('#core_tercero_id').val();

				$.get( url )
					.done(function( respuesta ) {
						$("#div_spin").hide();
						if ( respuesta !== 'no_es_paciente')
						{
							$('#contenido_modal').html( respuesta );
							$('#no_es_paciente').val(0);
						}else{
							$('#contenido_modal').html( '' );							
	            			$('#myModal').modal("hide");
							$('#tabla_formula_no_paciente').fadeIn();
							$('#no_es_paciente').val(1);
							$('#esfera_ojo_derecho').focus();
						}

					});		        
		    });


			// Al presionar el botón "check". Nota: este botón fue creado dinámicamente, no se puede acceder a él directamente desde su ID o CLASS, sino a través de document()
			$(document).on('click', '.btn_confirmar', function(event) {
				
				event.preventDefault();

				$('#tabla_formula').fadeIn();
				$('#tabla_formula').find('tbody:last').append( $(this).closest("tr") );

				$(this).attr('style','display: none;');

				$('#contenido_modal').html( ' ' );
	            $('#myModal').modal("hide");

	            $("inv_producto_id").select();

			});


			$(document).on('click',".btn_ver_examen",function(event){
				event.preventDefault();

		        $('#contenido_modal2').html('');
				$('#div_spin').fadeIn();

		        $("#myModal2").modal(
		        	{keyboard: 'true'}
		        );

		        var url = "{{ url('consultorio_medico_get_tabla_resultado_examen/') }}" + "/" + $(this).attr('data-consulta_id') + '/' + $(this).attr('data-paciente_id') + '/' + $(this).attr('data-examen_id');

		        $.get( url, function( respuesta ){
		        	$('#div_spin').hide();
		        	$('#contenido_modal2').html( respuesta );
		        });/**/
		    });

		});
	</script>

@endsection