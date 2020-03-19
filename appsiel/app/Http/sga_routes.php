<?php 


// ACTIVIDADES ESCOLARES
Route::get('actividades_escolares/ver_actividad/{actividad_id}', 'ActividadesEscolares\ActividadesEscolaresController@ver_actividad');

Route::post('actividades_escolares/eliminar_actividad', 'ActividadesEscolares\ActividadesEscolaresController@eliminar_actividad');

Route::get('actividades_escolares/visualizar_resultados_estudiante/{cuestionario_id}/{estudiante_id}', 'ActividadesEscolares\ActividadesEscolaresController@visualizar_resultados_estudiante');
Route::get('actividades_escolares/hacer_actividad/{id_actividad}', 'ActividadesEscolares\ActividadesEscolaresController@hacer_actividad');
Route::post('actividades_escolares/guardar_resultado_actividad', 'ActividadesEscolares\ActividadesEscolaresController@guardar_resultado_actividad');
Route::post('actividades_escolares/guardar_respuesta', 'ActividadesEscolares\ActividadesEscolaresController@guardar_respuesta');


Route::post('sin_cuestionario_guardar_respuesta', 'ActividadesEscolares\ActividadesEscolaresController@sin_cuestionario_guardar_respuesta');
Route::get('almacenar_calificacion_a_respuesta_estudiante', 'ActividadesEscolares\ActividadesEscolaresController@almacenar_calificacion_a_respuesta_estudiante');



Route::resource('actividades_escolares', 'ActividadesEscolares\ActividadesEscolaresController');

