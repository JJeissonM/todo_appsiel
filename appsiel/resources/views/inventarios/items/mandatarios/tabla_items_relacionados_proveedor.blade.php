<?php
	$items_relacionados = App\Inventarios\MandatarioProveedorTieneItem::where('mandatario_id',$registro->id)->get();
	$altura_en_pulgadas = 0;
?>
<br>
<div id="tabla_items_relacionados">
	<h5 style="width: 100%; text-align: center;">
		Items relacionados
		<br>
		<!-- <small>Haga Doble clic en el recuadro de la Talla para cambiarla.</small> -->
	</h5>
	
	<div class="row" style="padding:5px;">
		<div class="col-md-6">
			<?php 
				$item_bodega_principal_id = (int)config( 'inventarios.item_bodega_principal_id' );
				if( !is_null( Input::get('bodega_id') ) )
				{
					$item_bodega_principal_id = Input::get('bodega_id');
				}
			?>
			<!-- 
			{ { Form::bsSelect( 'item_bodega_principal_id', $item_bodega_principal_id, 'Bodega', App\Inventarios\InvBodega::opciones_campo_select(), ['class'=>'form-control']) }}
			-->
		</div>
		<div class="col-md-6">
			<!-- <a class="btn btn-info" title="Imprimir etiquetas de códigos de barras" href="{ { url('inv_item_mandatario_etiquetas_codigos_barra' . '/' . $registro->id . '/0/0' ) }}" target="_blank"> <i class="fa fa-barcode"></i></a>
			-->
		</div>
	</div>

	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Proveedor</th>
				<th>P. compras</th>
				<th>P. ventas</th>
				<th>Cód. barras</th>
				<th>Referencia</th>
				<th>Estado</th>
				<th>Acción</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $items_relacionados AS $item )
				<?php
					//$existencia_actual = $item->get_existencia_actual( $item_bodega_principal_id, date('Y-m-d') );

					$url_redirect = '[inv_item_mandatario/' . $registro->id . '?id=8&id_modelo=315&id_transaccion=]';
				?>
				<tr>
                    <td align="center"> {{ $item->proveedor()->tercero->descripcion }} </td>
					<td align="right"> ${{ number_format($item->item_relacionado->get_costo_promedio(),0,',','.') }} </td>
					<td align="right"> ${{ number_format($item->item_relacionado->get_precio_venta(),0,',','.') }} </td>
					<td align="center"> {{ $item->item_relacionado->codigo_barras }} </td>
					<td align="center"> {{ $item->item_relacionado->referencia }} </td>
					<td align="center"> {{ $item->item_relacionado->estado }} </td>
					<td>
                        <button class="btn btn-warning btn-sm btn_edit_item_relacionado" data-item_relacionado_id="{{ $item->id }}" title="Modificar"> <i class="fa fa-edit"></i></button>
					</td>
				</tr>
				<?php 
					$altura_en_pulgadas++;
				?>
			@endforeach
		</tbody>
		<tfoot>
            <tr>
                <td colspan="6">
                    <button style="background-color: transparent; color: #3394FF; border: none;" class="btn_nuevo_item_relacionado">
                    	<i class="fa fa-btn fa-plus"></i> Agregar registro
                    	<span data-mandatario_id="{{ $registro->id }}"></span>
                    </button>
                </td>
            </tr>
        </tfoot>
	</table>

	@include('inventarios.items.item_relacionado_modal', [ 'mandatario_id' => $registro->id, 'title' => 'Registro de Items por Proveedor', 'class_btn_save' => 'btn_edit_modal_item_relacionado' ])

</div>

@section('scripts8')

	<script type="text/javascript">

		$(document).ready(function(){

			$(".btn_nuevo_item_relacionado").click(function(event){

				event.preventDefault();
		        
		        var mandatario_id = $(this).children('span').attr('data-mandatario_id');
				
		        $( '#modal_item_relacionado' ).modal({backdrop: "static"});

		        $("#div_cargando").show();
				
				$('.btn_save_modal').attr('class','class="btn btn-lg btn-primary btn_save_modal btn_save_modal_item_relacionado');
		        
				modelo_id = 332; // MandatarioProveedorTieneItem

		        var url = "{{ url('inv_item_mandatario/create') }}" + "?id_modelo=" + modelo_id + "&mandatario_id=" + mandatario_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_item_relacionado').html(data);
		            document.getElementById("categoria_id").focus();
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

		        var mandatario_id = $(this).children('span').attr('data-mandatario_id');
		        formulario = $('#modal_item_relacionado').find('form');

		        var url = formulario.attr('action');
		        var data = formulario.serialize();

		        $.post(url, data, function (respuesta) {
		        	location.reload(true);
		        });/**/
		    });
            

			$(".btn_edit_item_relacionado").click(function(event){
                event.preventDefault();

                $( '#modal_item_relacionado' ).modal({backdrop: "static"});

                $("#div_cargando").show();

				$('.btn_save_modal').attr('class','class="btn btn-lg btn-primary btn_save_modal btn_edit_modal_item_relacionado');

                modelo_id = 332; // MandatarioProveedorTieneItem

                var url = url_raiz + "/inv_item_mandatario" + "/" + $(this).attr('data-item_relacionado_id') + "/edit" + "?id_modelo=" + modelo_id;

                $.get( url, function( data ) {
                    $('#div_cargando').hide();
                    $('#contenido_modal_item_relacionado').html(data);
                    document.getElementById("categoria_id").focus();
                });		        
            });

            // MODIFICAR 
			$(document).on("click",".btn_edit_modal_item_relacionado",function(event){

                event.preventDefault();

                if ( !validar_datos() )
                {
                    return false;
                }

                $(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');

                var mandatario_id = $(this).children('span').attr('data-mandatario_id');
                formulario = $('#modal_item_relacionado').find('form');

                var url = formulario.attr('action');
                var data = formulario.serialize();

                $.ajax({
                    url: url,
                    type: 'PUT',
                    data: data,
                    success: function(data) {
                        location.reload(true);
                    }
                });
            });            

		    function validar_datos()
		    {
				if ( $('#categoria_id').val() == '' )
				{
					$('#categoria_id').focus();
					alert('Debe seleccionar Proveedor.');
					return false;
				}

				if ( !$.isNumeric( $('#precio_compra').val() ) )
				{
					$('#precio_compra').focus();
					alert('Debe ingresar un precio de compras válido.');
					return false;
				}

				if ( !$.isNumeric( $('#precio_venta').val() ) )
				{
					$('#precio_venta').focus();
					alert('Debe ingresar un precio de ventas válido.');
					return false;
				}		    	

				return true;
		    }

		});
	</script>
	<script src="{{ asset( 'assets/js/modificar_con_doble_click_sin_validar_valor.js' ) }}"></script>
@endsection