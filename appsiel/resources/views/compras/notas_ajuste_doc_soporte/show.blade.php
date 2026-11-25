<?php
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
	$nota_enviada = $doc_encabezado->enviado_electronicamente();
?>

@extends('transaccion.show')

@section('botones_acciones')
	@if($doc_encabezado->estado != 'Anulado')
		@if(!$nota_enviada)
			<button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
		@endif
		@if(View::exists('compras.notas_ajuste_doc_soporte.acciones_electronicas'))
			@include('compras.notas_ajuste_doc_soporte.acciones_electronicas')
		@endif
	@endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'compras_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'compras/', $variables_url.'&vista=compras.notas_ajuste_doc_soporte.show' ) !!}
@endsection

@section('datos_adicionales_encabezado')
	<?php
		$ds_relacionado = App\Compras\ComprasDocEncabezado::with(['tipo_transaccion', 'tipo_documento_app'])
			->where('core_empresa_id', $doc_encabezado->core_empresa_id)
			->find($doc_encabezado->compras_doc_relacionado_id);
	?>
	<br/>
	<b>Documento soporte relacionado: </b>
	@if($ds_relacionado && $ds_relacionado->tipo_transaccion && $ds_relacionado->tipo_documento_app)
		<a href="{{ url('compras/'.$ds_relacionado->id.'?id='.$ds_relacionado->tipo_transaccion->core_app_id.'&id_modelo='.$ds_relacionado->tipo_transaccion->core_modelo_id.'&id_transaccion='.$ds_relacionado->core_tipo_transaccion_id) }}">
			{{ $ds_relacionado->tipo_documento_app->prefijo }} {{ $ds_relacionado->consecutivo }}
		</a>
	@else
		Sin documento soporte relacionado
	@endif
	<br/>
	<b>Concepto: </b> {{ $doc_encabezado->nota_ajuste_concepto }} - {{ isset(App\FacturacionElectronica\NotaAjusteDocSoporte::CONCEPTOS[$doc_encabezado->nota_ajuste_concepto]) ? App\FacturacionElectronica\NotaAjusteDocSoporte::CONCEPTOS[$doc_encabezado->nota_ajuste_concepto] : '' }}
@endsection

@section('filas_adicionales_encabezado')
	<tr>
		<td style="border: solid 1px #ddd;">
			<b>Proveedor:</b> {{ $doc_encabezado->tercero_nombre_completo }}
			<br/>
			<b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }} @else {{ $doc_encabezado->numero_identificacion}} @endif
			<br/>
			<b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
			<br/>
			<b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
		</td>
		<td style="border: solid 1px #ddd;">
			<b>Forma de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->forma_pago) }}
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
		Al anular la nota se revierten la devolución de inventario, las retenciones, la Cuenta por Pagar y el movimiento contable relacionado.
		<br>
		Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url( 'compras_notas_ajuste_doc_soporte_anular/'.$id.$variables_url ) }}" id="enlace_anular"> Anular </a> </small>
	</div>
@endsection

@section('documento_vista')
	@include('compras.incluir.documento_vista')
@endsection

@section('registros_otros_documentos')
	@include('compras.incluir.registros_abonos')
@endsection
