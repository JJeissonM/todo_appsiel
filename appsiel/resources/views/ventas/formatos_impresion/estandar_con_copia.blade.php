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
        $total_cantidad = 0;
        $subtotal = 0;
        $total_descuentos = 0;
        $total_impuestos = 0;
        $total_factura = 0;
        $array_tasas = [];

        $cantidad_items = 0;

        $impuesto_iva = 0;//iva en firma

        foreach($doc_registros as $linea )
        {
            // Si la tasa no está en el array, se agregan sus valores por primera vez
            if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
            {
                // Clasificar el impuesto
                $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA='.$linea->tasa_impuesto.'%';
                if ( $linea->tasa_impuesto == 0)
                {
                    $array_tasas[$linea->tasa_impuesto]['tipo'] = 'EX=0%';
                }
                // Guardar la tasa en el array
                $array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


                // Guardar el primer valor del impuesto y base en el array
                $array_tasas[$linea->tasa_impuesto]['precio_total'] = (float)$linea->precio_total;
                $array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float)$linea->base_impuesto * (float)$linea->cantidad;
                $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float)$linea->valor_impuesto * (float)$linea->cantidad;

            }else{
                // Si ya está la tasa creada en el array
                // Acumular los siguientes valores del valor base y valor de impuesto según el tipo
                $precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
                $array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float)$linea->precio_total;
                $array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float)$linea->base_impuesto * (float)$linea->cantidad;
                $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float)$linea->valor_impuesto * (float)$linea->cantidad;
            }

            $total_cantidad += $linea->cantidad;
            $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
            $total_factura += $linea->precio_total;
            $total_descuentos += $linea->valor_total_descuento;

            $total_abonos = 0;
            foreach ($abonos as $linea_abono)
            {
                $total_abonos += $linea_abono->abono;
            }
            if($linea->valor_impuesto > 0){
                $impuesto_iva = $linea->tasa_impuesto;
            }

            $cantidad_items++;
        }

        $subtotal += $total_factura + $total_descuentos - $total_impuestos;

        $cant_item_minimo = 5;
        $cant_item_por_pagina_adicional = 2;
    ?>

    @if( $cantidad_items <= $cant_item_minimo )

        <!-- UNA SOLA PAGINA -->
        <?php  
            $factura = \View::make( 'ventas.formatos_impresion.estandar_con_copia.una_sola_pagina.factura', compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos', 'docs_relacionados', 'otroscampos', 'datos_factura', 'cliente', 'tipo_doc_app', 'pdv_descripcion', 'medios_pago','total_cantidad','total_factura', 'total_abonos', 'array_tasas', 'subtotal', 'total_descuentos', 'total_impuestos', 'impuesto_iva' ) )->render();
        ?>
        <div class="contenedor">
            {!! $factura !!}
        </div>

        <div class="contenedor">
            {!! $factura !!}
        </div>

    @endif

    <?php 
        $items_restantes = $cantidad_items - $cant_item_minimo;
        
        $cantidad_total_paginas = 2;

        $cantidad_items_primera_pagina = 6;

    ?>
    
    @if( $items_restantes > 0 && $items_restantes <= $cant_item_por_pagina_adicional )

        <!-- DOS PAGINAS -->
        <?php
            $encabezado_factura = \View::make( 'ventas.formatos_impresion.estandar_con_copia.encabezado_factura', compact('doc_encabezado', 'empresa', 'resolucion', 'etiquetas', 'cantidad_total_paginas' ) )->render();
            // , 'doc_registros', 'abonos', 'docs_relacionados', 'otroscampos', 'datos_factura', 'cliente', 'tipo_doc_app', 'pdv_descripcion', 'medios_pago','total_cantidad', 'total_abonos', 'array_tasas', 'subtotal', 'total_descuentos', 'total_impuestos', 'impuesto_iva'
            
            // doc_registros queda truncado
            $doc_registros_restantes = $doc_registros->splice( $cantidad_items_primera_pagina );

            $lineas_registros_primera_pagina = \View::make( 'ventas.formatos_impresion.estandar_con_copia.dos_paginas.lineas_registros_primera_pagina', compact( 'doc_registros' ) )->render();
            
            $lineas_registros_segunda_pagina = \View::make( 'ventas.formatos_impresion.estandar_con_copia.dos_paginas.lineas_registros_segunda_pagina', compact( 'doc_registros_restantes', 'total_abonos', 'cantidad_items', 'cantidad_total_paginas' ) )->render();
            
            $tabla_impuestos_totales_y_firma = \View::make( 'ventas.formatos_impresion.estandar_con_copia.tabla_impuestos_totales_y_firma', compact( 'otroscampos', 'resolucion', 'array_tasas', 'subtotal', 'total_descuentos', 'total_impuestos', 'impuesto_iva', 'total_factura' ) )->render();

                //dd( $lineas_registros_primera_pagina, $lineas_registros_segunda_pagina );


                // ,, 'abonos', 'docs_relacionados', 'datos_factura', 'cliente', 'tipo_doc_app', 'pdv_descripcion', 'medios_pago','total_cantidad',

        ?>
        <div class="contenedor">
            {!! $encabezado_factura !!}

            {!! $lineas_registros_primera_pagina !!}
        </div>

        <div class="contenedor">
            {!! $encabezado_factura !!}

            {!! $lineas_registros_primera_pagina !!}
        </div>

        <div class="page-break"></div>

        <!-- Segunda pagina -->
        <div class="contenedor">
            {!! $lineas_registros_segunda_pagina !!}
            
            {!! $tabla_impuestos_totales_y_firma !!}            
        </div>

        <div class="contenedor">
            {!! $lineas_registros_segunda_pagina !!}
            
            {!! $tabla_impuestos_totales_y_firma !!}  
        </div>

    @endif
    
</body>

</html>