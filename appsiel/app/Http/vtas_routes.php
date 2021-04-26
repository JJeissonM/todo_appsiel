<?php


Route::post('vtas_agregar_precio_lista', 'Ventas\VentaController@agregar_precio_lista');
Route::post('vtas_doc_registro_guardar', 'Ventas\VentaController@doc_registro_guardar');
Route::get('vtas_get_formulario_edit_registro', 'Ventas\VentaController@get_formulario_edit_registro');

Route::get('vtas_get_lista_precios_cliente/{cliente_id}', 'Ventas\ClienteController@get_lista_precios_cliente');

// CONSULTAS GENERALES
Route::get('vtas_consultar_remisiones_pendientes', 'Ventas\VentaController@consultar_remisiones_pendientes');
Route::get('vtas_consultar_clientes', 'Ventas\VentaController@consultar_clientes');
Route::get('vtas_consultar_existencia_producto', 'Ventas\VentaController@consultar_existencia_producto');

Route::post('factura_remision_pendiente', 'Ventas\VentaController@factura_remision_pendiente');

// FACTURAS
Route::resource('ventas', 'Ventas\VentaController');
Route::resource('factura_medica', 'Ventas\FacturaMedicaController');


// PEDIDOS
Route::get('vtas_pedidos_enviar_por_email/{id}', 'Ventas\PedidoController@enviar_por_email');
Route::get('vtas_pedidos_imprimir/{id}', 'Ventas\PedidoController@imprimir');
Route::resource('vtas_pedidos', 'Ventas\PedidoController');
Route::post('vtas_pedidos_remision', 'Ventas\PedidoController@remision')->name('pedido.remision');
Route::get('vtas_pedidos_anular/{id}', 'Ventas\PedidoController@anular_pedido');
Route::get('vtas_pedidos_get_formulario_edit_registro','Ventas\PedidoController@get_formulario_edit_registro');
Route::post('vtas_pedidos_doc_registro_guardar','Ventas\PedidoController@doc_registro_guardar');


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
Route::post('ventas_anular_factura_medica', 'Ventas\FacturaMedicaController@anular_factura');
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
Route::post('vtas_reporte_rentabilidad', 'Ventas\ReportesController@vtas_reporte_rentabilidad');


// PROCESOS

Route::get('ventas_recontabilizar/{id}', 'Ventas\ProcesoController@recontabilizar_documento_factura');
Route::get('ventas_recontabilizar_nota/{id}', 'Ventas\ProcesoController@recontabilizar_documento_nota_credito');
Route::get('actualizar_valor_total_vtas_encabezados_doc', 'Ventas\ProcesoController@actualizar_valor_total_vtas_encabezados_doc');
Route::get('recontabilizar_documentos_ventas', 'Ventas\ProcesoController@recontabilizar_documentos_ventas');


//Coneccion de todos los procesos de ventas: cotizacion, pedidos, remisiones, facturas de venta

Route::post('ventas_conexion/procesos/procesar/masivo','Ventas\ProcesoController@conexion_procesos')->name('ventas.conexion_procesos');