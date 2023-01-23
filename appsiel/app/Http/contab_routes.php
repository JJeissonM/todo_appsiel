<?php 

use Illuminate\Support\Facades\Route;

Route::get('contab_consultar_cuentas', 'Contabilidad\ContabilidadController@consultar_cuentas');

Route::get('contab_get_formulario_cxc', 'Contabilidad\ContabilidadController@get_formulario_cxc');
Route::get('contab_get_formulario_cxp', 'Contabilidad\ContabilidadController@get_formulario_cxp');

Route::get('contabilidad_print/{id_transaccion}','Contabilidad\ContabilidadController@imprimir');
Route::get('contab_get_fila/{id_fila}','Contabilidad\ContabilidadController@contab_get_fila');
Route::get('contab_anular_documento/{id_fila}','Contabilidad\ContabilidadController@contab_anular_documento');


Route::get('contab_duplicar_documento/{doc_encabezado_id}','Contabilidad\ContabilidadController@duplicar_documento');

Route::resource('contabilidad','Contabilidad\ContabilidadController');


Route::get('contabilidad/mov_print/{id_transaccion}','Contabilidad\ContabMovimientoController@imprimir');
Route::resource('contabilidad/mov','Contabilidad\ContabMovimientoController');


Route::get('contab_get_grupos_cuentas/{clase_id}','Contabilidad\ContabilidadController@contab_get_grupos_cuentas');

// REPORTES
Route::get('contab_reporte_prueba', 'Contabilidad\ContabReportesController@reporte_prueba');

Route::post('contab_ajax_auxiliar_por_cuenta', 'Contabilidad\ContabReportesController@contab_ajax_auxiliar_por_cuenta');
Route::get('contab_auxiliar_por_cuenta','Contabilidad\ContabReportesController@contab_auxiliar_por_cuenta');
Route::get('contab_pdf_estados_de_cuentas', 'Contabilidad\ContabReportesController@contab_pdf_estados_de_cuentas');
Route::get('contab_balance_comprobacion','Contabilidad\ContabReportesController@balance_comprobacion');

Route::post('contab_ajax_generacion_eeff', 'Contabilidad\ContabReportesController@contab_ajax_generacion_eeff');
Route::get('contab_pdf_eeff', 'Contabilidad\ContabReportesController@contab_pdf_eeff');
Route::get('contab_generacion_eeff','Contabilidad\ContabReportesController@generacion_eeff');

Route::post('contab_taxes_general_report', 'Contabilidad\ContabReportesController@taxes_general_report');Route::post('contab_tax_reporting_by_third_parties', 'Contabilidad\ContabReportesController@tax_reporting_by_third_parties');

// Reportes del Menú Automático
Route::post('contab_cuadre_contabilidad_vs_tesoreria', 'Contabilidad\ContabReportesController@cuadre_contabilidad_vs_tesoreria');
Route::post('contab_lista_documentos_descuadrados', 'Contabilidad\ContabReportesController@lista_documentos_descuadrados');
Route::post('contab_impuestos', 'Contabilidad\ContabReportesController@impuestos');
Route::post('contab_ajax_balance_comprobacion', 'Contabilidad\ContabReportesController@contab_ajax_balance_comprobacion');

// OTRAS TAREAS
Route::get('reasignar_grupos_cuentas_form','Contabilidad\ContabReportesController@reasignar_grupos_cuentas_form');
Route::get('reasignar_grupos_cuentas_save/{cuenta_id}/{grupo_id}','Contabilidad\ContabReportesController@reasignar_grupos_cuentas_save');

// PROCESOS
Route::post('contab_generar_listado_cierre_ejercicio','Contabilidad\ProcesosController@generar_listado_cierre_ejercicio');
Route::post('contab_crear_nota_cierre_ejercicio','Contabilidad\ProcesosController@crear_nota_cierre_ejercicio');

Route::get('corregir_signo_a_movimientos','Contabilidad\ContabilidadController@corregir_signo_a_movimientos');
Route::get('contab/proceso_1','Contabilidad\ContabReportesController@proceso_1');
