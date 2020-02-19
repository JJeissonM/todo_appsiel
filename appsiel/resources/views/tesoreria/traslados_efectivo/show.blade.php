@extends('layouts.show')
@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    <div class="row">
        <div class="col-md-4">
            <div class="btn-group">
                @if( isset($url_crear) )
                    @if($url_crear!='')
                        {{ Form::bsBtnCreate($url_crear) }}
                    @endif
                @endif

                @if( isset($url_edit) )
                    @if($url_edit!='')
                        {{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
                    @endif
                @endif
                @if(isset($botones))
                    @php
                        $i=0;
                    @endphp
                    @foreach($botones as $boton)
                        {!! str_replace( 'id_fila', $registro->id, $boton->dibujar() ) !!}
                        @php
                            $i++;
                        @endphp
                    @endforeach
                @endif
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="btn-group">
                Imprimir
                {{ Form::bsBtnPrint( 'tesoreria/imprimir/'.$registro->id ) }}
                {{--                Imprimir <a class="btn btn-info btn-xs btn-detail" href="http://localhost/Appsiel/todo_appsiel/tesoreria_pagos_cxp_imprimir/1?id=3&amp;id_modelo=150&amp;id_transaccion=33&amp;formato_impresion_id=estandar" title="Imprimir" id="btn_print" target="_blank" style="transform: rotate(0deg); border-spacing: 0px;"><i class="fa fa-btn fa-print"></i>&nbsp;</a>--}}
            </div>
        </div>
        <div class="col-md-4">
            <div class="btn-group pull-right">
                @if($reg_anterior!='')
                    {{ Form::bsBtnPrev('tesoreria/arqueo_caja/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif

                @if($reg_siguiente!='')
                    {{ Form::bsBtnNext('tesoreria/arqueo_caja/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
                @endif
            </div>
        </div>
    </div>
    <hr>
    @include('layouts.mensajes')
    <div class="col-md-12">
        <div class="container-fluid">
            <div class="marco_formulario">
                <div class="container">
                    <table class="table table-bordered" style="margin-top: 20px;">
                        <tr>
                            <td width="50%" style="border: solid 1px #ddd; margin-top: -40px;">
                                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
                            </td>
                            <td style="border: solid 1px #ddd; padding-top: -20px;">
                                <div style="vertical-align: center;">
                                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado['titulo'] }}</b>
                                    <br/>
                                    <b>Documento:</b> {{ $doc_encabezado['documento'] }}
                                    <br/>
                                    <b>Fecha:</b> {{ $doc_encabezado['fecha'] }}

                                    @yield('datos_adicionales_encabezado')

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: solid 1px #ddd;">
                                <b>Tercero:</b> {{ $doc_encabezado['tercero_nombre_completo'] }}
                                <br/>
                                <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado['numero_identificacion'], 0, ',', '.') }}
                                <br/>
                                <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado['direccion'] }}
                                <br/>
                                <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado['telefono'] }}
                                <br/>
                                <b>Fecha y Hora de Realización: &nbsp;&nbsp;</b> {{$registro->created_at}}
                                <br/>
                            </td>
                            <td style="border: solid 1px #ddd;">

                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: solid 1px #ddd;">
                                <b>Observaciones:</b> {{$registro->descripcion}}
                            </td>
                        </tr>
                    </table>
                    <hr>
                    @section('documento_vista')
                        @include('tesoreria.recaudos.documento_vista')
                    @endsection
                </div>
            </div>
        </div>
    </div>
@endsection