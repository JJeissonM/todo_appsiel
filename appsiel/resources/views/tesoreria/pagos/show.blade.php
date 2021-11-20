<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;

    $caja = null;
    $cuenta_bancaria = null;

    $medio_recaudo = $encabezado_documento->medio_recaudo;

    if ( !is_null($medio_recaudo) )
    {
        switch ( $medio_recaudo->comportamiento )
        {
            case 'Efectivo':
                $caja = $encabezado_documento->caja;
                $cuenta_bancaria = null;
                break;

            case 'Tarjeta bancaria':
                $cuenta_bancaria = $encabezado_documento->cuenta_bancaria;
                $caja = null;
                break;
            
            default:
                break;
        }
    }
        
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'tesoreria/pagos/create'.$variables_url ) }}
	@if($doc_encabezado->estado != 'Anulado')

        {{ Form::bsBtnEdit2( 'tesoreria/pagos/'.$id.'/edit'.$variables_url,'Editar') }}
        
        <a class="btn-gmail" id="btn_duplicar" href="{{ url( 'teso_pagos_duplicar_documento/'.$id.$variables_url ) }}" title="Duplicar"><i class="fa fa-btn fa-clone"></i></a>

        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>

        <!-- <a class="btn-gmail" href="{ { url( 'teso_recontabilizar_documento_pago/'.$id.$variables_url ) }}" title="Recontabilizar"><i class="fa fa-cog"></i></a> -->
    @endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'tesoreria/pagos_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'tesoreria/pagos/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
        </td>
    </tr>
    <tr>        
        <td colspan="2" style="border: solid 1px #ddd;">
            @if( !is_null( $caja ) )
                <b>Caja: &nbsp;&nbsp;</b> {{ $caja->descripcion }}
                <br>
            @endif
            @if( !is_null( $cuenta_bancaria ) )
                <b>Cuenta bancaria: &nbsp;&nbsp;</b> Cuenta {{ $cuenta_bancaria->tipo_cuenta }} {{ $cuenta_bancaria->entidad_financiera->descripcion }} No. {{ $cuenta_bancaria->descripcion }}
                <br>
            @endif
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>¡ADVERTENCIA!</strong>
		Al anular el documento se eliminan los registros del movimiento contable relacionado. La anulación no puede revertirse. Si quieres confirmar, hacer click en: <a class="btn btn-danger btn-sm" href="{{ url('teso_anular_pago/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
	</div>
@endsection

@section('documento_vista')
    @include('tesoreria.pagos.documento_vista')
@endsection
