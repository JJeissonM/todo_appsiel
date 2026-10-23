<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	@if($doc_encabezado->estado != 'Anulado')
        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
    @endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'compras_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'compras/', $variables_url.'&vista=compras.notas_credito.por_valor.show' ) !!}
@endsection

@section('datos_adicionales_encabezado')
	<?php
		$factura_relacionada = App\Compras\ComprasDocEncabezado::with(['tipo_transaccion', 'tipo_documento_app'])
			->where('core_empresa_id', $doc_encabezado->core_empresa_id)
			->find($doc_encabezado->compras_doc_relacionado_id);
	?>
	<br/>
	<b>Factura de compra relacionada: </b>
	@if($factura_relacionada && $factura_relacionada->tipo_transaccion && $factura_relacionada->tipo_documento_app)
		<a href="{{ url('compras/'.$factura_relacionada->id.'?id='.$factura_relacionada->tipo_transaccion->core_app_id.'&id_modelo='.$factura_relacionada->tipo_transaccion->core_modelo_id.'&id_transaccion='.$factura_relacionada->core_tipo_transaccion_id) }}">
			{{ $factura_relacionada->tipo_documento_app->prefijo }} {{ $factura_relacionada->consecutivo }}
		</a>
	@else
		Sin factura relacionada
	@endif
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
            <b>Factura del proveedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->doc_proveedor_prefijo }} - {{ $doc_encabezado->doc_proveedor_consecutivo }}
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
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong> Advertencia!</strong>
		<br>
		Al anular el documento se eliminan los registros de la devolución, Cuentas por Pagar y el movimiento contable relacionado.
		<br>
		<?php 
			$url = 'compras_notas_credito_valor_anular';
		?>
		Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url( $url.'/'.$id.$variables_url ) }}" id="enlace_anular"> Anular </a> </small>
	</div>
@endsection

@section('documento_vista')
	@include('compras.incluir.documento_vista')
@endsection

@section('registros_otros_documentos')
	@include('compras.incluir.registros_abonos')
@endsection

@section('otros_scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$(".btn_editar_registro").click(function(event){
		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = '../compras_get_formulario_edit_registro';

		        console.log( $(this).attr('data-linea_registro_id') );

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

					$('#precio_total').val( parseFloat( $('#precio_unitario').val() ) * parseFloat( $('#cantidad').val() ));

				}else{
					$(this).focus();
					return false;
				}

			});

		    // Al modificar el precio de compra
            $(document).on('keyup','#cantidad',function(event){
				
				if( validar_input_numerico( $(this) ) )
				{	

					var x = event.which || event.keyCode;
					if( x==13 )
					{
						$('.btn_save_modal').focus();				
					}

					$('#precio_total').val( parseFloat( $('#precio_unitario').val() ) * parseFloat( $('#cantidad').val() ));

				}else{
					$(this).focus();
					return false;
				}

			});

            $('.btn_save_modal').click(function(){
            	if ( $.isNumeric( $('#precio_total').val() ) && $('#precio_total').val() > 0 )
            	{
            		$('#form_edit').submit();
            	}
            });

		});
	</script>
@endsection
