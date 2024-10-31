<?php
	//$items_relacionados = $registro->items_relacionados->where('estado','Activo')->all();
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
				<th>Referencia</th>
				<th>Talla</th>
				<th>Costo prom.</th>
				<th>P. ventas</th>
				<th>Acción</th>
			</tr>
		</thead>
		<tbody>
			@foreach( $items_relacionados AS $item_hijo )
				<?php
					$item = $item_hijo->item_relacionado; // $item es una instancia de InvProducto

					//$existencia_actual = $item->get_existencia_actual( $item_bodega_principal_id, date('Y-m-d') );

					$url_redirect = '[inv_item_mandatario/' . $registro->id . '?id=8&id_modelo=315&id_transaccion=]';
				?>
				<tr class="referencia_talla" data-codigo_referencia_talla="{{$item->referencia}}">
					<td class="referencia_item" align="center"><div class="elemento_modificar_no" title="Doble click para modificar." data-url_modificar="{{ url('inv_item_mandatario_update_item_relacionado') . "/referencia/" . $item->id }}"> {{ $item->referencia }}</div></td>
					<td class="talla_item" align="center"><div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('inv_item_mandatario_update_item_relacionado') . "/talla/" . $item->id }}"> {{ $item->unidad_medida2 }}</td>
					<td align="right"> ${{ number_format($item->get_costo_promedio(),0,',','.') }} </td>
					<td align="right"> ${{ number_format($item->get_precio_venta(),0,',','.') }} </td>
					<td align="right">
                        <button class="btn btn-warning btn-sm btn_edit_item_relacionado" data-item_relacionado_id="{{ $item_hijo->id }}" title="Modificar"> <i class="fa fa-edit"></i></button>
						<!-- 
						<a class="btn btn-danger btn-sm" href="{ { url('web_delete_record/8/22/' . $item->id . '/' . $url_redirect) }}" title="Eliminar talla"> <i class="fa fa-trash"></i></a>
						
						<a class="btn btn-success" href="{ { url('inventarios/create?id=8&id_modelo=248&id_transaccion=1') }}" title="Registrar entrada" target="_blank"> <i class="fa fa-arrow-up"></i></a>
						&nbsp;&nbsp;
						<a class="btn btn-danger" href="{ { url('inventarios/create?id=8&id_modelo=249&id_transaccion=3') }}" title="Registrar salida" target="_blank"> <i class="fa fa-arrow-down"></i></a>
						&nbsp;&nbsp;
						<input style="display:inline !important; width: 50px;border-radius: 4px;padding: 4px;" class="cantidad_etiquetas" type="number" min="1" value="{ {$existencia_actual}}" title="Cantidad a imprimir">
						<button class="btn btn-info btn_imprimir_etiquetas" title="Imprimir etiquetas de códigos de barras" data-mandatario_id="0" data-item_id="{ {$item->id}}"> <i class="fa fa-barcode"></i></button>

						<a class="btn btn-info btn_imprimir_etiquetas" title="Imprimir etiquetas de códigos de barras" href="{ { url('inv_item_mandatario_etiquetas_codigos_barra' . '/0/' . $item->id . '/' . $existencia_actual ) }}" target="_blank"> <i class="fa fa-barcode"></i></a>
						-->				

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
		        
				var mandatario_model_id = {{ Input::get('id_modelo') }}
		        var modelo_id = 317; // MandatarioTieneItem
				if ( mandatario_model_id == 331 ) {
					modelo_id = 332; // MandatarioProveedorTieneItem
				}

		        var url = "{{ url('inv_item_mandatario/create') }}" + "?id_modelo=" + modelo_id + "&mandatario_id=" + mandatario_id;

				$.get( url, function( data ) {
			        $('#div_cargando').hide();
		            $('#contenido_modal_item_relacionado').html(data);
					if ( document.getElementById("referencia") != null ) {
						document.getElementById("referencia").focus();
					}		            
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
		        });
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


			$(".btn_edit_item_relacionado").click(function(event){
                event.preventDefault();

                $( '#modal_item_relacionado' ).modal({backdrop: "static"});

                $("#div_cargando").show();

				$('.btn_save_modal').attr('class','class="btn btn-lg btn-primary btn_save_modal btn_edit_modal_item_relacionado');

                modelo_id = 317; // MandatarioTieneItem

                var url = url_raiz + "/inv_item_mandatario" + "/" + $(this).attr('data-item_relacionado_id') + "/edit" + "?id_modelo=" + modelo_id;

                $.get( url, function( data ) {
                    $('#div_cargando').hide();
                    $('#contenido_modal_item_relacionado').html(data);
                    document.getElementById("unidad_medida2").select();
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

			var validado = true;
		    function validar_datos()
		    {

		    	if ( $('#unidad_medida2').val() == '' )
				{
					$('#unidad_medida2').focus();
					Swal.fire({
							icon: 'error',
							title: 'Alerta!',
							text: 'Debe ingresar una Talla.'
						});
					return false;
				}

				if ( $('#precio_compra').val() != undefined ) {
					if ( !$.isNumeric( $('#precio_compra').val() ) )
					{
						$('#precio_compra').focus();
						Swal.fire({
								icon: 'error',
								title: 'Alerta!',
								text: 'Debe ingresar un precio de compras válido.'
							});
						return false;
					}
				}
				
				if ( $('#precio_venta').val() != undefined ) {
					if ( !$.isNumeric( $('#precio_venta').val() ) )
					{
						$('#precio_venta').focus();
						Swal.fire({
								icon: 'error',
								title: 'Alerta!',
								text: 'Debe ingresar un precio de ventas válido.'
							});
						return false;
					}
				}

		    	$(".referencia_talla").each(function() {
					valor_referencia_talla = $(this).attr('data-codigo_referencia_talla');

					if ( valor_referencia_talla == $('#referencia').val() + '-' + $('#unidad_medida2').val().toUpperCase() )
					{
						Swal.fire({
							icon: 'error',
							title: 'Alerta!',
							text: 'Ya se ingresó la Talla << ' + $('#unidad_medida2').val().toUpperCase() + ' >> para este Ítem.'
						});
						validado = false;
						return false;
					}
				});			    	

				return validado;
		    }

		    $(document).on('keyup','#referencia',function(event){
		    	var codigo_tecla_presionada = event.which || event.keyCode;
		    	if ( codigo_tecla_presionada == 13 )
				{
					$('#unidad_medida2').focus();
				}
		    });

		    $(document).on('keyup','#unidad_medida2',function(event){
		    	var codigo_tecla_presionada = event.which || event.keyCode;
		    	if ( codigo_tecla_presionada == 13 )
				{
					$('#cantidad').focus();
				}
		    });

		    $(document).on('keyup','#cantidad',function(event){
		    	var codigo_tecla_presionada = event.which || event.keyCode;
		    	if ( codigo_tecla_presionada == 13 )
				{
					$('.btn_save_modal_item_relacionado').focus();
				}
		    });

		});
	</script>
	<script src="{{ asset( 'assets/js/modificar_con_doble_click_sin_validar_valor.js' ) }}"></script>
@endsection