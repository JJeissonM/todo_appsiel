<?php 

// Registros de documentos de nómina
Route::get('nomina/crear_registros', 'Nomina\RegistrosDocumentosController@crear_registros1');
Route::post('nomina/crear_registros2', 'Nomina\RegistrosDocumentosController@crear_registros2');

Route::resource('nom_registros_documentos', 'Nomina\RegistrosDocumentosController');


Route::get('nom_eliminar_asignacion/registro_modelo_hijo_id/{registro_modelo_hijo_id}/registro_modelo_padre_id/{registro_modelo_padre_id}/id_app/{id_app}/id_modelo_padre/{id_modelo_padre}', 'Nomina\NominaController@eliminar_asignacion');
Route::post('nom_guardar_asignacion', 'Nomina\NominaController@guardar_asignacion');


// Documentos de nómina
Route::get('nomina/liquidacion/{id}', 'Nomina\NominaController@liquidacion');
Route::get('nomina/retirar_liquidacion/{id}', 'Nomina\NominaController@retirar_liquidacion');
Route::get('nomina_print/{id}', 'Nomina\NominaController@nomina_print');


Route::get('get_datos_contrato/{contrato_id}', 'Nomina\NominaController@get_datos_contrato');

Route::get('validar_fecha_otras_novedades/{fecha_inicial_tnl}/{fecha_final_tnl}/{contrato_id}/{novedad_id}', 'Nomina\NovedadesTnlController@validar_fecha_otras_novedades');


// LIQUIDACION DE PRESTACIONES SOCIALES
Route::post('nom_liquidar_prestaciones_sociales', 'Nomina\PrestacionesSocialesController@liquidacion');


// INFORMES Y LISTADOS
Route::post('nom_listado_acumulados','Nomina\ReporteController@listado_acumulados');
Route::post('nom_libro_fiscal_vacaciones','Nomina\ReporteController@libro_fiscal_vacaciones');
Route::post('nom_resumen_x_entidad_empleado','Nomina\ReporteController@resumen_x_entidad_empleado');
Route::post('nom_provisiones_x_entidad_empleado','Nomina\ReporteController@provisiones_x_entidad_empleado');
Route::post('nom_listado_aportes_parafiscales','Nomina\ReporteController@listado_aportes_parafiscales');

Route::get('nomina/reportes','Nomina\ReporteController@reportes');

Route::get('nomina/reporte_desprendibles_de_pago','Nomina\ReporteController@reporte_desprendibles_de_pago');
Route::post('nomina/ajax_reporte_desprendibles_de_pago','Nomina\ReporteController@ajax_reporte_desprendibles_de_pago');
Route::get('nomina_pdf_reporte_desprendibles_de_pago','Nomina\ReporteController@nomina_pdf_reporte_desprendibles_de_pago');

//PROCESOS
Route::post('nom_procesar_archivo_plano','Nomina\ProcesosController@procesar_archivo_plano');
Route::post('nom_almacenar_registros_via_interface','Nomina\ProcesosController@almacenar_registros_via_interface');

Route::post('nom_calcular_acumulados_seguridad_social_parafiscales','Nomina\ProcesosController@calcular_acumulados_seguridad_social_parafiscales');
Route::post('nom_almacenar_acumulados_seguridad_social_parafiscales','Nomina\ProcesosController@almacenar_acumulados_seguridad_social_parafiscales');


Route::resource('nomina', 'Nomina\NominaController');