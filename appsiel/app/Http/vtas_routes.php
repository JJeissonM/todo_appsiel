<?php

use Illuminate\Support\Facades\Route;

Route::post('vtas_agregar_precio_lista', 'Ventas\VentaController@agregar_precio_lista');
Route::post('vtas_doc_registro_guardar', 'Ventas\VentaController@doc_registro_guardar');
Route::get('vtas_get_formulario_edit_registro', 'Ventas\VentaController@get_formulario_edit_registro');

Route::get('vtas_get_lista_precios_cliente/{cliente_id}', 'Ventas\ClienteController@get_lista_precios_cliente');

// CONSULTAS GENERALES
Route::get('vtas_consultar_remisiones_pendientes', 'Ventas\VentaController@consultar_remisiones_pendientes');
Route::get('vtas_consultar_clientes', 'Ventas\VentaController@consultar_clientes');
Route::get('vtas_consultar_existencia_producto', 'Ventas\VentaController@consultar_existencia_producto');

Route::get('get_opciones_select_contactos/{cliente_id}', 'Ventas\ClienteController@get_opciones_select_contactos');

Route::post('vtas_totales_remisiones_seleccionadas', 'Ventas\VentaController@totales_remisiones_seleccionadas');

Route::post('factura_remision_pendiente', 'Ventas\VentaController@factura_remision_pendiente');

// FACTURAS
Route::resource('ventas', 'Ventas\VentaController');


// PEDIDOS
Route::get('vtas_pedidos_enviar_por_email/{id}', 'Ventas\PedidoController@enviar_por_email');
Route::get('vtas_pedidos_imprimir/{id}', 'Ventas\PedidoController@imprimir');
Route::post('vtas_pedidos_remision', 'Ventas\PedidoController@remision')->name('pedido.remision');
Route::get('vtas_pedidos_anular/{id}', 'Ventas\PedidoController@anular_pedido');
Route::get('vtas_pedidos_get_formulario_edit_registro','Ventas\PedidoController@get_formulario_edit_registro');
Route::post('vtas_pedidos_doc_registro_guardar','Ventas\PedidoController@doc_registro_guardar');

Route::get('vtas_mesero_listado_pedidos_pendientes','Ventas\PedidoController@mesero_listado_pedidos_pendientes');

Route::resource('vtas_pedidos', 'Ventas\PedidoController');

// Direciones de entrega (Domicilios)
Route::resource('vtas_direcciones_entrega', 'Ventas\DireccionEntregaController');



// NOTAS CREDITO
Route::get('ventas_notas_credito_anular/{id}', 'Ventas\NotaCreditoController@anular');
Route::resource('ventas_notas_credito', 'Ventas\NotaCreditoController');

Route::get('ventas_notas_credito_directa_anular/{id}', 'Ventas\NotaCreditoDirectaController@anular');
Route::resource('ventas_notas_credito_directa', 'Ventas\NotaCreditoDirectaController');
Route::post('nota_devolucion_pendiente', 'Ventas\NotaCreditoDirectaController@nota_devolucion_pendiente');

// IMPRIMIR - ENVIAR X EMAIL
Route::get('vtas_imprimir/{id}', 'Ventas\VentaController@imprimir');
Route::get('vtas_show_ventana_imprimir/{id}', 'Ventas\VentaController@show_ventana_imprimir');
Route::get('vtas_enviar_por_email/{id}', 'Ventas\VentaController@enviar_por_email');

Route::get('vtas_catalogos', 'Ventas\VentaController@catalogos');
Route::resource('vtas_vendedores', 'Ventas\VendedorController');

Route::post('vtas_clientes_tercero_a_cliente_store', 'Ventas\VentaController@tercero_a_cliente_store');
Route::get('vtas_clientes_tercero_a_cliente_create', 'Ventas\VentaController@tercero_a_cliente_create');

Route::get('vtas_tercero_a_cliente_create_direct/{tercero_id}/{url_redirect}', 'Ventas\VentaController@tercero_a_cliente_create_direct');

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
Route::post('vtas_reporte_rentabilidad', 'Ventas\ReportesController@vtas_reporte_rentabilidad');
Route::post('vtas_reporte_ventas_por_vendedor', 'Ventas\ReportesController@ventas_por_vendedor');

Route::post('vtas_remisiones_sin_factura_real', 'Ventas\ReportesController@remisiones_estado_facturadas_sin_factura_real');

Route::post('vtas_lineas_de_movimiento_repetidas', 'Ventas\ReportesController@lineas_de_movimiento_repetidas');
Route::post('vtas_reporte_pedidos', 'Ventas\ReportesController@reporte_pedidos');
Route::post('vtas_reporte_movimientos', 'Ventas\ReportesController@movimientos');
Route::post('vtas_documentos_facturacion', 'Ventas\ReportesController@documentos_facturacion');


// PROCESOS

Route::get('ventas_recontabilizar/{id}', 'Ventas\ProcesoController@recontabilizar_documento_factura');
Route::get('ventas_recontabilizar_nota/{id}', 'Ventas\ProcesoController@recontabilizar_documento_nota_credito');
Route::get('actualizar_valor_total_vtas_encabezados_doc', 'Ventas\ProcesoController@actualizar_valor_total_vtas_encabezados_doc');
Route::get('recontabilizar_documentos_ventas', 'Ventas\ProcesoController@recontabilizar_documentos_ventas');

Route::get('vtas_reconstruir_movimiento_documento/{documento_id}', 'Ventas\ProcesoController@reconstruir_movimiento_documento');
Route::get('vtas_reconstruir_movimiento_documento_por_lote/{inicial_documento_id}/{final_documento_id}', 'Ventas\ProcesoController@reconstruir_movimiento_documento_por_lote');

// Anular facturas masivas
Route::get('sales_documents_massive_canceling/{ids_list}', 'Ventas\ProcesoController@documents_massive_canceling');


//Coneccion de todos los procesos de ventas: cotizacion, pedidos, remisiones, facturas de venta

Route::post('vtas_form_crear_remision_desde_doc_venta','Ventas\ProcesoController@form_crear_remision_desde_doc_venta');
Route::post('vtas_crear_remision_y_factura_desde_doc_venta','Ventas\ProcesoController@crear_remision_y_factura_desde_doc_venta');
Route::post('ventas_conexion/procesos/procesar/masivo','Ventas\ProcesoController@conexion_procesos')->name('ventas.conexion_procesos');

// Pedidos de restarurantes
/**
 * vendedor_id = MESERO
 * cliente_id = MESA
 * 
 */
Route::resource('vtas_pedidos_restaurante', 'Ventas\PedidoRestauranteController');

Route::get('vtas_pedidos_restaurante_mesas_disponibles_mesero/{vendedor_id}', 'Ventas\PedidoRestauranteController@get_mesas_disponibles_mesero');

Route::get('vtas_get_pedidos_mesero_para_una_mesa/{vendedor_id}/{cliente_id}', 'Ventas\PedidoRestauranteController@get_pedidos_mesero_para_una_mesa');

Route::get('vtas_cargar_datos_editar_pedido/{pedido_id}', 'Ventas\PedidoRestauranteController@cargar_datos_editar_pedido');
Route::get('vtas_pedidos_restaurante_cancel/{pedido_id}/{user_email}', 'Ventas\PedidoRestauranteController@cancel');

Route::get('vtas_pedidos_restaurante_mesas_permitidas_para_cambiar', 'Ventas\PedidoRestauranteController@mesas_permitidas_para_cambiar');
Route::get('vtas_pedidos_restaurante_cambiar_pedidos_de_mesa/{mesa_pedidos_id}/{nueva_mesa_id}', 'Ventas\PedidoRestauranteController@cambiar_pedidos_de_mesa');

Route::get('vtas_pedidos_restaurante_pruebas', 'Ventas\PedidoRestauranteController@pruebas');
