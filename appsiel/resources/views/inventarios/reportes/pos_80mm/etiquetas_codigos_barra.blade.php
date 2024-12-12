
<style type="text/css">

    @page {
      margin: 5px;
      size: 67mm 330mm;
      font-family: "Lucida Console", "Courier New", monospace;
    }
    
    .page-break {
        page-break-after: always;
    }
    
    /*p { margin: -2px; }*/
</style>

<?php
    $i = $numero_columnas;
    $minimo_comun_multiplo_columnas = 12;
    $tamanio_letra = 11;

    $cantidad_stickers_x_pagina = 9;
    $contador_paginas = 1;

    $cantidad_total = count($items);
    $cantidad_impresos = 1;

    $top_margin = 10;

    $max_characters = 30;

    $barcodes = [];
?>

<h5>Se generaron {{ $cantidad_total }} etiquetas <small> <br> Cada página tiene 30 etiquetas y se deben imprimir página por página. <br> <span style="color: brown;">Recuerde que debe acomodar el papel antes de imprimir cada página. </span></small></h5>
     
@foreach($items as $fila)

    <?php 
       /* if ( $cantidad_impresos % 18 == 0 ) {
            $top_margin = 5;
        }else{
            $top_margin = 10;
        }*/

        $label = '';
        if($mostrar_descripcion)
        {
            $label = $fila->label; //substr( $fila->label, 0, $max_characters);
        }

        $codigo_barras = $fila->codigo_barras;
        if ( !is_numeric($codigo_barras) ) {            
            $codigo_barras = $fila->id;
        }

        $barcode_description = $codigo_barras . $fila->get_codigo_proveedor() . $fila->get_talla();
    ?>

    @include('inventarios.reportes.pos_80mm.una_etiqueta_codigo_barras',['top_margin' => $top_margin])

    <?php
        if ( $contador_paginas == $cantidad_stickers_x_pagina && $cantidad_impresos != $cantidad_total) {
            echo '<div class="page-break"></div>';
            $contador_paginas = 0;
        }
        $contador_paginas++;
        $cantidad_impresos++;

        $barcodes[] = (object)[
            'label' =>  $label, 
            'barcode' => $codigo_barras,
            'barcode_description' => $barcode_description
        ];
    ?>
@endforeach

<?php
    $data = (object)[ 
        'barcodes' => $barcodes, 
        'stickers_quantity' => $cantidad_impresos - 1,
        'columns' => $numero_columnas
    ];
?>
<span id="data_for_print" style="color: white;">
    {{ json_encode($data) }}
</span>