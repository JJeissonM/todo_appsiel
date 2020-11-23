<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
    $color = 'black';
?>

@extends('transaccion.show')

@section('informacion_antes_encabezado')
	<div style="width: 100%; text-align: center;">
		<code>Nota: La visualización de este documento es diferente al documento enviado al cliente por el proveedor tecnológico.</code>	
	</div>
	<br>
@endsection

@section('botones_acciones')
	@if($doc_encabezado->estado != 'Anulado')
        <button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-close"></i> Anular </button>
    @endif

    @if( $doc_encabezado->estado == 'Sin enviar' )
		<?php 
			$color = 'red';
		?>
		<a class="btn btn-warning btn-xs btn-detail" href="{{ url( 'fe_nota_debito_enviar/' . $doc_encabezado->id . $variables_url ) }}" title="Enviar por correo" id="btn_email"><i class="fa fa-btn fa-envelope"></i> Enviar </a>
	@endif

@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'ventas_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'fe_nota_debito/', $variables_url.'&vista=ventas.notas_debito.show' ) !!}
@endsection

@section('datos_adicionales_encabezado')
	<br/>
	<span style="color:{{$color}}"><b>Estado: </b> {{ $doc_encabezado->estado }}</span>
	<br/>
	<b>Devolución: </b> {!! $docs_relacionados[0] !!}
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Cliente:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
        </td>
        <td style="border: solid 1px #ddd;">
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
			$url = 'ventas_notas_debito_anular'; // NC con base en factura
			if( $id_transaccion == 41 )
			{
				$url = 'ventas_notas_debito_directa_anular';
			}
		?>
		Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url( $url.'/'.$id.$variables_url ) }}" id="enlace_anular"> Anular </a> </small>
	</div>
@endsection

@section('documento_vista')
	@include('ventas.incluir.documento_vista')
@endsection

@section('registros_otros_documentos')
	@include('ventas.incluir.registros_abonos')
@endsection

@section('otros_scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$(".btn_editar_registro").click(function(event){
		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = '../vtas_get_formulario_edit_registro';

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