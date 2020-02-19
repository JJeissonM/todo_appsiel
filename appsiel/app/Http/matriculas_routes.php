<?php 

//       A P P   M A T R I C U L A S 


// Inscripciones
Route::get('matriculas/inscripciones/creacion_masiva', 'Matriculas\InscripcionController@creacion_masiva');
Route::get('matriculas/inscripciones/eliminar/{id}', 'Matriculas\InscripcionController@eliminar');
Route::get('matriculas/inscripcion_print/{id_fila}', 'Matriculas\InscripcionController@inscripcion_print');
Route::resource('matriculas/inscripcion','Matriculas\InscripcionController');

// Usado para la lista de estudiantes
Route::get('get_cursos_del_grado/{grado_id}', 'Matriculas\ReportesController@get_cursos_del_grado');


//Estudiantes
Route::get('matriculas/estudiantes/nuevo/{documento?}/{matriculando?}', 'Matriculas\EstudianteController@create');
Route::get('matriculas/estudiantes/modificar/{id}', 'Matriculas\EstudianteController@modificar'); 
Route::get('matriculas/estudiantes/listar', 'Matriculas\EstudianteController@listar');
Route::get('matriculas/estudiantes/show/{estudiante_id}', 'Matriculas\EstudianteController@show');

// Importar de Excel
Route::get('matriculas/estudiantes/importar_excel', 'Matriculas\EstudianteController@importar_excel');
Route::post('matriculas/estudiantes/importar_excel/import-excel', 'Matriculas\ExcelEstudianteController@importFile');
Route::post('/matriculas/estudiantes/importar_excel/guardar', 'Matriculas\ExcelEstudianteController@store');


Route::resource('matriculas/estudiantes','Matriculas\EstudianteController', ['except' => ['show'] ]);


// Matrículas
Route::get('matriculas/eliminar/{id}', 'Matriculas\MatriculaController@eliminar');
Route::get('matriculas/show/{id}', 'Matriculas\MatriculaController@show');
Route::get('matriculas/index2', 'Matriculas\MatriculaController@index2');
Route::get('matriculas/imprimir/{id}', 'Matriculas\MatriculaController@imprimir');
Route::post('matriculas/crear_nuevo', 'Matriculas\MatriculaController@crear_nuevo');
Route::resource('matriculas', 'Matriculas\MatriculaController', ['except' => ['show']]);

// Observador
Route::get('/matriculas/estudiantes/observador/valorar_aspectos/{id_estudiante}', 'Matriculas\ObservadorEstudianteController@valorar_aspectos');

Route::post('/matriculas/estudiantes/observador/valorar_aspectos/', 'Matriculas\ObservadorEstudianteController@guardar_valoracion_aspectos');

Route::post('/matriculas/estudiantes/observador/analisis_foda/', 'Matriculas\ObservadorEstudianteController@analisis_foda');

Route::get('/matriculas/estudiantes/observador/imprimir_observador/{id_estudiante}','Matriculas\ObservadorEstudianteController@imprimir_observador');

Route::get('/matriculas/estudiantes/observador/show/{id_estudiante}','Matriculas\ObservadorEstudianteController@show');

Route::get('matriculas/estudiantes/eliminar_novedad_observador/{novedad_id}','Matriculas\ObservadorEstudianteController@eliminar_novedad');


Route::resource('/matriculas/estudiantes/observador', 'Matriculas\ObservadorEstudianteController', ['except' => ['show']]);



// Control disciplinario

// En académico docente
Route::get('matriculas/control_disciplinario/precreate/{curso_id}/{asignatura_id}', 'Matriculas\ControlDisciplinarioController@precreate');

Route::post('matriculas/control_disciplinario/crear', 'Matriculas\ControlDisciplinarioController@crear');

Route::get('matriculas/control_disciplinario/consultar/{curso_id}/{fecha}', 'Matriculas\ControlDisciplinarioController@consultar_control_disciplinario');

Route::get('matriculas/control_disciplinario/imprimir/{curso_id}/{fecha}', 'Matriculas\ControlDisciplinarioController@imprimir_control_disciplinario');


Route::get('matriculas/control_disciplinario/consultar2', 'Matriculas\ControlDisciplinarioController@consultar_control_disciplinario2');

Route::post('matriculas/ajax_consultar_control_disciplinario2', 'Matriculas\ControlDisciplinarioController@ajax_consultar_control_disciplinario2');


Route::resource('matriculas/control_disciplinario', 'Matriculas\ControlDisciplinarioController');
/**/
