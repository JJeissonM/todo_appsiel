<!-- DOS PAGINAS -->
<?php
    
    $cantidad_total_paginas = 2;

    $encabezado_factura = \View::make( 'ventas.formatos_impresion.estandar_con_copia.encabezado_factura', compact('doc_encabezado', 'empresa', 'resolucion', 'etiquetas', 'cantidad_total_paginas' ) )->render();
    
    // doc_registros queda truncado
    $doc_registros_restantes = $doc_registros->splice( $cantidad_items_primera_pagina_sin_footer );

    // Para agregar leyenda de ESPACIO EN BLANCO
    $row_span = $cantidad_items_primera_pagina_sin_footer - $doc_registros->count() - 1;

    $lineas_registros_primera_pagina_sin_footer = \View::make( 'ventas.formatos_impresion.estandar_con_copia.lineas_registros_primera_pagina_sin_footer', compact( 'doc_registros', 'row_span' ) )->render();
    
    $lineas_registros_segunda_pagina = \View::make( 'ventas.formatos_impresion.estandar_con_copia.dos_paginas.lineas_registros_segunda_pagina', compact( 'doc_registros_restantes', 'total_abonos', 'cantidad_items', 'cantidad_total_paginas' ) )->render();
    
    $tabla_impuestos_totales_y_firma = \View::make( 'ventas.formatos_impresion.estandar_con_copia.footer', compact( 'otroscampos', 'resolucion', 'array_tasas', 'subtotal', 'total_descuentos', 'total_impuestos', 'impuesto_iva', 'total_factura' ) )->render();
?>
<div class="contenedor">
    {!! $encabezado_factura !!}

    {!! $lineas_registros_primera_pagina_sin_footer !!}

    <div class="generado_por">
        {!! generado_por_appsiel() !!}
    </div>
</div>

<br>

<div class="contenedor">
    {!! $encabezado_factura !!}

    {!! $lineas_registros_primera_pagina_sin_footer !!}

    <div class="generado_por">
        {!! generado_por_appsiel() !!}
    </div>
</div>

<div class="page-break"></div>

<!-- Segunda pagina -->
<div class="contenedor">
    {!! $lineas_registros_segunda_pagina !!}
    
    {!! $tabla_impuestos_totales_y_firma !!}  

    <div class="generado_por">
        {!! generado_por_appsiel() !!}
    </div>
</div>

<br>

<div class="contenedor">
    {!! $lineas_registros_segunda_pagina !!}
    
    {!! $tabla_impuestos_totales_y_firma !!} 

    <div class="generado_por">
        {!! generado_por_appsiel() !!}
    </div>
</div>