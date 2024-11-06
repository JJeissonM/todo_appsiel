
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
    $i=$numero_columnas;
    $minimo_comun_multiplo_columnas = 12;
    $tamanio_letra = 11;

    $cantidad_stickers_x_pagina = 9;
    $contador_paginas = 1;

    $cantidad_total = count($items);
    $cantidad_impresos = 1;

    $top_margin = 10;
?>
     
@foreach($items as $fila)

    <?php 
       /* if ( $cantidad_impresos % 18 == 0 ) {
            $top_margin = 5;
        }else{
            $top_margin = 10;
        }*/
    ?>

    @include('inventarios.reportes.pos_80mm.una_etiqueta_codigo_barras',['top_margin' => $top_margin])
    <?php
        if ( $contador_paginas == $cantidad_stickers_x_pagina && $cantidad_impresos != $cantidad_total) {
            echo '<div class="page-break"></div>';
            $contador_paginas = 0;
        }
        $contador_paginas++;
        $cantidad_impresos++;
    ?>
@endforeach