<?php 

use Illuminate\Support\Facades\Route;
//      C X C 


Route::get('cxc_modificar_doc_encabezado/{id_fila}', 'CxC\CxCController@form_modificar_doc_encabezado');

Route::put('cxc_guardar_doc_encabezado/{id}', 'CxC\CxCController@guardar_doc_encabezado');

Route::get('cxc/imprimir_cartera_una_edad/{min}/{max}/{empresa_id}', 'CxC\CxCController@imprimir_cartera_una_edad');



Route::get('cxc/form_reimprimir', 'CxC\CxCController@form_reimprimir_cxc');
Route::post('cxc/ajax_reimprimir_cxc', 'CxC\CxCController@ajax_reimprimir_cxc');

Route::get('cxc_print/{id_fila}', 'CxC\CxCController@cxc_print');

Route::get('cxc_enviar_por_email/{id_fila}', 'CxC\CxCController@cxc_enviar_por_email');

Route::get('cxc/imprimir_lote/{empresa_id}/{core_tipo_doc_app_id}/{consec_desde}/{consec_hasta}', 'CxC\CxCController@imprimir_lote');

Route::get('cxc/enviar_email_lote/{empresa_id}/{core_tipo_doc_app_id}/{consec_desde}/{consec_hasta}', 'CxC\CxCController@enviar_email_lote');

Route::get('cxc_get_fila/{id_fila}','CxC\CxCController@cxc_get_fila');


Route::get('cxc/get_cartera_inmueble/{ph_propieda_id}/{fecha}', 'CxC\CxCController@get_cartera_inmueble');
Route::get('cxc/estados_de_cuentas', 'CxC\CxCController@estados_de_cuentas');
Route::post('cxc_ajax_estados_de_cuentas', 'CxC\CxCController@cxc_ajax_estados_de_cuentas');
Route::get('cxc_pdf_estados_de_cuentas', 'CxC\CxCController@cxc_pdf_estados_de_cuentas');

Route::get('cxc/eliminar/{id}', 'CxC\CxCController@eliminar_cxc');
Route::resource('cxc', 'CxC\CxCController');

Route::get('cxc/get_cartera_tercero/{tercero_id}/{fecha_doc}', 'CxC\DocCruceController@get_cartera_tercero');

Route::get('get_docs_cruce_tercero/{core_tercero_id}', 'CxC\DocCruceController@get_docs_cruce_tercero');

Route::get('doc_cruce_imprimir/{id_fila}', 'CxC\DocCruceController@imprimir');
Route::get('doc_cruce_cxc_anular/{id}', 'CxC\DocCruceController@anular_doc_cruce');
Route::resource('doc_cruce', 'CxC\DocCruceController');

Route::get('cancelacion_anticipo_print/{id_fila}', 'CxC\DocCancelacionController@cancelacion_anticipo_print');
Route::get('cancelacion_anticipo/eliminar/{id}', 'CxC\DocCancelacionController@eliminar_cancelacion_anticipo');
Route::resource('cancelacion_anticipo', 'CxC\DocCancelacionController');


Route::get('cxc_calcular_intereses/', 'CxC\InteresesMoraController@calcular_intereses');
Route::post('cxc_ajax_calcular_intereses/', 'CxC\InteresesMoraController@cxc_ajax_calcular_intereses');


Route::get('cxc_causar_intereses/', 'CxC\InteresesMoraController@causar_intereses');
Route::post('cxc_ajax_causar_intereses/', 'CxC\InteresesMoraController@cxc_ajax_causar_intereses');

Route::get('cxc_eliminar_interes/{id}', 'CxC\InteresesMoraController@eliminar_interes');


// REPORTES
Route::post('cxc_documentos_pendientes', 'CxC\ReportesController@documentos_pendientes');
Route::post('cxc_estado_de_cuenta', 'CxC\ReportesController@estado_de_cuenta');

//              PROCESOS
Route::get('cxc_procesos_pruebas', 'CxC\ProcesosController@pruebas');
