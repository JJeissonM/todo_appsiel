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
<body onload="window.print()">
    <?php        
        $url_img = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen;

        $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    ?>
    <table border="0" style="margin-top: 12px !important;" width="100%">
        <tr>
            <td>
                <div style="text-align: center;">
                    <br/>
                    <b>{{ $empresa->descripcion }}</b><br/>
                </div>
            </td>
        </tr>
        <tr>
            <td style="font-size: 15px;">
                <div style="text-align: center;">
                    <b>{{ $doc_encabezado->documento_transaccion_descripcion }} 
                    <br>
                    No.</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    <br>
                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                </div>
            </td>
        </tr>
    </table>
    
    @if($doc_encabezado->estado == 'Anulado')
        <div class="lbl_doc_anulado">
            Documento Anulado
        </div>
    @endif

    <div style="border: solid 1px #ddd;">
        <b>Cliente</b> {{ $doc_encabezado->tercero_nombre_completo }} 
        &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
        <b>NIT:</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
        &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
        <b>Dirección:</b> {{ $doc_encabezado->direccion1 }}
        &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
        <b>Teléfono:</b> {{ $doc_encabezado->telefono1 }}
        <br>
        <b>Atendido por: &nbsp;&nbsp;</b> {{ $doc_encabezado->vendedor->tercero->descripcion }}
        <br>
        <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
    </div>

    <table style="width: 100%;">
        <thead>
            <tr>
                <th width="100px">Item</th>
                <th width="40px">Cant. pedida</th>
                <th width="30px">Cant. <br> despachada</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $cantidad_total_productos = 0;
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td> 
                        {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->unidad_medida1 }}
                    </td>
                    <td> &nbsp; <br> ____________ </td>
                </tr>
                <?php 
                    $cantidad_total_productos++;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <b> Cantidad de items&nbsp;: </b> {{ $cantidad_total_productos }}
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <b> Despachado por &nbsp;&nbsp;&nbsp;: </b> _____________________
                </td>
            </tr>
        </tfoot>
    </table>

    <p style="text-align: right;">
        Generado: {{ $doc_encabezado->created_at }}    
    </p>
    
    <br><br><br>

    <script type="text/javascript">
        window.onload = function() { window.print(); }
    </script>

</body>

</html>