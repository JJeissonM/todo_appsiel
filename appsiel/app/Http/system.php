<?php 


// ACTIVIDADES ESCOLARES

use Illuminate\Support\Facades\Route;

Route::get('sys_send_printing_to_server', 'System\PrintingServerController@send_printing_to_server');
