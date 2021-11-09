<?php
	$items_relacionados = $registro->items_relacionados->where('estado','Activo')->all();
	$altura_en_pulgadas = 0;
?>
<br>
<div id="tabla_items_relacionados">
	<h5 style="width: 100%; text-align: center;">Registros de tallas</h5>
	<div class="row" style="padding:5px;">
		<div class="col-md-6">
			<?php 
				$item_bodega_principal_id = (int)config( 'inventarios.item_bodega_principal_id' );
				if( !is_null( Input::get('bodega_id') ) )
				{
					$item_bodega_principal_id = Input::get('bodega_id');
				}
			?>
			{{ Form::bsSelect( 'item_bodega_principal_id', $item_bodega_principal_id, 'Bodega', App\Inventarios\InvBodega::opciones_campo_select(), ['class'=>'form-control']) }}
		</div>
		<div class="col-md-6">
			<button class="btn btn-info" title="Imprimir etiquetas de códigos de barras" onclick="ventana_imprimir(this);" data-mandatario_id="{{ $registro->id }}" data-item_id="0"> <i class="fa fa-barcode"></i></button>
		</div>
	</div>
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Referencia</th>
				<th>Talla</th>
				<th>Cantidad</th>
				<th>Acción</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $items_relacionados AS $item )
				<?php 
					$existencia_actual = $item->get_existencia_actual( $item_bodega_principal_id, date('Y-m-d') );
				?>
				<tr>
					<td class="referencia_item" align="center"><div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('inv_item_mandatario_update_item_relacionado') . "/referencia/" . $item->id }}"> {{ $item->referencia }}</div></td>
					<td class="talla_item" align="center"><div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('inv_item_mandatario_update_item_relacionado') . "/talla/" . $item->id }}"> {{ $item->unidad_medida2 }}</td>
					<td align="center"> {{ $existencia_actual }} </td>
					<td>
						<a class="btn btn-success" href="{{ url('inventarios/create?id=8&id_modelo=248&id_transaccion=1') }}" title="Registrar entrada" target="_blank"> <i class="fa fa-arrow-up"></i></a>
						&nbsp;&nbsp;
						<a class="btn btn-danger" href="{{ url('inventarios/create?id=8&id_modelo=249&id_transaccion=3') }}" title="Registrar salida" target="_blank"> <i class="fa fa-arrow-down"></i></a>
						&nbsp;&nbsp;
						<input style="display:inline !important; width: 50px;border-radius: 4px;padding: 4px;" class="cantidad_etiquetas" type="number" min="1" value="{{$existencia_actual}}" title="Cantidad a imprimir">
						<!-- <button class="btn btn-info btn_imprimir_etiquetas" title="Imprimir etiquetas de códigos de barras" data-mandatario_id="0" data-item_id="{ {$item->id}}"> <i class="fa fa-barcode"></i></button>
						-->
						
						<a class="btn btn-info btn_imprimir_etiquetas" title="Imprimir etiquetas de códigos de barras" href="{{ url('inv_item_mandatario_etiquetas_codigos_barra' . '/0/' . $item->id . '/' . $existencia_actual ) }}" target="_blank"> <i class="fa fa-barcode"></i></a>

						

					</td>
				</tr>
				<?php 
					$altura_en_pulgadas++;
				?>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="5">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_item_relacionado">
                    	<i class="fa fa-btn fa-plus"></i> Agregar registro
                    	<span data-mandatario_id="{{ $registro->id }}"></span>
                    </button>
                </td>
            </tr>
        </tfoot>
	</table>

	@include('inventarios.items.item_relacionado_modal', [ 'mandatario_id' => $registro->id ])

</div>

@section('scripts8')

	<script type="text/javascript">

		// 96 px = 1 in
		/*var altura_hoja_imprimir = "{ {$altura_en_pulgadas * 96}}";
		var ancho_hoja_imprimir = 96 * 4.06;
		console.log(altura_hoja_imprimir);
		*/
		$(document).ready(function(){

			$(".btn_nuevo_item_relacionado").click(function(event){

				event.preventDefault();
		        
		        var mandatario_id = $(this).children('span').attr('data-mandatario_id');
				
		        $( '#modal_item_relacionado' ).modal({backdrop: "static"});

		        $("#div_cargando").show();
		        
		        var modelo_id = 317;

		        var url = "{{ url('inv_item_mandatario/create') }}" + "?id_modelo=" + modelo_id + "&mandatario_id=" + mandatario_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_item_relacionado').html(data);
				});		        
		    });


			// GUARDAR 
			$(document).on("click",".btn_save_modal_item_relacionado",function(event){

		    	event.preventDefault();
		        
		        if ( !validar_datos() )
		        {
		        	return false;
		        }
		        
		        $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );

		        var mandatario_id = $(this).children('span').attr('data-mandatario_id');
		        formulario = $('#modal_item_relacionado').find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {
		        	location.reload(true);
		        });/**/
		    });


			$(document).on( "change", ".cantidad_etiquetas",function(event){

		    	event.preventDefault();

		    	var input_cantidad = $(this);

		    	var btn_imprimir = $(this).next();
		    	var url_imprimir = btn_imprimir.attr('href');
		    	var url_separada = url_imprimir.split('/');
		    	
		    	var nueva_url = url_separada[0] + '//' + url_separada[2] + '/' + url_separada[3] + '/' + url_separada[4] + '/' + url_separada[5] + '/' + url_separada[6] + '/' + input_cantidad.val();

		    	btn_imprimir.attr( 'href', nueva_url );
		    });


		    function validar_datos()
		    {
		    	if ( $('#referencia').val() == '' )
				{
					$('#referencia').focus();
					alert('Debe ingresar una Referencia.');
					return false;
				}
		    	if ( $('#unidad_medida2').val() == '' )
				{
					$('#unidad_medida2').focus();
					alert('Debe ingresar una Talla.');
					return false;
				}

		    	$(".referencia_item").each(function() {
					var val = $(this).text();
					if ( val == $('#referencia').val() )
					{
						$('#referencia').focus();
						alert('La Referencia ingresada Ya existe para este Producto.');
						return false;
					}
				});

		    	$(".talla_item").each(function() {
					var val = $(this).text();
					if ( val == $('#unidad_medida2').val() )
					{
						$('#unidad_medida2').focus();
						alert('La Talla ingresada Ya existe para este Producto.');
						return false;
					}
				});

				return true;
		    }


		});
	</script>
	<script src="{{ asset( 'assets/js/modificar_con_doble_click_sin_validar_valor.js' ) }}"></script>
@endsection