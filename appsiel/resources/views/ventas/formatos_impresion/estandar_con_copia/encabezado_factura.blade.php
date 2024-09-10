<div style="width: 100%; text-align:right;">
    Pág. 1/{{$cantidad_total_paginas}}
</div>
<table class="table">
    <tr>
        <td style="border: none;" width="60%">
            <div class="headempresa">
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
            </div>
        </td>
        <td style="/*border: solid 1px #ddd;*/">
            <div class="headdoc">
                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>

                <table style="margin-top: 10px;">
                    <tr>
                        <td><b>Documento:</b></td>
                        <td>
                            @if( !is_null( $resolucion ) )
                                {{ $resolucion->prefijo }} {{ $doc_encabezado->documento_transaccion_consecutivo }}
                            @else
                                {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><b>Fecha:</b></td>
                        <td>{{ $doc_encabezado->fecha }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            @include('ventas.incluir.metodo_y_condicion_pago')
                        </td>
                    </tr>
                </table>
            </div>                
        </td>
    </tr>
</table>

@if($doc_encabezado->estado == 'Anulado')
    <div class="lbl_doc_anulado">
        Documento Anulado
    </div>
@endif

<div class="subhead">

    @if($doc_encabezado->estado == 'Anulado')
    <div class="lbl_doc_anulado">
        Documento Anulado
    </div>
    @endif

    <?php
        $elaboro = $doc_encabezado->creado_por;
    ?>

    @if( $etiquetas['encabezado'] != '')
        <table style="width: 100%;">
            <tr>
                <td style="border: solid 1px #ddd; text-align: center; font-family: Courier New; font-style: italic;">
                    <b> {!! $etiquetas['encabezado'] !!} </b>
        
                </td>
            </tr>
        </table>
    @endif

    <div style="width: 100%;">
        <table style="width: 100%;">
            <tr>
                <td colspan="2"><b>Cliente:</b> {{ $doc_encabezado->tercero->descripcion }}</td>
                <td rowspan="2">
                    <b>Dirección:</b> {{ $doc_encabezado->tercero->direccion1 }},
                    {{ $doc_encabezado->tercero->ciudad->descripcion }} -
                    {{ $doc_encabezado->tercero->ciudad->departamento->descripcion }}
                </td>
            </tr>
            <tr>
                <td>
                    <b>{{ config("configuracion.tipo_identificador") }} / CC:</b> 
                        @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->tercero->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->tercero->numero_identificacion}} @endif - {{ $doc_encabezado->tercero->digito_verificacion }}
                    </td>
                <td><b>Teléfono:</b> {{ $doc_encabezado->tercero->telefono1 }}</td>
            </tr>
            <tr>
                <td colspan="3">
                    @include('matriculas.facturas.datos_estudiante')
                    @include('ventas.formatos_impresion.detalles_factura_medica')
                    <b>Detalle: &nbsp;&nbsp;</b> {!! $doc_encabezado->descripcion !!}
                </td>
        </table>    
    </div>
</div>
