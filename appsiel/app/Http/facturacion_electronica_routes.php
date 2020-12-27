<?php


Route::get('consultar_documentos_emitidos', 'FacturacionElectronica\AplicacionController@consultar_documentos_emitidos');
Route::resource('facturacion_electronica', 'FacturacionElectronica\AplicacionController');


Route::get('fe_factura_enviar/{id}', 'FacturacionElectronica\FacturaController@enviar_factura_electronica');
Route::resource('fe_factura', 'FacturacionElectronica\FacturaController');


Route::get('fe_nota_credito_enviar/{id}', 'FacturacionElectronica\NotaCreditoController@enviar');
Route::resource('fe_nota_credito', 'FacturacionElectronica\NotaCreditoController');


Route::get('fe_nota_debito_enviar/{id}', 'FacturacionElectronica\NotaDebitoController@enviar');
Route::resource('fe_nota_debito', 'FacturacionElectronica\NotaDebitoController');
