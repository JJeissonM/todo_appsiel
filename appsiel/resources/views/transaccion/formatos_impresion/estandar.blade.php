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
            <td style="border: solid 1px #ddd; margin-top: -40px;" width="70%">
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
            </td>
            <td style="border: solid 1px #ddd;">

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

    <?php
        $elaboro = $doc_encabezado->creado_por;
    ?>

    @yield('encabezado_2')

    <div style="border: solid 1px #ddd;">
        <b>@yield('lbl_tercero')</b> {{ $doc_encabezado->tercero->descripcion }}
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>NIT / CC:</b> {{ number_format( $doc_encabezado->tercero->numero_identificacion, 0, ',', '.') }}
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Dirección:</b> {{ $doc_encabezado->tercero->direccion1 }}, {{ $doc_encabezado->tercero->ciudad->descripcion }} - {{ $doc_encabezado->tercero->ciudad->departamento->descripcion }}
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Teléfono:</b> {{ $doc_encabezado->tercero->telefono1 }}

        @yield('encabezado_datos_adicionales')
    </div>

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