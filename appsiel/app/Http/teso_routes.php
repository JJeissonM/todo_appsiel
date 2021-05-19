<?php 

/* --------------- Libreta de pagos ------------------- */
Route::get('tesoreria/libreta_pagos/eliminar/{id_fila}','Tesoreria\LibretaPagoController@eliminar_libreta_pagos');

//Route::get('/tesoreria/nueva_libreta/{aux}','Tesoreria\LibretaPagoController@nueva_libreta');

Route::get('/tesoreria/imprimir_libreta/{id_libreta}','Tesoreria\LibretaPagoController@imprimir_libreta');
Route::get('tesoreria/editar_libreta/{id_libreta}','Tesoreria\LibretaPagoController@edit');

Route::get('/tesoreria/hacer_recaudo/{id_libreta}','Tesoreria\LibretaPagoController@hacer_recaudo');
Route::get('tesoreria/eliminar_recaudo_libreta/{recaudo_id}','Tesoreria\LibretaPagoController@eliminar_recaudo_libreta');

Route::get('/tesoreria/ver_recaudos/{id_libreta}','Tesoreria\LibretaPagoController@ver_recaudos');
Route::get('/tesoreria/ver_plan_pagos/{id_libreta}','Tesoreria\LibretaPagoController@ver_plan_pagos');

Route::post('/tesoreria/guardar_recaudo','Tesoreria\LibretaPagoController@guardar_recaudo');
Route::post('/tesoreria/guardar_recaudo_cartera','Tesoreria\LibretaPagoController@guardar_recaudo_cartera');
Route::get('/tesoreria/hacer_recaudo_cartera/{id_cartera}','Tesoreria\LibretaPagoController@hacer_recaudo_cartera');
Route::get('/tesoreria/imprimir_comprobante_recaudo/{id_cartera}','Tesoreria\LibretaPagoController@imprimir_comprobante_recaudo');

Route::resource('teso_libreta_pagos','Tesoreria\LibretaPagoController');
/* ---------------------------------- */


Route::get('/tesoreria/imprimir_cartera/{concepto}/{tipo}/{id}','Tesoreria\TesoreriaController@imprimir_cartera');

Route::get('tesoreria/get_cajas_cuentas_bancarias/{empresa_id}','Tesoreria\TesoreriaController@get_cajas_cuentas_bancarias');
Route::get('tesoreria/get_cajas/{empresa_id}','Tesoreria\TesoreriaController@get_cajas');
Route::get('tesoreria/get_cuentas_bancarias/{empresa_id}','Tesoreria\TesoreriaController@get_cuentas_bancarias');



Route::get('tesoreria/get_cajas_to_select','Tesoreria\TesoreriaController@get_cajas_to_select');
Route::get('tesoreria/get_ctas_bancarias_to_select','Tesoreria\TesoreriaController@get_ctas_bancarias_to_select');



Route::get('teso_consultar_motivos', 'Tesoreria\TesoreriaController@consultar_motivos');
Route::get('tesoreria/ajax_get_motivos/{teso_tipo_motivo}', 'Tesoreria\TesoreriaController@ajax_get_motivos');

Route::get('tesoreria/recaudos_imprimir/{id}', 'Tesoreria\RecaudoController@imprimir');
Route::get('tesoreria/recaudos_anular/{id}', 'Tesoreria\RecaudoController@anular_recaudo');
Route::resource('tesoreria/recaudos', 'Tesoreria\RecaudoController');


//				PAGOS
Route::get('tesoreria/pagos/ajax_get_terceros/{id_tercero}', 'Tesoreria\PagoController@ajax_get_terceros');
Route::get('tesoreria/pagos/ajax_get_fila/{teso_tipo_motivo}', 'Tesoreria\PagoController@ajax_get_fila');
Route::get('tesoreria/pagos_imprimir/{id}', 'Tesoreria\PagoController@imprimir');
Route::get('teso_anular_pago/{id}', 'Tesoreria\PagoController@anular_pago');
Route::get('teso_pagos_duplicar_documento/{id}', 'Tesoreria\PagoController@duplicar_documento');
Route::resource('tesoreria/pagos', 'Tesoreria\PagoController');



Route::resource('tesoreria/arqueo_caja', 'Tesoreria\ArqueoCajaController');
Route::get('tesoreria/imprimir/{id}', 'Tesoreria\ArqueoCajaController@imprimir');

//TRASLADOS
Route::resource('tesoreria/traslado_efectivo', 'Tesoreria\TrasladoEfectivosController');
Route::get('tesoreria/traslado_efectivo/prueba/ajax_get_fila', 'Tesoreria\TrasladoEfectivosController@ajax_get_fila');
Route::get('tesoreria/traslado_efectivo/anular/{id}', 'Tesoreria\TrasladoEfectivosController@anular_traslado');
Route::get('tesoreria/traslado_efectivo/traslado/imprimir/{id}', 'Tesoreria\TrasladoEfectivosController@imprimir');

// RECAUDOS DE CXC
Route::get('tesoreria/get_documentos_pendientes_cxc', 'Tesoreria\RecaudoCxcController@get_documentos_pendientes_cxc');
// Anular
Route::get('teso_anular_recaudo_cxc/{id}', 'Tesoreria\RecaudoCxcController@anular_recaudo_cxc');

Route::get('tesoreria_recaudos_cxc_imprimir/{id}', 'Tesoreria\RecaudoCxcController@imprimir');
Route::resource('tesoreria/recaudos_cxc', 'Tesoreria\RecaudoCxcController');

// CONTROL DE CHEQUES
Route::get('get_cheques_recibidos/{teso_medio_recaudo_id}', 'Tesoreria\ControlChequeController@cheques_recibidos');
Route::get('teso_get_formulario_control_cheques/{teso_medio_recaudo_id}', 'Tesoreria\ControlChequeController@get_formulario_control_cheques');


// PAGOS DE CXP
Route::get('tesoreria/get_documentos_pendientes_cxp', 'Tesoreria\PagoCxpController@get_documentos_pendientes_cxp');
// Anular
Route::get('teso_anular_pago_cxp/{id}', 'Tesoreria\PagoCxpController@anular_pago_cxp');
Route::get('tesoreria_pagos_cxp_imprimir/{id}', 'Tesoreria\PagoCxpController@imprimir');
Route::resource('tesoreria/pagos_cxp', 'Tesoreria\PagoCxpController');


Route::resource('tesoreria', 'Tesoreria\TesoreriaController', ['except' => ['show']]);


// RECIBOS DE CAJA Y COMPROBANTES DE EGRESO (SIN CONTABILIZACION)
Route::get('teso_comprobante_egreso_show/{id}', 'Tesoreria\ComprobanteEgresoController@show');
Route::get('teso_comprobante_egreso_imprimir/{id}', 'Tesoreria\ComprobanteEgresoController@imprimir');
Route::get('teso_comprobante_egreso_anular/{id}', 'Tesoreria\ComprobanteEgresoController@anular');


Route::get('teso_recibo_caja_show/{id}', 'Tesoreria\ReciboCajaController@show');
Route::get('teso_recibo_caja_imprimir/{id}', 'Tesoreria\ReciboCajaController@imprimir');
Route::get('teso_recibo_caja_anular/{id}', 'Tesoreria\ReciboCajaController@anular');



// CONCILIACION BANCARIA
Route::post('tesoreria/procesa_archivo_plano_bancos', 'Tesoreria\ConciliacionBancariaController@procesa_archivo_plano_bancos');
Route::resource('tesoreria/conciliacion_bancaria', 'Tesoreria\ConciliacionBancariaController', ['except' => ['show']]);


/*
	REPORTES
*/
Route::get('tesoreria/get_tabla_movimiento','Tesoreria\ReporteController@get_tabla_movimiento');
Route::get('tesoreria/cartera_vencida_estudiantes','Tesoreria\ReporteController@cartera_vencida_estudiantes');
Route::get('tesoreria/flujo_de_efectivo','Tesoreria\ReporteController@flujo_de_efectivo');
Route::post('tesoreria/ajax_flujo_de_efectivo','Tesoreria\ReporteController@ajax_flujo_de_efectivo');

Route::post('teso_movimiento_caja_bancos','Tesoreria\ReporteController@teso_movimiento_caja_bancos');
Route::post('teso_resumen_movimiento_caja_bancos','Tesoreria\ReporteController@teso_resumen_movimiento_caja_bancos');

Route::get('tesoreria/reporte_cartera_por_curso','Tesoreria\ReporteController@reporte_cartera_por_curso');
Route::post('tesoreria/ajax_reporte_cartera_por_curso','Tesoreria\ReporteController@ajax_reporte_cartera_por_curso');
Route::get('teso_pdf_reporte_cartera_por_curso','Tesoreria\ReporteController@teso_pdf_reporte_cartera_por_curso');