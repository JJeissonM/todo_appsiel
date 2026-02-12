<?php

use Illuminate\Support\Facades\Route;

Route::get('siesa/tabla_descuentos', 'Siesa\\DescuentosController@tabla_descuentos');
Route::get('siesa/tabla_descuentos/excel', 'Siesa\\DescuentosController@tabla_descuentos_excel');
Route::get('siesa/tabla_proveedores_enterprise', 'Siesa\\ProveedoresEnterpriseController@tabla');
Route::get('siesa/tabla_proveedores_enterprise/excel', 'Siesa\\ProveedoresEnterpriseController@tabla_excel');
Route::get('siesa/tabla_proveedores_impuestos_retenciones', 'Siesa\\ProveedoresImpuestosRetencionesController@tabla');
Route::get('siesa/tabla_proveedores_impuestos_retenciones/excel', 'Siesa\\ProveedoresImpuestosRetencionesController@tabla_excel');

