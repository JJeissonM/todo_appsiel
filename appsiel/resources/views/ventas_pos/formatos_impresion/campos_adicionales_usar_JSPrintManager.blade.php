<input type="hidden" id="enviar_impresion_directamente_a_la_impresora" name="enviar_impresion_directamente_a_la_impresora" value="{{ $params_JSPrintManager->enviar_impresion_directamente_a_la_impresora }}">

<input type="hidden" id="imprimir_factura_automaticamente" name="imprimir_factura_automaticamente" value="{{ $params_JSPrintManager->imprimir_factura_automaticamente }}">

<input type="hidden" id="impresora_principal_por_defecto" name="impresora_principal_por_defecto" value="{{ $params_JSPrintManager->impresora_principal_por_defecto }}">

<input type="hidden" id="impresora_cocina_por_defecto" name="impresora_cocina_por_defecto" value="{{ $params_JSPrintManager->impresora_cocina_por_defecto }}">

<input type="hidden" id="ancho_formato_impresion" name="ancho_formato_impresion" value="{{ config('ventas_pos.ancho_formato_impresion') }}">

<input type="hidden" id="tamanio_letra_impresion_items_cocina" name="tamanio_letra_impresion_items_cocina" value="{{ config('ventas_pos.tamanio_letra_impresion_items_cocina') }}">

<input type="hidden" id="url_post_servidor_impresion" value="{{ config('ventas.url_post_servidor_impresion') }}">

<input type="hidden" id="metodo_impresion_pedido_ventas" value="{{ config('ventas.metodo_impresion_pedido_ventas') }}">
<input type="hidden" id="metodo_impresion_pedido_restaurante" value="{{ config('ventas.metodo_impresion_pedido_restaurante') }}">
<input type="hidden" id="metodo_impresion_factura_pos" value="{{ config('ventas.metodo_impresion_factura_pos') }}">
<input type="hidden" id="apm_ws_url" value="{{ config('ventas.apm_ws_url') }}">
<input type="hidden" id="apm_printer_id_pedidos_ventas" value="{{ config('ventas.apm_printer_id_pedidos_ventas') }}">
<input type="hidden" id="apm_printer_id_pedidos_restaurante" value="{{ config('ventas.apm_printer_id_pedidos_restaurante') }}">
<input type="hidden" id="apm_printer_id_factura_pos" value="{{ config('ventas.apm_printer_id_factura_pos') }}">
