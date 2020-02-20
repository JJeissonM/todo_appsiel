<?php 

//      A C A D É M I C O   D O C E N T E

// Gestión de Profesores


// Asignaciones de Carga Académica
Route::get('academico_docente/profesores/create_asignacion/{id}', 'AcademicoDocente\AsignacionProfesorController@create_asignacion');
Route::get('academico_docente/profesores/buscar_asignaturas/{curso_id}/{user_id}', 'AcademicoDocente\AsignacionProfesorController@buscar_asignaturas');
Route::post('academico_docente/profesores/guardar_asignacion', 'AcademicoDocente\AsignacionProfesorController@guardar_asignacion');
Route::get('academico_docente/profesores/eliminar_asignacion/{id}', 'AcademicoDocente\AsignacionProfesorController@eliminar_asignacion');
Route::get('academico_docente/profesores/revisar_asignaciones', 'AcademicoDocente\AsignacionProfesorController@revisar_asignaciones');
Route::get('academico_docente/get_tabla_carga_academica/{user_id}/{periodo_lectivo_id}', 'AcademicoDocente\AsignacionProfesorController@get_tabla_carga_academica');

Route::get('academico_docente/copiar_carga_academica', 'AcademicoDocente\AsignacionProfesorController@copiar_carga_academica');
Route::get('academico_docente/copiar_carga_academica/procesar/{periodo_origen_id}/{periodo_destino_id}', 'AcademicoDocente\AsignacionProfesorController@copiar_carga_academica_procesar');
Route::get('academico_docente/periodo_lectivo_tiene_carga_academica/{periodo_lectivo_id}', 'AcademicoDocente\AsignacionProfesorController@periodo_lectivo_tiene_carga_academica'); // devuelve 0 o 1

// Actividades escolares
Route::get('academico_docente/get_carga_academica/{user_id}', 'AcademicoDocente\AsignacionProfesorController@get_carga_academica');


Route::get('academico_docente/profesores/eliminar_profesor/{id}', 'AcademicoDocente\ProfesorController@eliminar_profesor');
Route::resource('academico_docente/profesores', 'AcademicoDocente\ProfesorController', ['except' => ['show']]);


Route::post('academico_docente/asistencia_clases/continuar_creacion', 'AcademicoDocente\AsistenciaClaseController@continuar_creacion');
Route::resource('academico_docente/asistencia_clases', 'AcademicoDocente\AsistenciaClaseController', ['except' => ['show']]);

//Selección de datos para calificar
Route::get('academico_docente/calificar/{curso_id}/{asignatura_id}/{ruta}','AcademicoDocente\AcademicoDocenteController@calificar1');
//Formulario de calificar
Route::post('academico_docente/calificar2', 'AcademicoDocente\AcademicoDocenteController@calificar2'); 
Route::get('academico_docente/revisar_calificaciones/curso_id/{curso_id}/{asignatura_id}','AcademicoDocente\AcademicoDocenteController@revisar_calificaciones');

// Logros
Route::get('academico_docente/ingresar_logros/{curso_id}/{asignatura_id}','AcademicoDocente\AcademicoDocenteController@ingresar_logros');

Route::get('academico_docente/eliminar_logros/{curso_id}/{asignatura_id}/{logro_id}','AcademicoDocente\AcademicoDocenteController@eliminar_logros');

Route::get('academico_docente/revisar_logros/{curso_id}/{asignatura_id}','AcademicoDocente\AcademicoDocenteController@revisar_logros');
Route::get('academico_docente/modificar_logros/{curso_id}/{asignatura_id}/{logro_id}','AcademicoDocente\AcademicoDocenteController@modificar_logros');

// Metas (propósitos)
Route::get('academico_docente/ingresar_metas/{curso_id}/{asignatura_id}','Calificaciones\MetaController@ingresar_metas');
Route::get('academico_docente/eliminar_metas/{curso_id}/{asignatura_id}/{logro_id}','Calificaciones\MetaController@eliminar_metas');
Route::get('academico_docente/revisar_metas/{curso_id}/{asignatura_id}','Calificaciones\MetaController@revisar_metas');
Route::get('academico_docente/modificar_metas/{curso_id}/{asignatura_id}/{logro_id}','Calificaciones\MetaController@modificar_metas');
Route::put('academico_docente_guardar_meta/{id}','Calificaciones\MetaController@guardar_meta');
Route::post('academico_docente_guardar_meta','Calificaciones\MetaController@guardar_meta');

// Estudiantes
Route::get('academico_docente/revisar_estudiantes/curso_id/{curso_id}/id_asignatura/{id_asignatura}','AcademicoDocente\AcademicoDocenteController@revisar_estudiantes');
Route::get('academico_docente/listar_estudiantes/curso_id/{curso_id}/id_asignatura/{id_asignatura}','AcademicoDocente\AcademicoDocenteController@listar_estudiantes');

//  Observador estudiantes
Route::get('academico_docente/valorar_aspectos_observador/{id_estudiante}','AcademicoDocente\AcademicoDocenteController@valorar_aspectos_observador');
Route::post('academico_docente/guardar_valoracion_aspectos','AcademicoDocente\AcademicoDocenteController@guardar_valoracion_aspectos');
Route::get('academico_docente/novedad_observador/show_observador/{id_estudiante}', 'AcademicoDocente\AcademicoDocenteController@show_observador');

// Nodevades
Route::get('academico_docente/novedad_observador/eliminar/{id_novedad}', 'AcademicoDocente\NovedadObservadorController@eliminar');
Route::resource('academico_docente/novedad_observador', 'AcademicoDocente\NovedadObservadorController');

//  DOFA
Route::get('academico_docente/dofa_observador/eliminar/{id_novedad}', 'AcademicoDocente\DofaObservadorController@eliminar');
Route::resource('academico_docente/dofa_observador', 'AcademicoDocente\DofaObservadorController');

//  Planes de Clases

Route::get( 'sga_planes_clases_imprimir/{encabezado_id}', 'AcademicoDocente\PlanClasesController@imprimir');
Route::get( 'sga_planes_clases_eliminar/{encabezado_id}', 'AcademicoDocente\PlanClasesController@eliminar');
Route::resource('sga_planes_clases', 'AcademicoDocente\PlanClasesController');


Route::resource('academico_docente', 'AcademicoDocente\AcademicoDocenteController');