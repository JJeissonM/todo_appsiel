<?php

use Illuminate\Support\Facades\Route;

Route::get('fe_consultar_documentos_emitidos/{doc_encabezado_id}/{tipo_operacion}', 'FacturacionElectronica\AplicacionController@consultar_documentos_emitidos');
Route::resource('facturacion_electronica', 'FacturacionElectronica\AplicacionController');


Route::get('fe_factura_enviar/{id}', 'FacturacionElectronica\FacturaController@enviar_factura_electronica');

Route::get('fe_actualizar_fecha_y_enviar/{id}', 'FacturacionElectronica\FacturaController@actualizar_fecha_y_enviar');

Route::get('fe_envio_masivo/{doc_encabezado_id}/{cambiar_fecha}', 'FacturacionElectronica\FacturaController@envio_masivo');

Route::get('fe_convertir_en_factura_electronica/{vtas_doc_encabezado_id}', 'FacturacionElectronica\FacturaController@convertir_en_factura_electronica');
Route::resource('fe_factura', 'FacturacionElectronica\FacturaController');


Route::get('fe_nota_credito_enviar/{id}', 'FacturacionElectronica\NotaCreditoController@enviar');
Route::resource('fe_nota_credito', 'FacturacionElectronica\NotaCreditoController');


Route::get('fe_nota_debito_enviar/{id}', 'FacturacionElectronica\NotaDebitoController@enviar');
Route::resource('fe_nota_debito', 'FacturacionElectronica\NotaDebitoController');


// Doc. Soporte Compras
Route::get('fe_doc_soporte_enviar/{id}', 'FacturacionElectronica\DocSoporteController@enviar_doc_soporte');
