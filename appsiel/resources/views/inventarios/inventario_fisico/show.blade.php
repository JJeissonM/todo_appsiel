<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'inv_fisico/create'.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion ) }}
    
    @if( !$inventario_fisico_tiene_ajuste )
        {{ Form::bsBtnEdit2(str_replace('id_fila', $id, 'inv_fisico/id_fila/edit'.$variables_url ),'Editar') }}

        <a class="btn-gmail" href="{{ url('inv_fisico_hacer_ajuste?id=8&id_modelo=25&id_transaccion=28&doc_inv_fisico_id='.$id) }}" target="_blank" title="Hacer Ajuste"><i class="fa fa-btn fa-cog"></i></a>
        <button type="button" class="btn-gmail" data-toggle="modal" data-target="#modal_unificar_lineas" title="Unificar items repetidos">
            <i class="fa fa-compress"></i>
        </button>
        <button type="button" class="btn-gmail" data-toggle="modal" data-target="#modal_ajustar_saldos_bodega" title="Agregrar ítems restantes">
            <i class="fa fa-balance-scale"></i>
        </button>
        @can('inventarios.inventario_fisico.descontar_ventas')
            <button type="button" class="btn-gmail" data-toggle="modal" data-target="#modal_descontar_ventas" title="Descontar Ventas">
                <i class="fa fa-cutlery"></i>
            </button>
        @endcan
    @endif
@endsection

@section('informacion_antes_encabezado')
    @if( $inventario_fisico_tiene_ajuste )
        <div class="alert alert-info">
            <strong>Inventario Fisico bloqueado.</strong>
            Ya tiene ajuste relacionado:
            <?php $cantidad_ajustes = count($ajustes_asociados); $i_ajuste = 1; ?>
            @foreach( $ajustes_asociados as $relacion )
                @if( $relacion->documento_relacionado != null )
                    <a href="{{ url('inventarios/'.$relacion->documento_relacionado->id.'?id=8&id_modelo=25&id_transaccion=28') }}" target="_blank">
                        @if( $relacion->documento_relacionado->tipo_documento_app != null )
                            {{ $relacion->documento_relacionado->tipo_documento_app->prefijo }}
                        @endif
                        {{ $relacion->documento_relacionado->consecutivo }}
                    </a>@if( $i_ajuste < $cantidad_ajustes ), @endif
                @endif
                <?php $i_ajuste++; ?>
            @endforeach
        </div>
    @endif
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

    @can('inventarios.inventario_fisico.descontar_ventas')
        <div class="modal fade" id="modal_descontar_ventas" tabindex="-1" role="dialog" aria-labelledby="modal_descontar_ventas_label" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal_descontar_ventas_label">Descontar Ventas</h4>
                    </div>
                    <div class="modal-body">
                        Este proceso calcula los ingredientes consumidos por ventas del dia <b>{{ date_format(date_create($doc_encabezado->fecha), "d-m-Y") }}</b> en la bodega <b>{{ $doc_encabezado->bodega_descripcion }}</b> y genera un ajuste de inventarios con entrada a bodega principal y salida de esa bodega.
                        <br><br>
                        ¿Deseas continuar?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <form method="POST" action="{{ url('inv_fisico_descontar_ventas/'.$id.$variables_url) }}" style="display:inline;" id="form_descontar_ventas">
                            {{ csrf_field() }}
                            <input type="hidden" name="url_id" value="{{ Input::get('id') }}">
                            <input type="hidden" name="url_id_modelo" value="{{ Input::get('id_modelo') }}">
                            <input type="hidden" name="url_id_transaccion" value="{{ $id_transaccion }}">
                            <button type="submit" class="btn btn-danger" id="btn_descontar_ventas">
                                <i class="fa fa-spinner fa-spin" id="spinner_descontar_ventas" style="display:none;"></i>
                                <span id="texto_descontar_ventas">Ejecutar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
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

        $(document).on('submit', '#form_descontar_ventas', function() {
            $('#spinner_descontar_ventas').show();
            $('#texto_descontar_ventas').text('Procesando...');
            $('#btn_descontar_ventas').prop('disabled', true);
        });
    </script>
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id', ['estandar' => 'Estándar', 'balance_inventarios' => 'Balance de inventarios'], 'estandar', [ 'id' =>'formato_impresion_id' ] ) }}
	{{ Form::bsBtnPrint( 'inv_fisico_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
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
            @if( $inventario_fisico_tiene_ajuste )
                <br/>
                <b>Doc. relacionado: &nbsp;&nbsp;</b>
                <?php $cantidad_ajustes = count($ajustes_asociados); $i_ajuste = 1; ?>
                @foreach( $ajustes_asociados as $relacion )
                    @if( $relacion->documento_relacionado != null )
                        <a href="{{ url('inventarios/'.$relacion->documento_relacionado->id.'?id=8&id_modelo=25&id_transaccion=28') }}" target="_blank">
                            @if( $relacion->documento_relacionado->tipo_documento_app != null )
                                {{ $relacion->documento_relacionado->tipo_documento_app->prefijo }}
                            @endif
                            {{ $relacion->documento_relacionado->consecutivo }}
                        </a>@if( $i_ajuste < $cantidad_ajustes ), @endif
                    @endif
                    <?php $i_ajuste++; ?>
                @endforeach
            @endif
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
