<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>

    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }
        @page {
          size: 3.15in 38.5in;
          margin: 15px;
        }

        .page-break {
            page-break-after: always;
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
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    ?>
    <table border="0" style="margin-top: 12px !important;" width="100%">
        <tr>
            <td width="15%">
                <img src="{{ $url_img }}" width="120px;" />
            </td>
            <td>
                <div style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                    <b>{{ $empresa->nombre1 }} {{ $empresa->apellido1 }} {{ $empresa->apellido2 }}</b><br/>
                    <b>NIT. {{ number_format($empresa->numero_identificacion, 0, ',', '.') }} - {{ $empresa->digito_verificacion }}</b><br/>
                    {{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
                    Teléfono(s): {{ $empresa->telefono1 }}<br/>
                    <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
                </div>
            </td>
        </tr>
    </table>

    @yield('encabezado_2')

    <table border="0" style="margin-top: 12px !important;" width="100%">
            <tr>
                <td>
                    <b>{{ $doc_encabezado->documento_transaccion_descripcion }} No.</b> @yield('documento_transaccion_prefijo_consecutivo')               
                </td>
                <td>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                </td>
            </tr>

            @yield('fila_datos_adicionales')

    </table>
    
    @if($doc_encabezado->estado == 'Anulado')
        <div class="lbl_doc_anulado">
            Documento Anulado
        </div>
    @endif

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