<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table.table-bordered, .table-bordered>tbody>tr>td{
            border: 1px solid #ddd;
        }

        .lbl_doc_anulado{
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

    <table class="table table-bordered">
        <tr>
            <td style="border: solid 1px #ddd; margin-top: -40px;">
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
            </td>
            <td style="border: solid 1px #ddd; padding-top: -20px;">

                <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                    <br/>
                    <b>Documento:</b> @yield('documento_transaccion_prefijo_consecutivo')
                    <br/>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}

                    @yield('documento_datos_adicionales')

                    @if($doc_encabezado->estado == 'Anulado')
                        <div class="lbl_doc_anulado">
                            Documento Anulado
                        </div>
                    @endif
            </td>
        </tr>
    </table>
    
    @if($doc_encabezado->estado == 'Anulado')
        <div class="lbl_doc_anulado">
            Documento Anulado
        </div>
    @endif

    @yield('encabezado_2')

    <div style="border: solid 1px #ddd;">
        <b>@yield('lbl_tercero')</b> {{ $doc_encabezado->tercero_nombre_completo }}
        <br>
        <b>NIT:</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
        <br>
        <b>Dirección:</b> {{ $doc_encabezado->direccion1 }}
        <br>
        <b>Teléfono:</b> {{ $doc_encabezado->telefono1 }}

        @yield('encabezado_datos_adicionales')
    </div>

    @yield('tabla_registros_1')

    @yield('tabla_registros_2')

    @yield('tabla_registros_3')

    <table style="width: 100%;">
        <tr>
            <td style="border: solid 1px black;"> <b> @yield('lbl_firma') </b> <br><br><br><br> </td>
        </tr>
        @yield('firma_fila_adicional')
    </table>

    <p style="text-align: right;">
        {{ $doc_encabezado->created_at }}    
    </p>
    
    <br><br><br>

</body>
</html>