<?php

Route::group(array('middleware' => array('auth'), 'prefix' => 'hotel'), function () {
    Route::get('/', 'Hotel\HotelDashboardController@index');

    Route::get('rooms', 'Hotel\HotelRoomController@index');
    Route::get('rooms/create', 'Hotel\HotelRoomController@create');
    Route::post('rooms', 'Hotel\HotelRoomController@store');
    Route::get('rooms/{id}', 'Hotel\HotelRoomController@show');
    Route::get('rooms/{id}/edit', 'Hotel\HotelRoomController@edit');
    Route::put('rooms/{id}', 'Hotel\HotelRoomController@update');
    Route::post('rooms/{id}/status', 'Hotel\HotelRoomController@changeStatus');
    Route::post('rooms/{id}/deactivate', 'Hotel\HotelRoomController@deactivate');

    Route::get('stays', 'Hotel\HotelStayController@index');
    Route::get('stays/active', 'Hotel\HotelStayController@active');
    Route::get('stays/check-in', 'Hotel\HotelStayController@createCheckIn');
    Route::post('stays/check-in', 'Hotel\HotelStayController@storeCheckIn');
    Route::get('stays/{id}', 'Hotel\HotelStayController@show');
    Route::post('stays/{id}/orders', 'Hotel\HotelStayController@createOrder');
    Route::post('stays/{id}/check-out', 'Hotel\HotelStayController@checkOut');
    Route::post('stays/{id}/cancel', 'Hotel\HotelStayController@cancel');
    Route::post('stays/{id}/guests', 'Hotel\HotelStayGuestController@store');
    Route::post('stays/{id}/guests/{guestId}/delete', 'Hotel\HotelStayGuestController@destroy');
    Route::post('reservations/{id}/cancel', 'Hotel\HotelReservationController@cancel');

    Route::get('orders/{id}', 'Hotel\HotelOrderController@show');
    Route::post('orders/{id}/save-lines', 'Hotel\HotelOrderController@saveLines');
    Route::post('orders/{id}/lines', 'Hotel\HotelOrderController@addLine');
    Route::post('orders/{id}/lines/{lineId}/update', 'Hotel\HotelOrderController@updateLine');
    Route::post('orders/{id}/lines/{lineId}/delete', 'Hotel\HotelOrderController@deleteLine');
    Route::post('orders/{id}/generate-standard-invoice', 'Hotel\HotelOrderController@generateStandardInvoice');
    Route::post('orders/{id}/generate-pos-invoice', 'Hotel\HotelOrderController@generatePosInvoice');

    Route::post('reports/rooms', 'Hotel\HotelReportController@rooms');
    Route::post('reports/stays', 'Hotel\HotelReportController@stays');
    Route::post('reports/migration', 'Hotel\HotelReportController@migration');
});
