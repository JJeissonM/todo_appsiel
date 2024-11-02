
<style type="text/css">

    @page {
      margin: 5px;
      size: {{ config('ventas_pos.ancho_formato_impresion') . 'in' }} 230mm;
    }
    
    .page-break {
        page-break-after: always;
    }
    
    /*p { margin: -2px; }*/
</style>

<?php
    $i=$numero_columnas;
    $minimo_comun_multiplo_columnas = 12;
    $tamanio_letra = 10;

    $cantidad_stickers_x_pagina = 6;
    $contador_paginas = 1;

    $cantidad_total = count($items);
    $cantidad_impresos = 1;
?>
     
@foreach($items as $fila)       
    @include('inventarios.reportes.pos_80mm.una_etiqueta_codigo_barras')
    <?php
        if ( $contador_paginas == $cantidad_stickers_x_pagina && $cantidad_impresos != $cantidad_total) {
            echo '<div class="page-break"></div>';
            $contador_paginas = 0;
        }
        $contador_paginas++;
        $cantidad_impresos++;
    ?>
@endforeach