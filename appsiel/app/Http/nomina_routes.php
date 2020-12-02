<?php 

Route::get('nomina/crear_registros', 'Nomina\NominaController@crear_registros1');
Route::post('nomina/crear_registros2', 'Nomina\NominaController@crear_registros2');

Route::get('nomina_print/{id}', 'Nomina\NominaController@nomina_print');

Route::get('nomina/liquidacion/{id}', 'Nomina\NominaController@liquidacion');
Route::get('nomina/retirar_liquidacion/{id}', 'Nomina\NominaController@retirar_liquidacion');


// INFORMES Y LISTADOS

Route::post('nom_listado_acumulados','Nomina\ReporteController@listado_acumulados');
Route::post('nom_libro_fiscal_vacaciones','Nomina\ReporteController@libro_fiscal_vacaciones');
Route::post('nom_provisiones_x_entidad_empleado','Nomina\ReporteController@provisiones_x_entidad_empleado');
Route::post('nom_listado_aportes_parafiscales','Nomina\ReporteController@listado_aportes_parafiscales');
Route::post('','Nomina\ReporteController@');
Route::post('','Nomina\ReporteController@');

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