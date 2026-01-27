<?php 


// ACTIVIDADES ESCOLARES

use Illuminate\Support\Facades\Route;

Route::get('actividades_escolares/ver_actividad/{actividad_id}', 'ActividadesEscolares\ActividadesEscolaresController@ver_actividad');

Route::post('actividades_escolares/eliminar_actividad', 'ActividadesEscolares\ActividadesEscolaresController@eliminar_actividad');

Route::get('actividades_escolares/visualizar_resultados_estudiante/{cuestionario_id}/{estudiante_id}', 'ActividadesEscolares\ActividadesEscolaresController@visualizar_resultados_estudiante');
Route::get('actividades_escolares/hacer_actividad/{id_actividad}', 'ActividadesEscolares\ActividadesEscolaresController@hacer_actividad');
Route::post('actividades_escolares/guardar_resultado_actividad', 'ActividadesEscolares\ActividadesEscolaresController@guardar_resultado_actividad');
Route::post('actividades_escolares/guardar_respuesta', 'ActividadesEscolares\ActividadesEscolaresController@guardar_respuesta');


Route::post('sin_cuestionario_guardar_respuesta', 'ActividadesEscolares\ActividadesEscolaresController@sin_cuestionario_guardar_respuesta');
Route::get('almacenar_calificacion_a_respuesta_estudiante', 'ActividadesEscolares\ActividadesEscolaresController@almacenar_calificacion_a_respuesta_estudiante');

Route::get('remover_archivo_adjunto/{respuesta_id}', 'ActividadesEscolares\ActividadesEscolaresController@remover_archivo_adjunto');

Route::resource('actividades_escolares', 'ActividadesEscolares\ActividadesEscolaresController');

Route::get('cuestionarios/revision', 'Cuestionarios\CuestionariosRevisionController@index')->name('cuestionarios.revision');
Route::post('cuestionarios/duplicar/{cuestionario_id}', 'Cuestionarios\CuestionariosRevisionController@duplicar')->name('cuestionarios.duplicar');
Route::get('cuestionarios/previsualizar/{cuestionario_id}', 'Cuestionarios\CuestionariosRevisionController@preview')->name('cuestionarios.preview');

// FOROS
Route::get('foros/{curso_id}/{asignatura_id}/{periodo_lectivo_id}/inicio', 'Core\ForoController@index')->name('foros.index');
Route::post('foros/inicio/crearnuevo', 'Core\ForoController@store')->name('foros.store');
Route::get('foros/{curso_id}/{asignatura_id}/{periodo_lectivo_id}/inicio/{idapp}/ver/{foro}/foro', 'Core\ForoController@show')->name('foros.show');
Route::post('foros/inicio/participacion/guardarrespuesta', 'Core\ForoController@guardarrespuesta')->name('foros.guardarrespuesta');
//Route::get('ver_foros/{curso_id}','AcademicoEstudianteController@ver_foros');

