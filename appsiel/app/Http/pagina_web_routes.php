<?php


// Página Web - FRONT END
Route::resource('paginas','web\PaginaController');
Route::get('pagina/secciones/{id}','web\PaginaController@secciones');
Route::get('pagina/administrar','web\PaginaController@admin');
Route::get('pagina/addSeccion/{id}','web\PaginaController@addSeccion');
Route::post('pagina/nuevaSeccion','web\PaginaController@nuevaSeccion');

//navegacion
Route::resource('navegacion', 'web\NavegacionController');

Route::resource('menuItem','web\MenuNavegacionController');
Route::post('menuItem/update/{id}','web\MenuNavegacionController@update')->name('itemUpdate');
Route::get('item/delete/{id}','web\MenuNavegacionController@destroy');

Route::get('seccion/{widget}','web\SeccionController@orquestador');

//SLIDER
Route::get('slider/{widget}','web\SliderController@create');
Route::get('slider/item/{item}','web\SliderController@destroyItem');
Route::resource('slider','web\SliderController');

//ABOUT US
Route::get('aboutus/create/{widget}', 'web\AboutusController@create');
Route::post('aboutus/store', 'web\AboutusController@store')->name('aboutus.store');
Route::put('aboutus/updated/{id}', 'web\AboutusController@updated')->name('aboutus.updated');

//GALERIA
Route::get('galeria/create/{widget}', 'web\GaleriaController@create');
Route::get('galeria/edit/{album}', 'web\GaleriaController@edit');
Route::get('galeria/delete/foto/{imagen}','web\GaleriaController@destroyImg')->name('galeria.deleteimagen');
Route::get('galeria/destroy/album/{album}','web\GaleriaController@destroyAlbum');
Route::post('galeria/store', 'web\GaleriaController@store')->name('galeria.store');
Route::put('galeria/updated/{id}', 'web\GaleriaController@updated')->name('galeria.updated');

Route::resource('sociales','web\RedesSocialesController');

Route::post('pagina_web/contactenos', 'PaginaWeb\FrontEndController@contactenos');

Route::get('categoria/{id?}', 'PaginaWeb\FrontEndController@show_categoria');
Route::get('blog/{articulo?}', 'PaginaWeb\FrontEndController@blog');
Route::get('ajax_galeria_imagenes/{carousel_id}', 'PaginaWeb\FrontEndController@ajax_galeria_imagenes');


//Route::get('/{url?}', 'PaginaWeb\FrontEndController@direccionar_url');


// Página Web - BACK END

Route::post('pagina_web/crear_nuevo_modulo', 'PaginaWeb\ModuloController@crear_nuevo');
Route::resource('pagina_web/modulos', 'PaginaWeb\ModuloController');

Route::resource('pagina_web/secciones', 'PaginaWeb\SeccionController');

Route::get('pagina_web/be/{modulo}/{accion}/{registro_id?}', 'PaginaWeb\BackEndController@gestionar_modulos');
Route::resource('pagina_web', 'PaginaWeb\BackEndController');


Route::get('pw_barra_navegacion', 'PaginaWeb\FrontEndController@micrositio');

Route::get('/mweb/{id}/microsites', 'PaginaWeb\FrontEndController@micrositio');
Route::get('generar_slug/{cadena}', 'PaginaWeb\SlugController@generar_slug');


// MÓDULOS
Route::resource('pagina_web/carousel', 'PaginaWeb\CarouselController');

//iconos
Route::get('pagina_web/icons/view', 'web\IconsController@view')->name('icons.view');
