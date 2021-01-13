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
Route::get('get_fecha_final_vacaciones/{grupo_empleado_id}/{fecha_inicial_tnl}/{cantidad_dias_tomados}/{dias_compensados}', 'Nomina\PrestacionesSocialesController@get_fecha_final_vacaciones');

Route::get('validar_fecha_otras_novedades/{fecha_inicial_tnl}/{fecha_final_tnl}/{contrato_id}/{novedad_id}', 'Nomina\NovedadesTnlController@validar_fecha_otras_novedades');


// LIQUIDACION DE PRESTACIONES SOCIALES
Route::post('nom_liquidar_prestaciones_sociales', 'Nomina\PrestacionesSocialesController@liquidacion');
Route::get('nom_retirar_prestaciones_sociales/{doc_encabezado_id}/{prestaciones}', 'Nomina\PrestacionesSocialesController@retirar_liquidacion');


// RETEFUENTE
Route::post('nom_liquidar_retefuente', 'Nomina\RetefuenteController@liquidacion');
Route::get('nom_retirar_retefuente/{doc_encabezado_id}', 'Nomina\RetefuenteController@retirar_liquidacion');


// INFORMES Y LISTADOS
Route::post('nom_resumen_liquidaciones','Nomina\ReporteController@resumen_liquidaciones');
Route::post('nom_consolidado_prestaciones_sociales','Nomina\ReporteController@consolidado_prestaciones_sociales');
Route::post('nom_listado_acumulados','Nomina\ReporteController@listado_acumulados');
Route::post('nom_libro_fiscal_vacaciones','Nomina\ReporteController@libro_fiscal_vacaciones');
Route::post('nom_resumen_x_entidad_empleado','Nomina\ReporteController@resumen_x_entidad_empleado');
Route::post('nom_listado_aportes_pila','Nomina\ReporteController@listado_aportes_pila');

Route::get('nomina/reportes','Nomina\ReporteController@reportes');

Route::get('nomina/reporte_desprendibles_de_pago','Nomina\ReporteController@reporte_desprendibles_de_pago');
Route::post('nomina/ajax_reporte_desprendibles_de_pago','Nomina\ReporteController@ajax_reporte_desprendibles_de_pago');
Route::get('nomina_pdf_reporte_desprendibles_de_pago','Nomina\ReporteController@nomina_pdf_reporte_desprendibles_de_pago');

//PROCESOS
Route::post('nom_procesar_archivo_plano','Nomina\ProcesosController@procesar_archivo_plano');
Route::post('nom_almacenar_registros_via_interface','Nomina\ProcesosController@almacenar_registros_via_interface');

Route::post('nom_calcular_acumulados_seguridad_social_parafiscales','Nomina\ProcesosController@calcular_acumulados_seguridad_social_parafiscales');
Route::post('nom_almacenar_acumulados_seguridad_social_parafiscales','Nomina\ProcesosController@almacenar_acumulados_seguridad_social_parafiscales');


// PLANILLA INTEGRADA
//Route::post('nom_generar_planilla_integrada/{planilla_id}','Nomina\PlanillaIntegradaController@generar_planilla_integrada');
Route::get('nom_pila_liquidar_planilla/{planilla_id}', 'Nomina\PlanillaIntegradaController@liquidar_planilla');
Route::get('nom_pila_show/{planilla_generada_id}', 'Nomina\PlanillaIntegradaController@show');
Route::get('nom_pila_catalogos/{permiso_padre_id}', 'Nomina\PlanillaIntegradaController@catalogos');
Route::get('nom_pila_descargar_archivo_plano/{planilla_id}', 'Nomina\PlanillaIntegradaController@descargar_archivo_plano');
Route::get('nom_pila_eliminar_planilla/{planilla_id}', 'Nomina\PlanillaIntegradaController@eliminar_planilla');



Route::resource('nomina', 'Nomina\NominaController');