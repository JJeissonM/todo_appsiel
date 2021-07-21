<!DOCTYPE html>
<html>

<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">        

        .lbl_doc_anulado {
            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 300px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

    </style>
</head>

<body>

    <table class="table">
        <tr>
            <td style="/*border: solid 1px #ddd;*/ border: none;" width="60%">
                <div class="headempresa">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </div>
            </td>
            <td style="/*border: solid 1px #ddd;*/">
                <div class="headdoc">
                    <br>
                    <b
                        style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b><br>

                    <table style="margin-top: 10px;">
                        <tr>
                            <td><b>Documento:</b></td>
                            <td>@yield('documento_transaccion_prefijo_consecutivo')</td>
                        </tr>
                        <tr>
                            <td><b>Fecha:</b></td>
                            <td>{{ $doc_encabezado->fecha }}</td>
                        </tr>
                        <tr>
                            @yield('documento_datos_adicionales')
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
    
        @yield('encabezado_2')
    
        <div>
            <b>@yield('lbl_tercero')</b> {{ $doc_encabezado->tercero->descripcion }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>{{ config("configuracion.tipo_identificador") }} / CC:</b> 
            @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->tercero->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->tercero->numero_identificacion}} @endif - {{ $doc_encabezado->tercero->digito_verificacion }}

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>Dirección:</b> {{ $doc_encabezado->tercero->direccion1 }},
            {{ $doc_encabezado->tercero->ciudad->descripcion }} -
            {{ $doc_encabezado->tercero->ciudad->departamento->descripcion }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>Teléfono:</b> {{ $doc_encabezado->tercero->telefono1 }}
    
            @yield('encabezado_datos_adicionales')
        </div>

    </div>
    <br>

    @yield('tabla_registros_1')

    @yield('tabla_registros_2')

    @yield('tabla_registros_3')

    @include('core.firmas')

    <table style="width: 100%;">
        <!-- <tr>
            <td style="border: solid 1px black;"> <b> @ yield('lbl_firma') </b> <br><br><br><br> </td>
        </tr>
        -->
        @yield('firma_fila_adicional')

    </table>

    <br><br><br>

</body>

</html>