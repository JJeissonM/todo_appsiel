<?php 

use Illuminate\Support\Facades\Route;

// Registros de documentos de nómina
Route::get('nomina/crear_registros', 'Nomina\RegistrosDocumentosController@crear_registros1');
Route::post('nomina/crear_registros2', 'Nomina\RegistrosDocumentosController@crear_registros2');

Route::resource('nom_registros_documentos', 'Nomina\RegistrosDocumentosController');


//  NOMINA ELECTRONICA
Route::resource('nom_electronica', 'Nomina\NominaElectronicaController');
Route::post('nom_electronica_generar_doc_soporte', 'Nomina\NominaElectronicaController@generar_doc_soporte');
Route::get('nom_electronica_enviar_documentos/{arr_ids}', 'Nomina\NominaElectronicaController@enviar_documentos');


Route::get('nom_eliminar_asignacion/registro_modelo_hijo_id/{registro_modelo_hijo_id}/registro_modelo_padre_id/{registro_modelo_padre_id}/id_app/{id_app}/id_modelo_padre/{id_modelo_padre}', 'Nomina\NominaController@eliminar_asignacion');
Route::post('nom_guardar_asignacion', 'Nomina\NominaController@guardar_asignacion');


// Documentos de nómina
Route::get('nomina/liquidacion/{id}', 'Nomina\NominaController@liquidacion');
Route::get('nomina/liquidacion_sp/{id}', 'Nomina\NominaController@liquidacion_sp');
Route::get('nomina/retirar_liquidacion/{id}', 'Nomina\NominaController@retirar_liquidacion');
Route::get('nomina_print/{id}', 'Nomina\NominaController@nomina_print');

// 			LIQUIDACIONES INDIVIDUALES
Route::get('nom_liquidar_prima_antiguedad/{nom_doc_encabezado_id}', 'Nomina\LiquidacionPorModosController@liquidar_prima_antiguedad');
Route::get('nom_retirar_prima_antiguedad/{nom_doc_encabezado_id}', 'Nomina\LiquidacionPorModosController@retirar_prima_antiguedad');


// 			TRANSACCIONES VARIAS 
Route::get('get_datos_contrato/{contrato_id}', 'Nomina\NominaController@get_datos_contrato');
Route::get('get_fecha_final_vacaciones/{grupo_empleado_id}/{fecha_inicial_tnl}/{cantidad_dias_tomados}/{dias_compensados}', 'Nomina\PrestacionesSocialesController@get_fecha_final_vacaciones');

Route::get('validar_fecha_otras_novedades/{fecha_inicial_tnl}/{fecha_final_tnl}/{contrato_id}/{novedad_id}', 'Nomina\NovedadesTnlController@validar_fecha_otras_novedades');

Route::get('nom_get_options_incapacidades_anteriores/{fecha_inicial_tnl}/{fecha_final_tnl}/{contrato_id}/{novedad_id}', 'Nomina\NovedadesTnlController@get_options_incapacidades_anteriores');


// LIQUIDACION DE PRESTACIONES SOCIALES
Route::post('nom_liquidar_prestaciones_sociales', 'Nomina\PrestacionesSocialesController@liquidacion');
Route::get('nom_retirar_prestaciones_sociales/{doc_encabezado_id}/{prestaciones}', 'Nomina\PrestacionesSocialesController@retirar_liquidacion');
Route::get('nom_prestaciones_liquidadas_show/{registro_id}', 'Nomina\PrestacionesSocialesController@prestaciones_liquidadas_show');
Route::get('nom_pdf_prestaciones_liquidadas/{registro_id}', 'Nomina\PrestacionesSocialesController@pdf_prestaciones_liquidadas');

// 		CONSOLIDADO DE PRESTACIONES
Route::post('nom_consolidar_prestaciones', 'Nomina\ConsolidadoPrestacionesController@consolidar_prestaciones');
Route::get('nom_retirar_consolidado_prestaciones/{fecha_final_promedios}', 'Nomina\ConsolidadoPrestacionesController@retirar_consolidado_prestaciones');
Route::get('nom_consolidado_empleado/{contrato_id}', 'Nomina\ConsolidadoPrestacionesController@show');


// RETEFUENTE
Route::post('nom_liquidar_retefuente', 'Nomina\RetefuenteController@liquidacion');
Route::get('nom_retirar_retefuente/{doc_encabezado_id}', 'Nomina\RetefuenteController@retirar_liquidacion');

Route::post('nom_calcular_porcentaje_fijo_retefuente', 'Nomina\RetefuenteController@calcular_porcentaje_fijo_retefuente');


// 				CONTABILIZACIÓN

// CONTAB. PLANILLA INTEGRADA
Route::post('nom_contabilizar_pila', 'Nomina\ContabilizacionPilaController@contabilizar');
Route::get('nom_retirar_contabilizacion_pila/{fecha_final_promedios}', 'Nomina\ContabilizacionPilaController@retirar');

// CONTAB. PROVISION NOMINA
Route::post('nom_contabilizar_provision_nomina', 'Nomina\ContabilizacionProvisionController@contabilizar');
Route::get('nom_retirar_contabilizacion_provision_nomina/{fecha_final_promedios}', 'Nomina\ContabilizacionProvisionController@retirar');


// CONTAB. DOCUMENTO DE NOMINA
Route::post('nom_contabilizar_documento_nomina', 'Nomina\ContabilizacionDocumentoController@contabilizar');
Route::get('nom_retirar_contabilizacion_documento_nomina/{doc_encabezado_id}', 'Nomina\ContabilizacionDocumentoController@retirar');


//		ORDENES DE TRABAJO
Route::get('nom_get_tabla_empleados_ingreso_registros', 'Nomina\OrdenDeTrabajoController@get_tabla_empleados_ingreso_registros');
Route::get('nom_ordenes_trabajo_imprimir/{orden_trabajo_id}', 'Nomina\OrdenDeTrabajoController@imprimir');
Route::get('nom_ordenes_trabajo_anular/{orden_trabajo_id}', 'Nomina\OrdenDeTrabajoController@anular');


Route::get('nom_ordenes_trabajo_cambiar_cantidad_horas_empleados/{orden_trabajo_id}/{nom_concepto_id}/{nom_contrato_id}/{nueva_cantidad_horas}','Nomina\OrdenDeTrabajoController@cambiar_cantidad_horas_empleados');
Route::get('nom_ordenes_trabajo_cambiar_valor_por_hora_empleados/{orden_trabajo_id}/{nom_concepto_id}/{nom_contrato_id}/{nuevo_valor_por_hora}','Nomina\OrdenDeTrabajoController@cambiar_valor_por_hora_empleados');

Route::get('nom_ordenes_trabajo_cambiar_cantidad_items/{orden_trabajo_id}/{inv_producto_id}/{nueva_cantidad}','Nomina\OrdenDeTrabajoController@cambiar_cantidad_items');

Route::resource('nom_ordenes_trabajo', 'Nomina\OrdenDeTrabajoController');



// INFORMES Y LISTADOS
Route::post('nom_resumen_liquidaciones','Nomina\ReporteController@resumen_liquidaciones');
Route::post('nom_consolidado_prestaciones_sociales','Nomina\ReporteController@consolidado_prestaciones_sociales');
Route::post('nom_listado_acumulados','Nomina\ReporteController@listado_acumulados');
Route::post('nom_libro_fiscal_vacaciones','Nomina\ReporteController@libro_fiscal_vacaciones');
Route::post('nom_resumen_x_entidad_empleado','Nomina\ReporteController@resumen_x_entidad_empleado');
Route::post('nom_listado_aportes_pila','Nomina\ReporteController@listado_aportes_pila');
Route::post('nom_costos_por_proyectos','Nomina\ReporteController@costos_por_proyectos');

Route::get('nomina/reportes','Nomina\ReporteController@reportes');

Route::get('nomina/reporte_desprendibles_de_pago','Nomina\ReporteController@reporte_desprendibles_de_pago');
Route::post('nomina/ajax_reporte_desprendibles_de_pago','Nomina\ReporteController@ajax_reporte_desprendibles_de_pago');
Route::get('nomina_pdf_reporte_desprendibles_de_pago','Nomina\ReporteController@nomina_pdf_reporte_desprendibles_de_pago');
Route::post( 'nom_enviar_por_email_desprendibles_de_pago','Nomina\ReporteController@enviar_por_email_desprendibles_de_pago');

Route::get('nom_certificado_ingresos_y_retenciones','Nomina\ReporteController@certificado_ingresos_y_retenciones');
Route::post('nom_ajax_certificado_ingresos_y_retenciones','Nomina\ReporteController@ajax_certificado_ingresos_y_retenciones');
Route::get('nomina_pdf_certificado_ingresos_y_retenciones','Nomina\ReporteController@pdf_certificado_ingresos_y_retenciones');

Route::get('nom_formato_2276_informacion_exogena','Nomina\ReporteController@formato_2276_informacion_exogena');
Route::post('nom_ajax_formato_2276_informacion_exogena','Nomina\ReporteController@ajax_formato_2276_informacion_exogena');

// Listado de vacaciones pendientes
Route::get('nom_listado_vacaciones_pendientes','Nomina\ReporteController@listado_vacaciones_pendientes');
Route::post('nom_ajax_listado_vacaciones_pendientes','Nomina\ReporteController@ajax_listado_vacaciones_pendientes');
Route::get('nomina_pdf_listado_vacaciones_pendientes','Nomina\ReporteController@pdf_listado_vacaciones_pendientes');

//PROCESOS
Route::post('nom_procesar_archivo_plano','Nomina\ProcesosController@procesar_archivo_plano');
Route::post('nom_almacenar_registros_via_interface','Nomina\ProcesosController@almacenar_registros_via_interface');

Route::post('nom_calcular_acumulados_seguridad_social_parafiscales','Nomina\ProcesosController@calcular_acumulados_seguridad_social_parafiscales');
Route::post('nom_almacenar_acumulados_seguridad_social_parafiscales','Nomina\ProcesosController@almacenar_acumulados_seguridad_social_parafiscales');
Route::post('nom_generar_archivo_consignar_cesantias','Nomina\ProcesosController@generar_archivo_consignar_cesantias');


// PLANILLA INTEGRADA
//Route::post('nom_generar_planilla_integrada/{planilla_id}','Nomina\PlanillaIntegradaController@generar_planilla_integrada');
Route::get('nom_pila_liquidar_planilla/{planilla_id}', 'Nomina\PlanillaIntegradaController@liquidar_planilla');
Route::get('nom_pila_show/{planilla_generada_id}', 'Nomina\PlanillaIntegradaController@show');
Route::get('nom_pila_catalogos/{permiso_padre_id}', 'Nomina\PlanillaIntegradaController@catalogos');
Route::get('nom_pila_descargar_archivo_plano/{planilla_id}', 'Nomina\PlanillaIntegradaController@descargar_archivo_plano');
Route::get('nom_pila_eliminar_planilla/{planilla_id}', 'Nomina\PlanillaIntegradaController@eliminar_planilla');


Route::resource('nomina', 'Nomina\NominaController');