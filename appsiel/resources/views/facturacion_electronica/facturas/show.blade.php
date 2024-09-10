<?php  
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');

	$color = 'black';

	$tipo_operacion = 'factura';
?>

@extends('transaccion.show')

@section('informacion_antes_encabezado')
	<div style="width: 100%; text-align: center;">
		<code>Nota: La visualización de este documento es diferente al documento enviado al cliente por el proveedor tecnológico.</code>	
	</div>
	<br>
@endsection

@section('botones_acciones')
	@if( $doc_encabezado->estado != 'Anulado' && $doc_encabezado->condicion_pago != 'contado'  )
	    <a href="{{ url('tesoreria/recaudos_cxc/create?id='.Input::get('id').'&id_modelo=153&id_transaccion=32') }}" target="_blank" class="btn-gmail" title="Hacer abono"><i class="fa fa-btn fa-money"></i></a>
	@endif

	@if( !$docs_relacionados[1] && $doc_encabezado->estado == 'Enviada' )
    	<a class="btn-gmail" href="{{ url( 'fe_nota_credito/create?factura_id='.$doc_encabezado->id . '&id='.Input::get('id').'&id_modelo=245&id_transaccion=53') }}" title="Nota crédito"><i class="fa fa-file"></i></a>

    	<a class="btn-gmail" href="{{ url( 'fe_nota_debito/create?factura_id='.$doc_encabezado->id . '&id='.Input::get('id').'&id_modelo=246&id_transaccion=54') }}" title="Nota Débito"><i class="fa fa-file-o"></i></a>

    	<a class="btn-gmail" href="{{ url( 'fe_consultar_documentos_emitidos/' . $doc_encabezado->id . '/' . $tipo_operacion . $variables_url ) }}" title="Representación gráfica (PDF)" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
	@endif

		<!-- MOSTRAR SOLO SI YA ESTA ENVIADO -->

	@if( $doc_encabezado->estado == 'Sin enviar' || $doc_encabezado->estado == 'Contabilizado - Sin enviar' )

		<button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-btn fa-close"></i></button>
	
		<?php 
			$color = 'red';
		?>
		<a class="btn-gmail" href="{{ url( 'fe_factura_enviar/' . $doc_encabezado->id . $variables_url ) }}" title="Enviar" id="btn_enviar_fe"><i class="fa fa-btn fa-send"></i></a>
        <i class="fa fa-circle" style="color: orange;"> Sin enviar </i>

		@if ( $doc_encabezado->fecha != date('d-m-Y') )
			<br>
			<span style="color: red;"><i class="fa fa-warning"></i> Nota: La fecha de la factura es diferente a la fecha actual. La factura no será aceptada por la DIAN.</span>
			<br>
			<button class="btn btn-xs btn-primary" id="btn_actualizar_fecha_y_enviar" data-href="{{url('/') . '/fe_actualizar_fecha_y_enviar/' . $doc_encabezado->id }}"> <i class="fa fa-calendar"></i> Actualizar fecha y enviar</button>

			
			@include('components.design.ventana_modal2',['titulo2'=>'','texto_mensaje2'=>''])
		@endif
		

	@endif

@endsection

@section('botones_imprimir_email')
	@include('ventas.incluir.botones_imprimir_email')
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'fe_factura/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
	<br/>
	<b>Remisión: </b> {!! $docs_relacionados[0] !!}
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Cliente:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $doc_encabezado->tercero->digito_verificacion }}
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->tercero->direccion1 }}, {{ $doc_encabezado->tercero->ciudad->descripcion }} - {{ $doc_encabezado->tercero->ciudad->departamento->descripcion }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->tercero->telefono1 }}
            <br/>
            <b>Email: &nbsp;&nbsp;</b> {{ $doc_encabezado->tercero->email }}
        </td>
        <td style="border: solid 1px #ddd;">
			<?php 
				$lbl_vendedor = '';
				if ($doc_encabezado->vendedor != null) {
					$lbl_vendedor = $doc_encabezado->vendedor->tercero->descripcion;
				}
			?>
            <b>Vendedor: &nbsp;&nbsp;</b> {{ $lbl_vendedor }}
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
	{{ Form::open(['url'=>'ventas_anular_factura', 'id'=>'form_anular']) }}
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
	@if( $doc_encabezado->forma_pago == 'contado')
		{!! $medios_pago !!}	
	@endif
    
	@include('ventas.incluir.documento_vista')
@endsection

@section('registros_otros_documentos')
	@include('ventas.incluir.registros_abonos')
	@include('ventas.incluir.notas_credito')
@endsection

@section('otros_scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#enlace_anular').click(function(){
				
				if ( !$("#opcion1").is(":checked") && !$("#opcion2").is(":checked") )
				{
					alert('Debe escoger una opción.');
					$("#opcion1").focus();
					return false;
				}

				$('#form_anular').submit();

			});

			$('#btn_enviar_fe').click(function(){
				
				$(this).children('.fa-send').attr('class','fa fa-spinner fa-spin');
				$(this).css('pointer-events','none');

			});

            $("#myModal").on('hide.bs.modal', function(){
                $('#popup_alerta_danger').hide();
            });
			
			$("#btn_actualizar_fecha_y_enviar").click(function (event) {
				event.preventDefault();
				
				$('#contenido_modal2').html('<h3> <i class="fa fa-warning"></i> Nota: este proceso solo cambiará la fecha del encabezado de la factura para legalizarla ante la DIAN con la fecha de HOY. <br> <small style="color: red;">No se afectará la fecha para los Movimientos contables, de Remisiones, de Cajas ó Bancos ni de Cuentas por Cobrar (CxC). </small></h3> ');

				$("#myModal2").modal(
					{keyboard: true}
				);

				$("#myModal2 .modal-title").text('Actualizar fecha y enviar Factura Electrónica');

				$("#myModal2 .btn_edit_modal").hide();
				$("#myModal2 .btn_save_modal").html('<i class="fa fa-send"></i> Actualizar fecha y enviar');
				
				$("#myModal2 .btn_save_modal").attr( 'data-href', $(this).attr( 'data-href') );

			});
			
            $('.btn_save_modal').click(function(event){
				event.preventDefault();
				// Desactivar el click del botón				
				$(this).children('.fa-send').attr('class','fa fa-spinner fa-spin');
		        $(this).attr( 'disabled', 'disabled' );
				$( this ).off( event );
				location.href = $(this).attr( 'data-href' );
			});

		});
	</script>
@endsection