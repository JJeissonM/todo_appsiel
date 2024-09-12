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

        .tabla_con_bordes > tr > td {
            border: 1px solid black;
        }
        
        .contenedor{
            height: 48%;
            border-bottom: 1px #ddd dashed;
            /*background-color: aquamarine;*/
            clear: both;
        }

        .lbl_doc_anulado {
            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 300px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

        .generado_por{
            /*background-color: blueviolet;*/
            position: absolute;
            bottom: 27px;
            left: 0;
        }

        .contenedor_col {
            text-align: right;
            width: 100%;
            margin: auto;

            /* Para limpiar los floats */
            content: "";
            display: table;
            clear: both;

            font-size: 1.1em;
        }

        /*  
        .contenedor_col  > div {
           width: 50%;
        }
  
        */
        .columna_izq_resumen_totales{
            width: 45%;  /* Este será el ancho que tendrá tu columna */
            float: left;; /* Aquí determinas de lado quieres quede esta "columna" */

            font-weight: bold;

        }

        .columna_resumen_totales{
            width: 45%;
            float: right;
            background-color: #FFFFFF;
            padding-right: 10px;
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

        /*
          $cant_items_minimo_una_sola_pagina: es el numero de lineas que soporta una factura para quedar completa en media hoja carta.   
        */
        $cant_items_minimo_una_sola_pagina = 6;
        $cantidad_items_primera_pagina_sin_footer = 10;
        
        /*
          $cant_maxima_items_segunda_pagina_con_footer: es el numero de lineas que soporta una factura para quedar completa en media hoja carta.   
        */
        $cant_maxima_items_segunda_pagina_con_footer = 18;
        
        $items_restantes = $cantidad_items - $cant_items_minimo_una_sola_pagina;
    ?>

    @if( $cantidad_items <= $cant_items_minimo_una_sola_pagina )

        <!-- UNA SOLA PAGINA -->
        <?php  
        
            $cantidad_total_paginas = 1;

            $factura = \View::make( 'ventas.formatos_impresion.estandar_con_copia.una_sola_pagina.factura', compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos', 'docs_relacionados', 'otroscampos', 'datos_factura', 'cliente', 'tipo_doc_app', 'pdv_descripcion', 'medios_pago','total_cantidad','total_factura', 'total_abonos', 'array_tasas', 'subtotal', 'total_descuentos', 'total_impuestos', 'impuesto_iva', 'cantidad_total_paginas' ) )->render();
        ?>
        
        <div class="contenedor">
            {!! $factura !!}

            {!! generado_por_appsiel() !!}
        </div>

        <br>

        <div class="contenedor">
            {!! $factura !!}

            {!! generado_por_appsiel() !!}
        </div>

    @endif
    
    <!-- DOS PAGINAS -->
    @if( $items_restantes > 0 && $items_restantes <= $cant_maxima_items_segunda_pagina_con_footer )

        @include('ventas.formatos_impresion.estandar_con_copia.dos_paginas.factura')

    @endif
    
</body>

</html>