<?php

use Illuminate\Support\Facades\Route;

Route::get('sys_send_printing_to_server', 'System\PrintingServerController@send_printing_to_server');

Route::get('sys_test_printing_form', 'System\PrintingServerController@test_printing_form');
Route::get('sys_test_print_example_rawbt', 'System\PrintingServerController@test_print_example_rawbt');
