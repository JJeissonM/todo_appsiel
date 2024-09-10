
@include('ventas.formatos_impresion.estandar_con_copia.encabezado_factura')

@include('ventas.formatos_impresion.estandar_con_copia.una_sola_pagina.lineas_registros')

@include('ventas.formatos_impresion.estandar_con_copia.tabla_impuestos_totales_y_firma')

@include('transaccion.registros_contables')

@include('transaccion.auditoria')

@include('core.firmas')

<table style="width: 100%;">
    @yield('firma_fila_adicional')
</table>
