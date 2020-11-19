<?php

Route::resource('facturacion_electronica', 'FacturacionElectronica\AplicacionController');


Route::get('fe_factura_enviar/{id}', 'FacturacionElectronica\FacturaController@enviar_factura_electronica');
Route::resource('fe_factura', 'FacturacionElectronica\FacturaController');


Route::get('fe_nota_credito_enviar/{id}', 'FacturacionElectronica\NotaCreditoController@enviar');
Route::resource('fe_nota_credito', 'FacturacionElectronica\NotaCreditoController');


Route::resource('fe_nota_debito', 'FacturacionElectronica\NotaDebitoController');
