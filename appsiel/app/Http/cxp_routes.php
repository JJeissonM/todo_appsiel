<?php


Route::get('cxp/get_cartera_tercero/{core_tercero_id}/{fecha}', 'CxP\DocCruceController@get_cartera_tercero');

Route::get('doc_cruce_cxp_imprimir/{id_fila}', 'CxP\DocCruceController@imprimir');
Route::get('doc_cruce_cxp_anular/{id}', 'CxP\DocCruceController@anular_doc_cruce');
Route::resource('doc_cruce_cxp', 'CxP\DocCruceController');

// Eliminar

// REPORTES
