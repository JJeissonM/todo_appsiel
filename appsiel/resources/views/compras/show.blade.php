<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
?>

@extends('transaccion.show')

@section('botones_acciones')
	@if($doc_encabezado->estado != 'Anulado')
        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
		<a href="{{ url('tesoreria/pagos_cxp/create?id=3&id_modelo=150&id_transaccion=33') }}" target="_blank" class="btn-gmail" title="Hacer abono"><i class="fa fa-btn fa-money"></i></a>	
        @if(!$docs_relacionados[1])
        	<!-- WARNING: Solo se hacen notas para facturas con una sola éntrada de almacén -->
        	<a class="btn-gmail" href="{{ url('compras_notas_credito/create?factura_id='.$id.'&id='.Input::get('id').'&id_modelo=166&id_transaccion=36') }}" title="Nota crédito"><i class="fa fa-file-text"></i></a>
        @endif
		
        @can('compras_recontabilizar_un_documento')
        	<a class="btn-gmail" href="{{ url( 'compras_recontabilizar_un_documento/'.$id.$variables_url ) }}" title="Recontabilizar"><i class="fa fa-cog"></i></a>
        @endcan
    @endif

	@include('compras.doc_soporte.acciones_doc_soporte_electronico')
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS','estandar2'=>'Estándar v2'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'compras_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'compras/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
	<br/>
	<b>Entrada(s) almacén: </b> {!! $docs_relacionados[0] !!}
	<br/>
    <b>Orden de compras: &nbsp;&nbsp;</b> {{ $doc_encabezado->orden_compras }}
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Proveedor:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
        </td>
        <td style="border: solid 1px #ddd;">
            <b>Fact. del proveedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->doc_proveedor_prefijo }} - {{ $doc_encabezado->doc_proveedor_consecutivo }}
            <br/>
            <b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
            <br/>
            <b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}
            
        </td>
    </tr>
    <tr>        
        <td colspan="2" style="border: solid 1px #ddd;">
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
	{{ Form::open(['url'=>'compras_anular_factura', 'id'=>'form_anular']) }}
		<div class="alert alert-warning" style="display: none;">
			<a href="#" id="close" class="close">&times;</a>
			<strong>Advertencia!</strong>
			<br>
			Al anular el documento se eliminan los registros de la Entrada de Almacén, Cuentas por Pagar y el movimiento contable relacionado.
			<br>
			¿Desea eliminar también la(s) entrada(s) de almacén? 
			<label class="radio-inline"> <input type="radio" name="anular_entrada_almacen" value="1" id="opcion1">Si</label>
			<label class="radio-inline"> <input type="radio" name="anular_entrada_almacen" value="0" id="opcion2">No</label>
			<br>
			Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="#" id="enlace_anular" data-url="{{ url('compras_anular_factura/'.$id.$variables_url ) }}"> Anular </a> </small>
		</div>

				{{ Form::hidden('url_id', Input::get('id')) }}
				{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
				{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

				{{ Form::hidden( 'factura_id', $id ) }}

	{{ Form::close() }}
@endsection

@section('documento_vista')
	@include('compras.incluir.documento_vista')
@endsection

@section('registros_otros_documentos')
	{!! $medios_pago !!}
	@include('compras.incluir.registros_abonos')
	@include('compras.incluir.notas_credito')
@endsection

@section('otros_scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$(".btn_editar_registro").click(function(event){
		        $('#contenido_modal').html('');
		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = '../compras_get_formulario_edit_registro';

				$.get( url, { linea_registro_id: $(this).attr('data-linea_registro_id'), id: getParameterByName('id'), id_modelo: getParameterByName('id_modelo'), id_transaccion: getParameterByName('id_transaccion') } )
					.done(function( data ) {

		                $('#contenido_modal').html(data);

		                $("#div_spin").hide();

		                $('#precio_unitario').select();

					});		        
		    });

		    // Al modificar el precio de compra
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

		    // Al modificar el precio de compra
            $(document).on('keyup','#cantidad',function(event){
				if( validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					calcula_nuevo_saldo_a_la_fecha();

					var x = event.which || event.keyCode;
					
					if( x==13 )
					{
						if ( !validar_saldo_a_la_fecha() )
						{
							return false;
						}
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

            $('.btn_save_modal').click(function(){
            	if ( $.isNumeric( $('#precio_total').val() ) && $('#precio_total').val() > 0 )
            	{
            		if ( !validar_saldo_a_la_fecha() )
					{
						return false;
					}
                    validacion_saldo_movimientos_posteriores();
            	}else{
                    alert('El precio total es incorrecto. Verifique lo valores ingresados.');
                }
            });

            $("#myModal").on('hide.bs.modal', function(){
                $('#popup_alerta_danger').hide();
            });
            
            function validacion_saldo_movimientos_posteriores()
            {
                var url = '../inv_validacion_saldo_movimientos_posteriores/' + $('#bodega_id').val() + '/' + $('#producto_id').val() + '/' + $('#fecha').val() + '/' + $('#cantidad').val() + '/' + $('#saldo_a_la_fecha2').val() + '/entrada';

                $.get( url )
                    .done( function( data ) {
                        if ( data != 0 )
                        {
                            $('#popup_alerta_danger').show();
                            $('#popup_alerta_danger').text( data );
                        }else{
                            $('.btn_save_modal').off( 'click' );
                            $('#form_edit').submit();
                            //console.log('a guardar');
                            $('#popup_alerta_danger').hide();
                        }
                    });

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

			/*
				validar_existencia_actual
				WARNING: Es diferente al validación de ventas o movimientos de salida de inventarios
			*/
			function validar_saldo_a_la_fecha()
			{
				if ( $('#tipo').val() == 'servicio' ) { return true; }

				if ( parseFloat( $('#saldo_a_la_fecha').val() ) < 0 ) 
                {
                    alert('Saldo negativo a la fecha.');
                    $('#cantidad').val('');
                    $('#cantidad').focus();
                    return false;
                }
				return true;
			}

			function calcula_nuevo_saldo_a_la_fecha()
			{
				var saldo_actual = parseFloat( $('#saldo_a_la_fecha').val() );
				var cantidad_anterior = parseFloat( $('#cantidad_anterior').val() );
				var nuevo_saldo = saldo_actual - cantidad_anterior + parseFloat( $('#cantidad').val() );

				$('#saldo_a_la_fecha').val( nuevo_saldo );
				$('#saldo_a_la_fecha2').val( nuevo_saldo );
				$('#cantidad_anterior').val( $('#cantidad').val() );
			}

		});
	</script>
@endsection