<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'inv_fisico/create'.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion ) }}
    
    {{ Form::bsBtnEdit2(str_replace('id_fila', $id, 'inv_fisico/id_fila/edit'.$variables_url ),'Editar') }}

	<a class="btn-gmail" href="{{ url('inv_fisico_hacer_ajuste?id=8&id_modelo=25&id_transaccion=28&doc_inv_fisico_id='.$id) }}" target="_blank" title="Hacer Ajuste"><i class="fa fa-btn fa-cog"></i></a>
    <button type="button" class="btn-gmail" data-toggle="modal" data-target="#modal_unificar_lineas" title="Unificar items repetidos">
        <i class="fa fa-compress"></i>
    </button>
    <button type="button" class="btn-gmail" data-toggle="modal" data-target="#modal_ajustar_saldos_bodega" title="Agregrar ítems restantes">
        <i class="fa fa-balance-scale"></i>
    </button>
@endsection

@section('section_after_documento_vista')

<div class="container-fluid">
    <div class="modal fade" id="modal_unificar_lineas" tabindex="-1" role="dialog" aria-labelledby="modal_unificar_lineas_label" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_unificar_lineas_label">Unificar items repetidos</h4>
                </div>
                <div class="modal-body">
                    Esta accion sumara las cantidades y costos de items repetidos y dejara una sola linea por producto.
                    Deseas continuar?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ url('inv_fisico_unificar_registros/'.$id.$variables_url) }}" style="display:inline;" id="form_unificar_lineas">
                        {{ csrf_field() }}
                        <input type="hidden" name="url_id" value="{{ Input::get('id') }}">
                        <input type="hidden" name="url_id_modelo" value="{{ Input::get('id_modelo') }}">
                        <input type="hidden" name="url_id_transaccion" value="{{ $id_transaccion }}">
                        <button type="submit" class="btn btn-danger" id="btn_unificar_lineas">
                            <i class="fa fa-spinner fa-spin" id="spinner_unificar_lineas" style="display:none;"></i>
                            Unificar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_ajustar_saldos_bodega" tabindex="-1" role="dialog" aria-labelledby="modal_ajustar_saldos_bodega_label" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal_ajustar_saldos_bodega_label">Procesos agregrar ítems restantes - Para ajustar Saldos en Bodega</h4>
                </div>
                <div class="modal-body">
                    Este proceso agrega una linea por cada Producto Activo que haya tenido inventario, pero que no exista actualmente en los registros del documento.
                    ¿Deseas continuar?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ url('inv_fisico_ajustar_saldos_bodega/'.$id.$variables_url) }}" style="display:inline;" id="form_ajustar_saldos_bodega">
                        {{ csrf_field() }}
                        <input type="hidden" name="url_id" value="{{ Input::get('id') }}">
                        <input type="hidden" name="url_id_modelo" value="{{ Input::get('id_modelo') }}">
                        <input type="hidden" name="url_id_transaccion" value="{{ $id_transaccion }}">
                        <button type="submit" class="btn btn-danger" id="btn_ajustar_saldos_bodega">
                            <i class="fa fa-spinner fa-spin" id="spinner_ajustar_saldos_bodega" style="display:none;"></i>
                            Ejecutar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('otros_scripts')
    <script type="text/javascript">
        $(document).on('submit', '#form_unificar_lineas', function() {
            $('#spinner_unificar_lineas').show();
            $('#btn_unificar_lineas').prop('disabled', true);
        });

        $(document).on('submit', '#form_ajustar_saldos_bodega', function() {
            $('#spinner_ajustar_saldos_bodega').show();
            $('#btn_ajustar_saldos_bodega').prop('disabled', true);
        });
    </script>
@endsection

@section('botones_imprimir_email')
	{{ Form::bsBtnPrint( 'inv_fisico_imprimir/'.$id.$variables_url ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'inv_fisico/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
    &nbsp;
@endsection

@section('filas_adicionales_encabezado')
    <tr> 
        <td style="border: solid 1px #ddd;">
            <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
            <br/>
            <b>Bodega: &nbsp;&nbsp;</b> {{ $doc_encabezado->bodega_descripcion }}
            <br/>
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
        <td style="border: solid 1px #ddd;">
            <b>Hora inicio:</b> {{ $doc_encabezado->hora_inicio ? date('g:i a', strtotime($doc_encabezado->hora_inicio)) : '' }}
            <br/>
            <b>Hora finalizacion:</b> {{ $doc_encabezado->hora_finalizacion ? date('g:i a', strtotime($doc_encabezado->hora_finalizacion)) : '' }}
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>ADVERTENCIA</strong>
		La anulacion no puede revertirse. Si quieres confirmar, hacer click en: <a class="btn btn-danger btn-sm" href="{{ url('inv_fisico_anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
	</div>
@endsection
