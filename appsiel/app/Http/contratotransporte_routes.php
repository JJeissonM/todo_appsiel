<?php

//      CONTRATO DE TRANSPORTE (FORMATO UNICO DE EXTRACTO DE CONTRATO DE TRANSPORTE)

Route::resource('cte_contratos', 'ContratoTransporte\ContratoTransporteController');

Route::resource('cte_plantillas', 'ContratoTransporte\ContratoTransporteController');

Route::resource('contratos_transporte', 'ContratoTransporte\ContratoTransporteController');

Route::get('cte_vehiculos/{id}/show', 'ContratoTransporte\VehiculoController@show')->name('cte_vehiculo.show');
Route::get('cte_anioperiodos/{id}/show', 'ContratoTransporte\AnioperiodoController@show')->name('cte_anioperiodo.show');
Route::get('cte_documentos_vehiculo/{id}/show', 'ContratoTransporte\VehiculoController@showDocuments')->name('cte_vehiculo.showDocuments');
