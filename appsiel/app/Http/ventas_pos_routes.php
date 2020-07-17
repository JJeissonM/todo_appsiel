<?php

Route::resource('ventas_pos', 'VentasPos\AplicacionController');

Route::get('pos_factura_imprimir/{doc_encabezado_id}', 'VentasPos\FacturaPosController@imprimir');
Route::get('pos_factura_anular/{doc_encabezado_id}', 'VentasPos\FacturaPosController@anular_factura_pos');
Route::get('pos_factura_acumular/{pdv_id}', 'VentasPos\FacturaPosController@acumular');

Route::resource('pos_factura', 'VentasPos\FacturaPosController');
