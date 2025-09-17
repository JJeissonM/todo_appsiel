<?php

use Illuminate\Support\Facades\Route;

Route::get('sys_send_printing_to_server', 'System\PrintingServerController@send_printing_to_server');

Route::get('sys_test_printing_form', 'System\PrintingServerController@test_printing_form');
Route::get('sys_test_print_example_rawbt', 'System\PrintingServerController@test_print_example_rawbt');


Route::get('sys_printing_cut_paper', 'System\PrintingServerController@cut_paper');
Route::get('sys_printing_feed_paper/{line_numbers?}', 'System\PrintingServerController@feed_paper');
Route::get('sys_printing_feed_reverse_paper/{line_numbers?}', 'System\PrintingServerController@feed_reverse_paper');


Route::get('sys_model_testing', 'Sistema\ModeloController@testing');
