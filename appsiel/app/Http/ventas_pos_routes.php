<?php

Route::resource('ventas_pos', 'VentasPos\AplicacionController');

Route::get('pos_factura_imprimir/{doc_encabezado_id}', 'VentasPos\FacturaPosController@imprimir');
Route::resource('pos_factura', 'VentasPos\FacturaPosController');
