<?php

use Illuminate\Support\Facades\Route;

Route::resource('ventas_pos', 'VentasPos\AplicacionController');

Route::get('ventas_pos_testing', 'VentasPos\AplicacionController@testing');

Route::get( 'ventas_pos_set_catalogos/{pdv_id}', 'VentasPos\FacturaPosController@set_catalogos');

Route::get('pos_factura_imprimir/{doc_encabezado_id}', 'VentasPos\FacturaPosController@imprimir');

Route::get('pos_hacer_desarme_automatico/{pdv_id}/{fecha}', 'VentasPos\FacturaPosController@hacer_desarme_automatico');

Route::get('pos_get_doc_encabezado_por_uniqid/{uniqid}', 'VentasPos\FacturaPosController@get_doc_encabezado_por_uniqid');

// Anular factura que no esté acumulada
Route::get('pos_factura_anular/{doc_encabezado_id}', 'VentasPos\FacturaPosController@anular_factura_pos');

Route::get('pos_factura_borrar_propina/{doc_encabezado_id}', 'VentasPos\FacturaPosController@borrar_propina');

Route::get('pos_factura_validar_existencias/{pdv_id}', 'VentasPos\FacturaPosController@validar_existencias');

Route::get('pos_acumular_una_factura/{factura_id}', 'VentasPos\FacturaPosController@acumular_una_factura');
Route::get('pos_acumular_una_factura_individual/{factura_id}', 'VentasPos\FacturaPosController@acumular_una_factura_individual');

Route::get('pos_factura_contabilizar/{pdv_id}', 'VentasPos\FacturaPosController@contabilizar');
Route::get('pos_contabilizar_una_factura/{factura_id}', 'VentasPos\FacturaPosController@contabilizar_una_factura');

Route::get('ventas_pos_form_registro_ingresos_gastos/{pdv_id}/{id_modelo}/{id_transaccion}', 'VentasPos\FacturaPosController@form_registro_ingresos_gastos');
Route::post('ventas_pos_form_registro_ingresos_gastos', 'VentasPos\FacturaPosController@store_registro_ingresos_gastos');

// 				CREAR DESDE PEDIDO DE VENTAS
Route::get('pos_factura_crear_desde_pedido/{pedido_id}', 'VentasPos\FacturaPosController@crear_desde_pedido');
Route::get('pos_revisar_pedidos_ventas/{pdv_id}', 'VentasPos\ReporteController@revisar_pedidos_ventas');

Route::get('pos_cargar_pedido/{pedido_id}', 'VentasPos\PedidosPosController@cargar_pedido');
Route::get('pos_anular_pedido/{pedido_id}', 'VentasPos\PedidosPosController@anular_pedido');

// Generar remisiones para documentos ya acumulados
Route::get('pos_factura_generar_remisiones/{pdv_id}', 'VentasPos\FacturaPosController@generar_remisiones');

Route::post('ventas_pos_anular_factura', 'VentasPos\FacturaPosController@anular_factura_acumulada');
Route::resource('pos_factura', 'VentasPos\FacturaPosController');

Route::resource('pos_pedido', 'VentasPos\PedidosPosController');
Route::get('pos_consultar_mis_pedidos_pendientes/{pdv_id}', 'VentasPos\PedidosPosController@consultar_mis_pedidos_pendientes');

// Archivos planos
Route::post('ventas_pos_cargue_archivo_plano', 'VentasPos\ArchivoPlanoController@procesar_archivo');

// RECETAS RESTAURANTE
Route::get('vtas_pos_hacer_preparaciones_recetas/{pdv_id}', 'VentasPos\FacturaPosController@hacer_preparaciones_recetas');


// REPORTES
Route::get('pos_get_saldos_caja_pdv/{pdv_id}/{fecha_desde}/{fecha_hasta}', 'VentasPos\ReporteController@get_saldos_caja_pdv'); // ESTADO PDV

Route::get('pos_consultar_documentos_pendientes/{pdv_id}/{fecha_desde}/{fecha_hasta}', 'VentasPos\ReporteController@consultar_documentos_pendientes');

Route::post('pos_movimientos_ventas', 'VentasPos\ReporteController@movimientos_ventas');
Route::post('pos_resumen_existencias', 'VentasPos\ReporteController@resumen_existencias');
Route::post('pos_comprobante_informe_diario', 'VentasPos\ReporteController@comprobante_informe_diario');

Route::post('pos_resumen_diario', 'VentasPos\ReporteController@resumen_diario');

Route::get('pos_get_facturas_con_lineas_registros_sin_movimiento/{fecha_desde}/{fecha_hasta}', 'VentasPos\ReporteController@get_facturas_con_lineas_registros_sin_movimiento');


// PROCESOS
Route::get('factura_pos_recontabilizar/{id}', 'VentasPos\FacturaPosController@recontabilizar_factura');

Route::get('ventas_pos_reconstruir_mov_ventas_documento/{documento_id}', 'VentasPos\ProcesosController@reconstruir_mov_ventas_documento');

Route::get('vtas_pos_form_modificar_total_factura/{documento_id}', 'VentasPos\ProcesosController@form_modificar_total_factura');
Route::post('vtas_pos_store_nuevo_total_factura', 'VentasPos\ProcesosController@store_nuevo_total_factura');

Route::get('vtas_pos_reducir_porcentaje_facturacion/{fecha_ini}/{fecha_fin}/{porcentaje_disminucion}', 'VentasPos\ProcesosController@reducir_porcentaje_facturacion');

// fact. elct.
Route::resource('pos_factura_electronica', 'VentasPos\FacturaElectronicaController');

Route::get('pos_acumulacion_convertir_en_factura_electronica/{vtas_doc_encabezado_id}', 'VentasPos\FacturaElectronicaController@convertir_en_factura_electronica');

// RESTAURANTE
Route::resource('pos_factura_restaurante', 'VentasPos\FacturaRestauranteController');


Route::resource('pos_clientes', 'VentasPos\ClienteController');