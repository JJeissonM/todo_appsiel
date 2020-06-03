<?php

//      CONTRATO DE TRANSPORTE (FORMATO UNICO DE EXTRACTO DE CONTRATO DE TRANSPORTE)

Route::resource('cte_contratos', 'ContratoTransporte\ContratoTransporteController');

Route::resource('cte_plantillas', 'ContratoTransporte\ContratoTransporteController');

Route::resource('contratos_transporte', 'ContratoTransporte\ContratoTransporteController');


Route::get('cte_contratos/{id}/show', 'ContratoTransporte\ContratoTransporteController@show');
Route::get('cte_contratos/{id}/imprimir', 'ContratoTransporte\ContratoTransporteController@imprimir');
Route::get('cte_contratos/{id}/gestion/grupousuarios', 'ContratoTransporte\ContratoTransporteController@grupousuarios');
Route::get('cte_contratos/gestion/grupousuarios/{id}/delete', 'ContratoTransporte\ContratoTransporteController@deletegrupousuario')->name('cte_contratos.deletegu');
Route::post('cte_contratos/gestion/grupousuarios/store', 'ContratoTransporte\ContratoTransporteController@storegrupousuario')->name('cte_contratos.storegu');
Route::get('cte_contratos_propietarios', 'ContratoTransporte\ContratoTransporteController@miscontratos');
Route::get('cte_contratos/{id}/planillas/{source}/index', 'ContratoTransporte\ContratoTransporteController@planillaindex')->name('cte_contratos.planillaindex');


//VEHICULOS
Route::get('cte_vehiculos/{id}/show', 'ContratoTransporte\VehiculoController@show')->name('cte_vehiculo.show');
Route::get('cte_documentos_vehiculo/{id}/show', 'ContratoTransporte\VehiculoController@showDocuments')->name('cte_vehiculo.showDocuments');

//CONDUCTORES
Route::get('cte_conductores/{id}/show', 'ContratoTransporte\ConductorController@show')->name('cte_conductor.show');
Route::get('cte_documentos_conductor/{id}/show', 'ContratoTransporte\ConductorController@showDocuments')->name('cte_conductor.showDocuments');

//AÑOS Y PERIODOS
Route::get('cte_anioperiodos/{id}/show', 'ContratoTransporte\AnioperiodoController@show')->name('cte_anioperiodo.show');

//PLANTILLAS
Route::get('cte_numeraltablas/{id}/show', 'ContratoTransporte\PlantillaController@show_numeraltabla')->name('cte_plantilla.show_numeraltabla');
Route::get('cte_plantillaarticulonumerals/{id}/show', 'ContratoTransporte\PlantillaController@show_plantillaarticulonumeral')->name('cte_plantilla.show_plantillaarticulonumeral');
Route::get('cte_plantillaarticulos/{id}/show', 'ContratoTransporte\PlantillaController@show_plantillaarticulo')->name('cte_plantilla.show_plantillaarticulo');
Route::get('cte_plantillas/{id}/show', 'ContratoTransporte\PlantillaController@show_plantilla')->name('cte_plantilla.show_plantilla');

//MANTENIMIENTOS
Route::get('cte_mantenimientos', 'ContratoTransporte\MantenimientoController@index')->name('mantenimiento.index');
Route::get('cte_mantenimientos/{vehiculo_id}/continuar', 'ContratoTransporte\MantenimientoController@continuar')->name('mantenimiento.continuar');
Route::get('cte_mantenimientos/{vehiculo_id}/{anioperiodo_id}/create', 'ContratoTransporte\MantenimientoController@create')->name('mantenimiento.create');
Route::post('cte_mantenimientos/store', 'ContratoTransporte\MantenimientoController@store')->name('mantenimiento.store');
Route::get('cte_mantenimientos/mantenimientos/reportes/{id}/delete', 'ContratoTransporte\MantenimientoController@deletereporte')->name('mantenimiento.deletereporte');
Route::get('cte_mantenimientos/mantenimientos/observaciones/{id}/delete', 'ContratoTransporte\MantenimientoController@deleteobs')->name('mantenimiento.deleteobs');
Route::post('cte_mantenimientos/reportes/store', 'ContratoTransporte\MantenimientoController@storemant')->name('mantenimiento.storemant');
Route::post('cte_mantenimientos/observaciones/store', 'ContratoTransporte\MantenimientoController@storeobs')->name('mantenimiento.storeobs');
