<?php

Route::resource('facturacion_electronica', 'FacturacionElectronica\AplicacionController');


Route::get('fe_factura_enviar/{id}', 'FacturacionElectronica\FacturaController@enviar_factura_electronica');
Route::resource('fe_factura', 'FacturacionElectronica\FacturaController');
