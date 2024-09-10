<!DOCTYPE html>
<html>

<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">

        @page {
            margin: 0.5cm;
        }
    
        html, body{
            height: 100%;
        }

        .contenedor{
            height: 48%;
        }

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

    <?php 
        $factura = \View::make( 'ventas.formatos_impresion.estandar_con_copia.una_factura', compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos', 'docs_relacionados', 'otroscampos', 'datos_factura', 'cliente', 'tipo_doc_app', 'pdv_descripcion', 'medios_pago' ) )->render();
    ?>
    
    <div class="contenedor">
        {!! $factura !!}
    </div>

    <div class="contenedor">
        {!! $factura !!}
    </div>
</body>

</html>