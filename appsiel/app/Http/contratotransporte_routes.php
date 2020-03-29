<?php

//      CONTRATO DE TRANSPORTE (FORMATO UNICO DE EXTRACTO DE CONTRATO DE TRANSPORTE)

Route::resource('cte_contratos', 'ContratoTransporte\ContratoTransporteController');

Route::resource('cte_plantillas', 'ContratoTransporte\ContratoTransporteController');

Route::resource('contratos_transporte', 'ContratoTransporte\ContratoTransporteController');

//VEHICULOS
Route::get('cte_vehiculos/{id}/show', 'ContratoTransporte\VehiculoController@show')->name('cte_vehiculo.show');
Route::get('cte_documentos_vehiculo/{id}/show', 'ContratoTransporte\VehiculoController@showDocuments')->name('cte_vehiculo.showDocuments');
//CONDUCTORES
Route::get('cte_conductores/{id}/show', 'ContratoTransporte\ConductorController@show')->name('cte_conductor.show');
Route::get('cte_documentos_conductor/{id}/show', 'ContratoTransporte\ConductorController@showDocuments')->name('cte_conductor.showDocuments');
//AÑOS Y PERIODOS
Route::get('cte_anioperiodos/{id}/show', 'ContratoTransporte\AnioperiodoController@show')->name('cte_anioperiodo.show');
