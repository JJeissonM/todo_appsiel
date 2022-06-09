<?php

Route::resource('ventas_pos', 'VentasPos\AplicacionController');

Route::get( 'ventas_pos_set_catalogos/{pdv_id}', 'VentasPos\FacturaPosController@set_catalogos');

Route::get('pos_get_saldos_caja_pdv/{pdv_id}/{fecha_desde}/{fecha_hasta}', 'VentasPos\ReporteController@get_saldos_caja_pdv');
Route::get('pos_consultar_documentos_pendientes/{pdv_id}/{fecha_desde}/{fecha_hasta}', 'VentasPos\ReporteController@consultar_documentos_pendientes');

Route::get('pos_factura_imprimir/{doc_encabezado_id}', 'VentasPos\FacturaPosController@imprimir');

Route::get('pos_hacer_desarme_automatico/{pdv_id}/{fecha}', 'VentasPos\FacturaPosController@hacer_desarme_automatico');

// Anular factura que no esté acumulada
Route::get('pos_factura_anular/{doc_encabezado_id}', 'VentasPos\FacturaPosController@anular_factura_pos');

Route::get('pos_factura_validar_existencias/{pdv_id}', 'VentasPos\FacturaPosController@validar_existencias');

Route::get('pos_factura_acumular/{pdv_id}', 'VentasPos\FacturaPosController@acumular');
Route::get('pos_acumular_una_factura/{factura_id}', 'VentasPos\FacturaPosController@acumular_una_factura');

Route::get('pos_factura_contabilizar/{pdv_id}', 'VentasPos\FacturaPosController@contabilizar');
Route::get('pos_contabilizar_una_factura/{factura_id}', 'VentasPos\FacturaPosController@contabilizar_una_factura');

Route::get('ventas_pos_form_registro_ingresos_gastos/{pdv_id}/{id_modelo}/{id_transaccion}', 'VentasPos\FacturaPosController@form_registro_ingresos_gastos');
Route::post('ventas_pos_form_registro_ingresos_gastos', 'VentasPos\FacturaPosController@store_registro_ingresos_gastos');


// 				CREAR DESDE PEDIDO DE VENTAS
Route::get('pos_factura_crear_desde_pedido/{pedido_id}', 'VentasPos\FacturaPosController@crear_desde_pedido');
Route::get('pos_revisar_pedidos_ventas/{pdv_id}', 'VentasPos\ReporteController@revisar_pedidos_ventas');



// Proceso especial

Route::get('factura_pos_recontabilizar/{id}', 'VentasPos\FacturaPosController@recontabilizar_factura');

// Generar remisiones para documentos ya acumulados
Route::get('pos_factura_generar_remisiones/{pdv_id}', 'VentasPos\FacturaPosController@generar_remisiones');

Route::post('ventas_pos_anular_factura', 'VentasPos\FacturaPosController@anular_factura_acumulada');
Route::resource('pos_factura', 'VentasPos\FacturaPosController');

// Archivos planos
Route::post('ventas_pos_cargue_archivo_plano', 'VentasPos\ArchivoPlanoController@procesar_archivo');



// RECETAS RESTAURANTE

Route::get('vtas_pos_hacer_preparaciones_recetas/{pdv_id}', 'VentasPos\FacturaPosController@hacer_preparaciones_recetas');
