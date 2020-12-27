<?php 

//  A C A D E M I C O  E S T U D I A N T E 
Route::get('academico_estudiante/horario', 'AcademicoEstudianteController@horario');

Route::get('academico_estudiante/calificaciones', 'AcademicoEstudianteController@calificaciones');
Route::post('academico_estudiante/ajax_calificaciones', 'AcademicoEstudianteController@ajax_calificaciones');

Route::get('academico_estudiante/observador_show/{estudiante_id}', 'AcademicoEstudianteController@observador_show');
Route::get('academico_estudiante/agenda', 'AcademicoEstudianteController@agenda');

Route::get('academico_estudiante/actividades_escolares/{curso_id}/{asignatura_id}', 'AcademicoEstudianteController@actividades_escolares');
Route::get('academico_estudiante/guias_planes_clases/{curso_id}/{asignatura_id}', 'AcademicoEstudianteController@guias_planes_clases');
Route::get('academico_estudiante/ver_guia_plan_clases/{curso_id}/{asignatura_id}/{plan_id}', 'AcademicoEstudianteController@ver_guia_plan_clases');

Route::get('academico_estudiante', 'AcademicoEstudianteController@index');

Route::get('academico_estudiante/mi_plan_de_pagos/{libreta_id}', 'AcademicoEstudianteController@mi_plan_de_pagos');


Route::get('mis_asignaturas/{curso_id}','AcademicoEstudianteController@mis_asignaturas');


Route::get('consultar_preinforme/{periodo_id}/{curso_id}/{estudiante_id}','AcademicoEstudianteController@consultar_preinforme');

Route::get('academico_estudiante/reconocimientos', 'AcademicoEstudianteController@reconocimientos');


// Solo deberia contener rutas del AcademicoEstudianteController, nada de Matriculas
// Route::get('academico_estudiante/usuarios_estudiantes', 'Matriculas\EstudianteController@usuarios_estudiantes');

// Route::get('academico_estudiante/modificar_usuario_estudiante/{id}', 'Matriculas\EstudianteController@modificar_usuario_estudiante');
// Route::put('academico_estudiante/actualizar_usuario_estudiante/{id}', 'Matriculas\EstudianteController@actualizar_usuario_estudiante');

