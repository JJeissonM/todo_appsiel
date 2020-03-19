<?php

// Página Web - FRONT END
Route::resource('paginas', 'web\PaginaController');
Route::get('pagina/secciones/{id}', 'web\PaginaController@secciones');
Route::get('pagina/administrar', 'web\PaginaController@admin');
Route::get('pagina/addSeccion/{id}', 'web\PaginaController@addSeccion');
Route::post('pagina/nuevaSeccion', 'web\PaginaController@nuevaSeccion');
Route::delete('pagina/eliminarSeccion/{id}', 'web\PaginaController@eliminarSeccion');
Route::get('pagina/cambiarorden/{id}/{orden}', 'web\PaginaController@cambiarOrden');

//navegacion
Route::resource('navegacion', 'web\NavegacionController');
Route::post('navegacion/store', 'web\NavegacionController@storeNav')->name('navegacion.storenav');

Route::resource('menuItem', 'web\MenuNavegacionController');
Route::post('menuItem/update/{id}', 'web\MenuNavegacionController@update')->name('itemUpdate');
Route::get('item/delete/{id}', 'web\MenuNavegacionController@destroy');

Route::get('seccion/{widget}', 'web\SeccionController@orquestador');

//SLIDER A
Route::get('slider/{widget}', 'web\SliderController@create');
Route::get('slider/item/{item}', 'web\SliderController@destroyItem');
Route::resource('slider', 'web\SliderController');

//ABOUT US
Route::get('aboutus/create/{widget}', 'web\AboutusController@create');
Route::post('aboutus/store', 'web\AboutusController@store')->name('aboutus.store');
Route::put('aboutus/updated/{id}', 'web\AboutusController@updated')->name('aboutus.updated');
Route::get('/aboutus/{id}/institucional/leer', 'web\AboutusController@leer_institucional')->name('aboutus.leer_institucional');

//GALERIA
Route::get('galeria/create/{widget}', 'web\GaleriaController@create');
Route::get('galeria/edit/{album}', 'web\GaleriaController@edit');
Route::post('galeria/guardar/seccion', 'web\GaleriaController@guardarseccion')->name('galeria.guardar');
Route::put('galeria/modificar/seccion/{id}', 'web\GaleriaController@modificarseccion')->name('galeria.modificar');
Route::get('galeria/eliminar/{galeria_id}','web\GaleriaController@destroy');
Route::get('galeria/delete/foto/{imagen}', 'web\GaleriaController@destroyImg')->name('galeria.deleteimagen');
Route::get('galeria/destroy/album/{album}', 'web\GaleriaController@destroyAlbum');
Route::post('galeria/store', 'web\GaleriaController@store')->name('galeria.store');
Route::put('galeria/updated/{id}', 'web\GaleriaController@updated')->name('galeria.updated');
Route::get('/galeria/{id}/albums/index', 'web\GaleriaController@albums')->name('galeria.albums');
Route::get('/galeria/crear/','web\GaleriaController@importar')->name('galeria.importar');

Route::resource('sociales','web\RedesSocialesController');
Route::resource('footer','web\FooterController');
Route::post('footerstoreCategoria','web\FooterController@footerstoreCategoria')->name('footerstoreCategoria');
Route::get('footer/{id}/categorias','web\FooterController@categorias');
Route::put('footer/edit/categoria/{id}','web\FooterController@updateCategoria')->name('updateCategoria');
Route::post('footer/categoria/enlace','web\FooterController@newEnlace')->name('newEnlace');
Route::get('footer/eliminar/enlace/{id}','web\FooterController@eliminarEnlace');
Route::get('footer/eliminar/seccion/{id}','web\FooterController@eliminarSeccion');

//SERVICIOS
Route::get('servicios/create/{widget}', 'web\ServicioController@create');
Route::post('servicios/store', 'web\ServicioController@store')->name('servicios.store');
Route::get('servicios/edit/{itemservicio}', 'web\ServicioController@edit');
Route::post('servicios/guardar/itemservicio', 'web\ServicioController@guardar')->name('servicios.guardar');
Route::put('servicios/updated/{id}', 'web\ServicioController@updated')->name('servicios.updated');
Route::put('servicios/updated/item/{id}', 'web\ServicioController@modificar')->name('servicios.editar');
Route::get('servicios/destroy/item/{itemservicio}', 'web\ServicioController@destroy');
Route::get('servicios/destroy/{servicio}', 'web\ServicioController@delete');
Route::get('/servicios/{id}/index', 'web\ServicioController@leer_servicio')->name('servicios.leer_servicio');

//CONTACTENOS
Route::get('contactenos/create/{widget}', 'web\ContactenosController@create');
Route::post('contactenos/store', 'web\ContactenosController@store')->name('contactenos.store');
Route::put('contactenos/updated/{id}', 'web\ContactenosController@updated')->name('contactenos.updated');
Route::get('contactenos/configuracion/{names}/{email}/{asunto}/{message}/guardar', 'web\ContactenosController@guardar_contactenos')->name('contactenos.guardar');

//CLIENTES
Route::get('clientes/create/{widget}', 'web\ClienteController@creaste');
Route::post('clientes/store', 'web\ClienteController@store')->name('clientes.store');
Route::get('clientes/destroy/{cliente}', 'web\ClienteController@destroy');
Route::post('clientes/modificar/cliente/', 'web\ClienteController@updated')->name('clientes.modificar');

Route::post('pagina_web/contactenos', 'PaginaWeb\FrontEndController@contactenos');

Route::get('categoria/{id?}', 'PaginaWeb\FrontEndController@show_categoria');
Route::get('blog/{articulo?}', 'PaginaWeb\FrontEndController@blog');
Route::get('ajax_galeria_imagenes/{carousel_id}', 'PaginaWeb\FrontEndController@ajax_galeria_imagenes');

//ARTICLES
Route::post('articles/store', 'web\ArticleController@store')->name('article.store');
Route::resource('articles', 'web\ArticleController');
Route::post('articles/article/store', 'web\ArticleController@articlestore')->name('article.articlestore');
Route::post('articles/article/update', 'web\ArticleController@articleupdate')->name('article.articleupdate');
Route::get('articles/article/{id}/viewfinder', 'web\ArticleController@show')->name('article.show');
Route::get('article/delete/destroy/{id}','web\ArticleController@destroy');

//ARCHIVOS
Route::post('archivos/store', 'web\ArchivoController@store')->name('archivos.store');
Route::resource('archivos', 'web\ArchivoController');
Route::post('archivos/archivo/store', 'web\ArchivoController@archivostore')->name('archivos.archivostore');
Route::post('archivos/archivo/update', 'web\ArchivoController@archivoupdate')->name('archivos.archivoupdate');
Route::post('archivos/archivo/delete', 'web\ArchivoController@destroy')->name('archivos.delete');

Route::resource('cofiguraciones','web\ConfiguracionesController');

//PREGUNTAS FRECUENTES
Route::get('preguntas/create/{widget}', 'web\PreguntasfrecuenteController@create');
Route::get('preguntas/eliminar/itempregunta/{itempregunta}', 'web\PreguntasfrecuenteController@delete')->name('preguntas.eliminar');
Route::post('preguntas/guardar/seccion','web\PreguntasfrecuenteController@guardar')->name('preguntas.guardar');
Route::post('preguntas/store', 'web\PreguntasfrecuenteController@store')->name('preguntas.store');
Route::get('preguntas/destroy/{pregunta}', 'web\PreguntasfrecuenteController@destroy');
Route::post('preguntas/modificar/pregunta/', 'web\PreguntasfrecuenteController@updated')->name('preguntas.modificar');
Route::put('preguntas/ferecuntes/seccion/modificar/{seccion}', 'web\PreguntasfrecuenteController@modificar')->name('preguntas.updated');

//TESTIMONIALES
Route::get('testimonial/eliminar/itemtestimonial/{itemtestimonial}', 'web\TestimonialController@delete')->name('testimonial.eliminar');
Route::post('testimonial/guardar/seccion','web\TestimonialController@guardar')->name('testimonial.guardar');
Route::post('testimonial/store', 'web\TestimonialController@store')->name('testimonial.store');
Route::get('testimonial/destroy/{testimonial}', 'web\TestimonialController@destroy');
Route::post('testimonial/modificar/pregunta/', 'web\TestimonialController@updated')->name('testimonial.modificar');
Route::put('testimonial/testimonial/seccion/modificar/{seccion}', 'web\TestimonialController@modificar')->name('testimonial.updated');

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

//ICONOS
Route::get('pagina_web/icons/view', 'web\IconsController@view')->name('icons.view');
//leer contactenos
Route::get('configuracion/contactenos/{id}/leer', 'web\ContactenosController@leer');

//PRODUCTOS
Route::post('pedidosweb/store', 'web\PedidoswebController@store')->name('pedidosweb.store');
Route::resource('pedidosweb', 'web\PedidoswebController');

//NUBE
Route::get('pagina_web/nube/view', 'web\NubeController@view')->name('nube.view');
Route::post('pagina_web/nube/ruta/get', 'web\NubeController@listPath')->name('nube.list');
Route::post('pagina_web/nube/ruta/get/all/delete', 'web\NubeController@delete')->name('nube.delete');
Route::post('pagina_web/nube/ruta/nueva/carpeta', 'web\NubeController@nueva')->name('nube.nueva');
Route::post('pagina_web/nube/ruta/upload', 'web\NubeController@upload')->name('nube.upload');
