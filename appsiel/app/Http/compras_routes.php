<?php

Route::post('compras_doc_registro_guardar', 'Compras\CompraController@doc_registro_guardar');

Route::get('compras_get_formulario_edit_registro', 'Compras\CompraController@get_formulario_edit_registro');
Route::get('compras_consultar_entradas_pendientes', 'Compras\CompraController@consultar_entradas_pendientes');

Route::get('compras_consultar_proveedores', 'Compras\CompraController@consultar_proveedores');
Route::get('compras_consultar_existencia_producto', 'Compras\CompraController@consultar_existencia_producto');
Route::get('compras_validar_documento_proveedor', 'Compras\CompraController@validar_documento_proveedor');


Route::resource('factura_entrada_pendiente', 'Compras\FacturaEntradaPendienteController');
Route::resource('compras', 'Compras\CompraController');

// ORDEN DE COMPRA
Route::get('orden_compra_anular/{id}', 'Compras\OrdenCompraController@anular');
Route::resource('orden_compra', 'Compras\OrdenCompraController');
Route::post('orden_compra/entrada_almacen', 'Compras\OrdenCompraController@entrada_almacen')->name('almacen.entrada');

// NOTAS CREDITO
Route::get('compras_notas_credito_anular/{id}', 'Compras\NotaCreditoController@anular');
Route::resource('compras_notas_credito', 'Compras\NotaCreditoController');

Route::get('compras_notas_credito_directa_anular/{id}', 'Compras\NotaCreditoDirectaController@anular');
Route::resource('compras_notas_credito_directa', 'Compras\NotaCreditoDirectaController');

// IMPRIMIR - ENVIAR X EMAIL
Route::get('compras_imprimir/{id}', 'Compras\CompraController@imprimir');
Route::get('compras_enviar_por_email/{id}', 'Compras\CompraController@enviar_por_email');

// CATÁLOGOS
Route::get('compras_catalogos', 'Compras\CompraController@catalogos');

Route::post('compras_proveedores_tercero_a_proveedor_store', 'Compras\ProveedorController@tercero_a_proveedor_store');
//Route::get('compras_proveedores_tercero_a_proveedor_create', 'Compras\ProveedorController@tercero_a_proveedor_create');
Route::resource('compras_proveedores', 'Compras\ProveedorController');


// Anular
Route::post('compras_anular_factura', 'Compras\CompraController@anular_factura');
/*Route::get('compras/eliminar_bodega/{id}', 'Compras\CompraController@eliminar_bodega');
Route::get('compras/eliminar_producto/{id}', 'Compras\CompraController@eliminar_producto');
*/

// REPORTES
Route::post('compras_ctas_por_pagar', 'Compras\ReportesController@ctas_por_pagar');
Route::post('compras_precio_compra_por_producto', 'Compras\ReportesController@precio_compra_por_producto');

// PROCESOS
Route::get('actualizar_valor_total_compras_encabezados_doc', 'Compras\ProcesoController@actualizar_valor_total_compras_encabezados_doc');

Route::get('recalcular_entradas_almacen_recosteadas', 'Compras\ProcesoController@recalcular_entradas_almacen_recosteadas');

Route::get('recontabilizar_documentos_compras', 'Compras\ProcesoController@recontabilizar_documentos_compras');
Route::get('compras_recontabilizar_nota/{id}', 'Compras\ProcesoController@recontabilizar_documento_nota_credito');
