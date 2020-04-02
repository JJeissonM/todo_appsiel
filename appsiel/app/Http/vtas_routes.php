<?php

Route::post('vtas_doc_registro_guardar', 'Ventas\VentaController@doc_registro_guardar');
Route::get('vtas_get_formulario_edit_registro', 'Ventas\VentaController@get_formulario_edit_registro');

// CONSULTAS GENERALES
Route::get('vtas_consultar_remisiones_pendientes', 'Ventas\VentaController@consultar_remisiones_pendientes');
Route::get('vtas_consultar_clientes', 'Ventas\VentaController@consultar_clientes');
Route::get('vtas_consultar_existencia_producto', 'Ventas\VentaController@consultar_existencia_producto');

Route::post('factura_remision_pendiente', 'Ventas\VentaController@factura_remision_pendiente');
Route::resource('ventas', 'Ventas\VentaController');

// PEDIDOS
Route::get('vtas_pedidos_enviar_por_email/{id}', 'Ventas\PedidoController@enviar_por_email');
Route::get('vtas_pedidos_imprimir/{id}', 'Ventas\PedidoController@imprimir');
Route::resource('vtas_pedidos', 'Ventas\PedidoController');
Route::post('vtas_pedidos_remision', 'Ventas\PedidoController@remision')->name('pedido.remision');
Route::get('vtas_pedidos_anular/{id}', 'Ventas\PedidoController@anular_pedido');


// NOTAS CREDITO
Route::get('ventas_notas_credito_anular/{id}', 'Ventas\NotaCreditoController@anular');
Route::resource('ventas_notas_credito', 'Ventas\NotaCreditoController');

Route::get('ventas_notas_credito_directa_anular/{id}', 'Ventas\NotaCreditoDirectaController@anular');
Route::resource('ventas_notas_credito_directa', 'Ventas\NotaCreditoDirectaController');
Route::post('nota_devolucion_pendiente', 'Ventas\NotaCreditoDirectaController@nota_devolucion_pendiente');

// IMPRIMIR - ENVIAR X EMAIL
Route::get('vtas_imprimir/{id}', 'Ventas\VentaController@imprimir');
Route::get('vtas_enviar_por_email/{id}', 'Ventas\VentaController@enviar_por_email');

Route::get('vtas_catalogos', 'Ventas\VentaController@catalogos');
Route::resource('vtas_vendedores', 'Ventas\VendedorController');

Route::post('vtas_clientes_tercero_a_cliente_store', 'Ventas\VentaController@tercero_a_cliente_store');
Route::get('vtas_clientes_tercero_a_cliente_create', 'Ventas\VentaController@tercero_a_cliente_create');
Route::resource('vtas_clientes', 'Ventas\ClienteController');

// Cotizaciones
Route::get('vtas_cotizacion_enviar_por_email/{id}', 'Ventas\CotizacionController@enviar_por_email');
Route::get('vtas_cotizacion_imprimir/{id}', 'Ventas\CotizacionController@imprimir');
Route::resource('vtas_cotizacion', 'Ventas\CotizacionController');

// Eliminar
Route::post('ventas_anular_factura', 'Ventas\VentaController@anular_factura');
Route::get('eliminar_cliente/{id}', 'Ventas\ClienteController@eliminar_cliente');
Route::get('vtas_cotizacion_anular/{id}', 'Ventas\CotizacionController@anular_cotizacion');
/*
Route::get('ventas/eliminar_grupo_venta/{id}', 'Ventas\VentaController@eliminar_grupo_venta');
Route::get('ventas/eliminar_bodega/{id}', 'Ventas\VentaController@eliminar_bodega');
Route::get('ventas/eliminar_producto/{id}', 'Ventas\VentaController@eliminar_producto');
*/

// REPORTES
Route::post('vtas_precio_venta_por_producto', 'Ventas\ReportesController@precio_venta_por_producto');
Route::post('vtas_reporte_ventas', 'Ventas\ReportesController@vtas_reporte_ventas');


// PROCESOS

Route::get('ventas_recontabilizar/{id}', 'Ventas\ProcesoController@recontabilizar_documento_factura');
Route::get('ventas_recontabilizar_nota/{id}', 'Ventas\ProcesoController@recontabilizar_documento_nota_credito');
Route::get('actualizar_valor_total_vtas_encabezados_doc', 'Ventas\ProcesoController@actualizar_valor_total_vtas_encabezados_doc');
Route::get('recontabilizar_documentos_ventas', 'Ventas\ProcesoController@recontabilizar_documentos_ventas');



/*
	Desarrollo personalizado para el Cliente AVIPOULET
	Obtener los productos pesados en una balanza marca DIBAL y agrgarlos a las líneas de registros
	al momento de hacer una factura
*/
Route::get('vtas_get_productos_por_facturar','Ventas\BasculaDibalController@get_productos_por_facturar');