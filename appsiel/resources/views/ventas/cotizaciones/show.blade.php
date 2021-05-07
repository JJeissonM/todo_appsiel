<?php
	$variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;	
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'vtas_cotizacion/create'.$variables_url ) }}
	@if( $doc_encabezado->estado != 'Anulado' && is_null( $doc_encabezado->documento_ventas_hijo() ) )
		{{ Form::bsBtnEdit2(str_replace('id_fila', $id, 'vtas_cotizacion/id_fila/edit'.$variables_url ),'Editar') }}
		<button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-btn fa-close"></i></button>
	@endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['1'=>'Estándar','2'=>'Estándar v2'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'vtas_cotizacion_imprimir/'.$id.$variables_url.'&formato_impresion_id=1' ) }}
	{{ Form::bsBtnEmail( 'vtas_cotizacion_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=1' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'vtas_cotizacion/', $variables_url ) !!}
@endsection

@section('cabecera')
	@if( is_null( $doc_encabezado->documento_ventas_hijo() ) && $doc_encabezado->estado == 'Pendiente' )
		<div class="col-md-12">
			<form class="form-control" method="post" action="{{route('ventas.conexion_procesos')}}">
				<input type="hidden" name="url" value="vtas_cotizacion/{{$doc_encabezado->id.$variables_url}}" />
				<input type="hidden" name="modelo" value="{{$doc_encabezado->id}}" />
				<input type="hidden" name="source" value="COTIZACION" />
				{{ csrf_field() }}
				<label class="control-label">Genere de forma automática su pedido <i class="fa fa-arrow-down" aria-hidden="true"></i></label>
				<div class="row">
					<div class="col-md-6 col-lg-6 col-xl-2">
							{{ Form::bsFecha('fecha_entrega',date('Y-m-d'),'Fecha de Entrega', null,[]) }}
					</div>
					<div class="col-md-2">
						{{ Form::select('generar',['1'=>'Pedido'],null, ['class'=>'form-control select2','required'=>'required', 'id' =>'generar']) }}
					</div>
					<div class="col-md-1 col-lg-2">
						<button type="submit" class="btn btn-primary btn-block">GENERAR</button>
					</div>
				</div>
				
			</form>
		</div>
	@endif
@endsection

@section('datos_adicionales_encabezado')
	<br>
	<b>Fecha vencimiento:</b> {{ $doc_encabezado->fecha_vencimiento }}
	<br>
	<b>Condición de ventas:</b> {{ $doc_encabezado->texto_condicion_venta() }}
	
	@if( !is_null( $doc_encabezado->documento_ventas_hijo() ) )
		<br>
		<b>{{ $doc_encabezado->documento_ventas_hijo()->tipo_transaccion->descripcion }}: &nbsp;&nbsp;</b> {!! $doc_encabezado->documento_ventas_hijo()->enlace_show_documento() !!}
	@endif

	@if( $docs_relacionados[0] != '' )
		<br/>
		<b>Remisión: </b> {!! $docs_relacionados[0] !!}
	@endif

@endsection

@section('filas_adicionales_encabezado')
	<tr>
		<td style="border: solid 1px #ddd;">
			<b>Cliente: </b> {{ $doc_encabezado->tercero_nombre_completo }}
			<br>
			<b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			
			@if( config("configuracion.tipo_identificador") == 'NIT') 
				{{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	
			@else 
				{{ $doc_encabezado->numero_identificacion}} 
			@endif
		</td>
		<td style="border: solid 1px #ddd;">
			@if( !is_null($doc_encabezado->contacto_cliente) )
				<b>Contacto: </b> {{ $doc_encabezado->contacto_cliente->tercero->descripcion }}
				<br>
				<b>Tel: </b> {{ $doc_encabezado->contacto_cliente->tercero->telefono1 }}
				<br>
				<b>Email: </b> {{ $doc_encabezado->contacto_cliente->tercero->email }}
			@endif
		</td>
	</tr>
@endsection

@section('div_advertencia_anulacion')
<div class="alert alert-warning" style="display: none;">
	<a href="#" id="close" class="close">&times;</a>
	<strong>Advertencia!</strong>
	<br>
	La anulación no se puede revertir.
	<br>
	Si realmente quiere anular el documento, haga click en el siguiente enlace: <span style="text-decoration-line: underline"> <a href="{{ url( 'vtas_cotizacion_anular/'.$id.$variables_url ) }}"> Anular </a> </span>
</div>
@endsection

@section('otros_scripts')
<script type="text/javascript">
	$(document).ready(function() {
		//
	});
</script>
@endsection