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
				
			{{ Form::close() }}

			<br/>

			@include('ventas.incluir.elementos_remisiones_pendientes')

			<br/>


			<h4>Facturación de formula</h4>
	        <hr>
	        <button class="btn btn-primary btn-xs agregar_examen"><i class="fa fa-btn fa-plus"></i> Agregar fórmula </button>


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

	@include( 'components.design.ventana_modal',['titulo'=>'Agregar precio de producto','texto_mensaje'=>'','contenido_modal' => $contenido_modal] )

	<br/><br/>
@endsection

@section('scripts')
	
	<script src="{{ asset( 'assets/js/ventas/create.js' ) }}"></script>

	<script type="text/javascript">
		$(document).ready(function(){

			$(".btn_agregar_precio").click(function(event){
		        $("#myModal").modal({backdrop: "static"});
		        $('#inv_producto_id').focus();
		        $(".btn_edit_modal").hide();
		    });

			$(".agregar_examen").click(function(event){
				event.preventDefault();

				elementos_consulta_actual = $(this).parents(".secciones_consulta");

				// El ID de la formula lo tiene una etiqueta div en el título
				var formula_id = elementos_consulta_actual.find(".formula_id").html();

				// Se reutiliza la caja modal, se oculta el botón editar y se cambia el título
				$(".btn_edit_examen").hide();
				$("#myModal").modal(
		        	{keyboard: 'true'}
		        );
		        $(".modal-title").html( "Haga click sobre los exámenes que quiera asociar a la fórmula." );
		        $("#alert_mensaje").hide();

		        // Se recorren los botones de la pestaña Exámenes, pues estos son los exámenes que realmente tiene el paciente en esta consulta
		        var botones = '';
		        elementos_consulta_actual.find(".btns_examenes button").each(function(){

		        	var texto_boton = $(this).attr('data-examen_descripcion');
		        	var examen_realizado_id = $(this).attr('data-examen_id');

		        	// Se valida que ya no esté asociado el exámen
		        	// Se recorren los botones YA asociados a la fórmula y se valida si ya está
		        	var esta = false;
		        	elementos_consulta_actual.find(".desasociar_examen").each(function(){
		        		if ( examen_realizado_id == $(this).attr('data-examen_id')) {
		        			esta = true;
		        		}
		        	});
		        	if ( !esta ) {
		        		botones = botones + '<p data-examen_descripcion="' + texto_boton + '">' + texto_boton + '  <button class="asociar_examen btn btn-xs btn-default" data-formula_id="' + formula_id + '" data-examen_id="' + examen_realizado_id + '" > <i class="fa fa-check"></i> </button></p>';
		        	}						
				});

		        // Se agrega al cuerpo de la ventana modal el listado de los exámenes que se le han practicado al paciente y que no se hayan asignado a la fórmula.
				$("#info_examen").html( botones );
			});


			// Al presionar el botón "check". Nota: este botón fue creado dinámicamente, no se puede acceder a él directamente desde su ID o CLASS, sino a través de document()
			$(document).on('click', '.asociar_examen', function() {
				$("#div_spin").show();

				console.log( elementos_consulta_actual );

				var linea = $(this).parent('p');
				var examen_descripcion = linea.attr('data-examen_descripcion');
				var formula_id = $(this).attr('data-formula_id');
				var examen_id = $(this).attr('data-examen_id');

				var url = "../../consultorio_medico/asociar_examen/formulas_opticas/" + formula_id + "/" + examen_id;

				$.get( url, function( respuesta ){
					$("#div_spin").hide();
					alert( "El exámen fue asignado correctamente!" );
					linea.remove();

					elementos_consulta_actual.find(".btns_examenes_asignados").append( '<span class="label label-default label-md"> <span> ' +  examen_descripcion + ' </span> <button class="desasociar_examen" style="background-color: transparent;" data-formula_id="' + formula_id + '" data-examen_id="' + examen_id + '">&times;</button> </span> &nbsp;&nbsp;&nbsp;' );
				});				
			});

			// Al presionar el botón X del exámen asociado.
			$(document).on('click', '.desasociar_examen', function() {
				event.preventDefault();

				elementos_consulta_actual = $(this).parents(".secciones_consulta");

				var linea = $(this).parent('span');

				if (confirm("Realmente quiere quitar el examen " + $(this).prev().html() + " de la formula?" )) {			

					$("#div_cargando").show();

					var url = "../../consultorio_medico/quitar_examen/formulas_opticas/" + $(this).attr('data-formula_id') + "/" + $(this).attr('data-examen_id');

					$.get( url, function( respuesta ){
						$("#div_cargando").hide();
						linea.remove();
						alert( "El exámen fue removido correctamente!" );
					});					  	
				}
			});
		});
	</script>

@endsection