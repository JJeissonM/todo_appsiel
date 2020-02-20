<?php 


Route::get('inv_get_formulario_edit_registro','Inventarios\InventarioController@get_formulario_edit_registro');
Route::post('inv_doc_registro_guardar','Inventarios\InventarioController@doc_registro_guardar');


Route::get('get_ajax','Inventarios\InventarioController@get_ajax');

// AL cambiar la selección de un producto en el formulario de ingreso_productos_2.blade.php
Route::post('post_ajax','Inventarios\InventarioController@post_ajax');

// CONSULTAS GENERALES
Route::get('inv_consultar_productos','Inventarios\InventarioController@consultar_productos');
Route::get('inv_consultar_existencia_producto','Inventarios\InventarioController@consultar_existencia_producto');
Route::get('inv_validacion_saldo_movimientos_posteriores/{bodega_id}/{producto_id}/{fecha}/{cantidad_nueva}/{saldo_a_la_fecha}/{movimiento}/{cantidad_anterior?}','Inventarios\InventarioController@get_validacion_saldo_movimientos_posteriores');





Route::get('inv_fisico_imprimir/{id}','Inventarios\InvFisicoController@imprimir');
Route::get('inv_fisico_hacer_ajuste','Inventarios\InvFisicoController@hacer_ajuste');
Route::get('inv_get_productos_del_grupo','Inventarios\InvFisicoController@get_productos_del_grupo');
Route::resource('inv_fisico', 'Inventarios\InvFisicoController');

//Route::get('inv_balance', 'Inventarios\InventarioController@inv_balance');
//Route::post('ajax_balance', 'Inventarios\InventarioController@ajax_balance');
Route::resource('inventarios', 'Inventarios\InventarioController');
Route::get('inv_get_lista_productos', 'Inventarios\InventarioController@get_lista_productos');

Route::get('transaccion_print/{id_transaccion}','Inventarios\InventarioController@imprimir');
Route::resource('transaccion','Core\TransaccionController');


Route::get('inventarios/eliminar_grupo_inventario/{id}', 'Inventarios\InventarioController@eliminar_grupo_inventario');
Route::get('inventarios/eliminar_bodega/{id}', 'Inventarios\InventarioController@eliminar_bodega');
Route::get('inventarios/eliminar_producto/{id}', 'Inventarios\InventarioController@eliminar_producto');


Route::get('inv_anular_documento/{id}', 'Inventarios\InventarioController@anular_documento');

// REPORTES
Route::get('inv_movimiento', 'Inventarios\ReporteController@inv_movimiento');
Route::post('ajax_movimiento', 'Inventarios\ReporteController@ajax_movimiento');
Route::get('inv_existencias', 'Inventarios\ReporteController@inv_existencias');
Route::post('inv_etiquetas_codigos_barra', 'Inventarios\ReporteController@inv_etiquetas_codigos_barra');
Route::post('inv_existencias_corte', 'Inventarios\ReporteController@inv_existencias_corte');


// inv_consultar_existencias desde el index para cada bodega
Route::get('inv_consultar_existencias/{bodega_id}', 'Inventarios\ReporteController@inv_consultar_existencias');

Route::post('ajax_existencias', 'Inventarios\ReporteController@ajax_existencias');

Route::get('inv_pdf_existencias', 'Inventarios\ReporteController@inv_pdf_existencias');

Route::get('inv_stock_minimo', 'Inventarios\ReporteController@inv_stock_minimo');




// PROCESOS
Route::get('recontabilizar_documentos_inventarios', 'Inventarios\ProcesoController@recontabilizar_documentos_inventarios');
Route::get('inv_recontabilizar_un_documento/{id}', 'Inventarios\ProcesoController@recontabilizar_un_documento');

Route::get('inv_recosteo_form','Inventarios\InventarioController@recosteo_form');
Route::get('inv_recosteo', 'Inventarios\ProcesoController@recosteo');

Route::get('inv_corregir_cantidades', 'Inventarios\ProcesoController@corregir_cantidades');
