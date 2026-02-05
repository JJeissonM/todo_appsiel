<?php

use Illuminate\Support\Facades\Route;

Route::get('siesa/tabla_descuentos', 'Siesa\\DescuentosController@tabla_descuentos');
Route::get('siesa/tabla_descuentos/excel', 'Siesa\\DescuentosController@tabla_descuentos_excel');
