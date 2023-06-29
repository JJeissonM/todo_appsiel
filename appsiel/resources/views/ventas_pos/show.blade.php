<?php  
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
?>

@extends('transaccion.show')

@section('botones_acciones')	
	@if( $doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado != 'Pendiente' )

		@if($doc_encabezado->condicion_pago != 'contado')
	    	<a href="{{ url('tesoreria/recaudos_cxc/create?id='.Input::get('id').'&id_modelo=153&id_transaccion=32') }}" target="_blank" class="btn-gmail" title="Hacer abono"><i class="fa fa-btn fa-money"></i></a>
		@endif

		@if( $doc_encabezado->estado != 'Enviada' && $doc_encabezado->estado != 'Contabilizado - Sin enviar' )
	    	<button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-btn fa-close"></i></button>

			@can('Facturación Electrónica')
				<button class="btn-gmail" id="btn_convertir_en_factura_electronica" data-href="{{ url( 'fe_convertir_en_factura_electronica/'. $id . $variables_url ) }}" title="Convertir en Factura Electrónica"><i class="fa fa-file-text-o"></i></button>
			@endcan
		@endif

        @can('vtas_recontabilizar')
        	<a class="btn-gmail" href="{{ url( 'factura_pos_recontabilizar/'.$id.$variables_url ) }}" title="Recontabilizar"><i class="fa fa-cog"></i></a>
        @endcan
        
	@endif
	
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['pos'=>'POS','estandar'=>'Estándar'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'vtas_imprimir/'.$id.$variables_url.'&formato_impresion_id=pos' ) }}
	{{ Form::bsBtnEmail( 'vtas_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=pos' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'pos_factura/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
	<br/>
	<b>Remisión: </b> {!! $docs_relacionados[0] !!}
	@if (!empty($pedidos_padres->toArray()))
		<br/>
		<b>Pedidos: </b>
		<?php 
			$es_el_primero = true;
		?>
		@foreach ( $pedidos_padres AS $pedido )
			@if($es_el_primero)
				{!! $pedido->get_link_pedido() !!}
			@else
				, {!! $pedido->get_link_pedido() !!}
			@endif
			<?php 
				$es_el_primero = false;
			?>
		@endforeach
	@endif
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Cliente:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
        </td>
        <td style="border: solid 1px #ddd;">
            <b>Vendedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->vendedor->tercero->descripcion }}
            <br/>
            <b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
            <br/>
            <b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}
            <br/>
            <b>Orden de compras: &nbsp;&nbsp;</b> {{ $doc_encabezado->orden_compras }}
        </td>
    </tr>
    <tr>        
        <td colspan="2" style="border: solid 1px #ddd;">
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
	{{ Form::open(['url'=>'ventas_pos_anular_factura', 'id'=>'form_anular']) }}
		<div class="alert alert-warning" style="display: none;">
			<a href="#" id="close" class="close">&times;</a>
			<strong>Advertencia!</strong>
			<br>
			Al anular el documento se eliminan los registros de la Remisión de inventarios, Cuentas por Cobrar y el movimiento contable relacionado.
			<br>
			¿Desea eliminar también la remisión o remisiones? 
			<label class="radio-inline"> <input type="radio" name="anular_remision" value="1" id="opcion1">Si</label>
			<label class="radio-inline"> <input type="radio" name="anular_remision" value="0" id="opcion2">No</label>
			<br>
			Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="#" id="enlace_anular" data-url="{{ url('ventas_anular_factura/'.$id.$variables_url ) }}"> Anular </a> </small>
		</div>

				{{ Form::hidden('url_id', Input::get('id')) }}
				{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
				{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

				{{ Form::hidden( 'factura_id', $id ) }}

	{{ Form::close() }}
@endsection

@section('documento_vista')

	@include('ventas.incluir.documento_vista')
	
	<input name="datos_doc_encabezado" id="datos_doc_encabezado" type="hidden" value="{{ $doc_encabezado->documento_transaccion_descripcion.' '.$doc_encabezado->documento_transaccion_prefijo_consecutivo }}">

	
	@include('components.design.ventana_modal2',['titulo2'=>'','texto_mensaje2'=>''])

@endsection

@section('registros_otros_documentos')
	@include('ventas.incluir.registros_abonos')
	@include('ventas.incluir.notas_credito')
@endsection

@section('otros_scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#btn_create_general').hide();

			$(".btn_editar_registro").click(function(event){
		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = '../vtas_get_formulario_edit_registro';

				$.get( url, { 
								linea_registro_id: $(this).attr('data-linea_registro_id'), 
								id: getParameterByName('id'), 
								id_modelo: getParameterByName('id_modelo'), 
								id_transaccion: getParameterByName('id_transaccion')
							} )
					.done(function( data ) {

						$('#saldo_original').val( $('#saldo_a_la_fecha').val() );
						$('#cantidad_original').val( $('#cantidad').val() );

		                $('#contenido_modal').html(data);

		                $("#div_spin").hide();

		                $('#precio_unitario').select();

					});		        
		    });

		    // Al modificar el precio 
            $(document).on('keyup','#precio_unitario',function(event){
				
				if( validar_input_numerico( $(this) ) )
				{	

					var x = event.which || event.keyCode;
					if( x==13 )
					{
						$('#cantidad').select();				
					}

					calcular_valor_descuento();

					calcular_precio_total();

				}else{
					$(this).focus();
					return false;
				}

			});

		    // Al modificar la cantidad
            $(document).on('keyup','#cantidad',function(event){
				
				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{	
					calcula_nuevo_saldo_a_la_fecha();
					if ( !validar_existencia_actual() )
					{
						$('#precio_total').val('');
						return false;
					}

					var x = event.which || event.keyCode;
					if( x==13 )
					{
						$('#tasa_descuento').select();
					}

					calcular_valor_descuento();

					calcular_precio_total();
					
				}else{
					$(this).focus();
					return false;
				}

			});


            $(document).on('keyup','#tasa_descuento',function(event){
            	if( validar_input_numerico( $(this) ) )
				{	
					// máximo valor de 100
					if ( $(this).val() > 100 )
					{ 
						$(this).val(100);
					}

					var x = event.which || event.keyCode;
					if( x == 13 )
					{
						$('.btn_save_modal').focus();
						return true;
					}
					
					calcular_valor_descuento();

					calcular_precio_total();

				}else{

					$(this).focus();
					return false;
				}
			});

			function calcular_valor_descuento()
			{
				var valor_total_descuento = $('#precio_unitario').val() * $('#tasa_descuento').val() / 100 * $('#cantidad').val();

				$('#valor_total_descuento_no').val( valor_total_descuento );
				$('#valor_total_descuento').val( valor_total_descuento );
			}



			function calcular_precio_total()
			{
				var valor_total_descuento = parseFloat( $('#valor_total_descuento').val() );

				var precio_unitario = parseFloat( $('#precio_unitario').val() );

				var cantidad = parseFloat( $('#cantidad').val() );
				
				var precio_total = precio_unitario * cantidad - valor_total_descuento;

				$('#precio_total').val( precio_total );
			}


            $('#enlace_anular').click(function(){
            	
            	if ( !$("#opcion1").is(":checked") && !$("#opcion2").is(":checked") )
            	{
            		alert('Debe escoger una opción.');
            		$("#opcion1").focus();
            		return false;
            	}

            	$('#form_anular').submit();

            });

            $("#myModal").on('hide.bs.modal', function(){
                $('#popup_alerta_danger').hide();
            });

			/*
				validar_existencia_actual
			*/
			function validar_existencia_actual()
			{
				if ( $('#tipo').val() == 'servicio' ) { return true; }

				if ( parseFloat( $('#saldo_a_la_fecha').val() ) < 0 ) 
				{
					alert('Nueva EXISTENCIA negativa.');
					$('#cantidad').val('');
					$('#cantidad').focus();
					return false;
				}
				return true;
			}

			function validar_venta_menor_costo()
			{
				var ok = true;
				var costo_unitario = parseFloat ( $('#linea_ingreso_default').find('.costo_unitario').html() );
				var base_impuesto = parseFloat ( $('#linea_ingreso_default').find('.base_impuesto').html() );

				if ( base_impuesto < costo_unitario)
				{
					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color','red');
					$('#popup_alerta').text( 'El precio está por debajo del costo de venta del producto.' + ' $'+ new Intl.NumberFormat("de-DE").format( costo_unitario.toFixed(2) ) + ' + IVA' );
					ok = false;
				}else{
					$('#popup_alerta').hide();
					ok = true;
				}

				return ok;
			}
            
            function validacion_saldo_movimientos_posteriores()
            {
                var url = '../inv_validacion_saldo_movimientos_posteriores/' + $('#bodega_id').val() + '/' + $('#producto_id').val() + '/' + $('#fecha').val() + '/' + $('#cantidad').val() + '/' + $('#saldo_a_la_fecha2').val() + '/salida';

                $.get( url )
                    .done( function( data ) {
                        if ( data != 0 )
                        {
                            $('#popup_alerta_danger').show();
                            $('#popup_alerta_danger').text( data );
                        }else{
                            $('.btn_save_modal').off( 'click' );
                            $('#form_edit').submit();
                            $('#popup_alerta_danger').hide();
                        }
                    });
            }

			function calcula_nuevo_saldo_a_la_fecha()
			{
				var nuevo_saldo = parseFloat( $('#saldo_original').val() ) + parseFloat( $('#cantidad_original').val() ) - parseFloat( $('#cantidad').val() );

				$('#saldo_a_la_fecha').val( nuevo_saldo );
				$('#saldo_a_la_fecha2').val( nuevo_saldo );
			}

			$("#myModal").on('hide.bs.modal', function(){
                $('#popup_alerta_danger').hide();
            });


			// Convertir en Fact. Elect.
			$("#btn_convertir_en_factura_electronica").click(function (event) {
				event.preventDefault();
				
				$('#contenido_modal2').html('<h2>'+$("#datos_doc_encabezado").val()+'</h2><h3>¿Realmente desea convertir este documento en un <b>Factura Electrónica</b>?</h3>');

				$("#myModal2").modal(
					{keyboard: true}
				);

				$("#myModal2 .modal-title").text('Convertir documento en Factura Electrónica');

				$("#myModal2 .btn_edit_modal").hide();
				$("#myModal2 .btn_save_modal").html('<i class="fa fa-file-text-o"></i> Convertir');
				
				$("#myModal2 .btn_save_modal").attr( 'data-href', $(this).attr( 'data-href') );

			});

			
            $('.btn_save_modal').click(function(event){
				// Desactivar el click del botón
		        $(this).children('.fa-file-text-o').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );
				$( this ).off( event );
				location.href = $(this).attr( 'data-href' );
			});

		});
	</script>
@endsection