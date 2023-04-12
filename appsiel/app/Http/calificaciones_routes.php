<?php 

use Illuminate\Support\Facades\Route;

//              A P P   C A L I F I C A C I O N E S

//Logros
Route::get('calificaciones_logros/consultar/{asignatura_id}/{curso_id}', 'Calificaciones\LogroController@consultar');

Route::get('calificaciones_eliminar_logro/{logro_id}', 'Calificaciones\LogroController@eliminar_logros');
Route::get('calificaciones_eliminar_escala_valoracion/{id}', 'Calificaciones\LogroController@eliminar_escala_valoracion');

Route::get('calificaciones_logros/listar', 'Calificaciones\LogroController@listar');
Route::get('calificaciones_logros/inactivos', 'Calificaciones\LogroController@consulta_inactivos');
Route::get('cali_copiar_logros_de_un_periodo', 'Calificaciones\ProcesoController@form_copiar_logros_de_un_periodo');
Route::post('cali_copiar_logros_de_un_periodo', 'Calificaciones\ProcesoController@copiar_logros_de_un_periodo');

Route::resource('calificaciones_logros', 'Calificaciones\LogroController');

//Calificaciones
Route::post('calificaciones/calificar2', 'Calificaciones\CalificacionController@calificar2');

//  Asistencia a clases
Route::get('calificaciones/asistencia_clases/reportes', 'Calificaciones\AsistenciaClaseController@reportes');
Route::get('calificaciones/asistencia_clases/generar_reporte/{fecha_inicial}/{fecha_final}/{curso_id}/{tipo_reporte}', 'Calificaciones\AsistenciaClaseController@generar_reporte');

Route::post('calificaciones/asistencia_clases/continuar_creacion', 'Calificaciones\AsistenciaClaseController@continuar_creacion');
Route::resource('calificaciones/asistencia_clases', 'Calificaciones\AsistenciaClaseController');


// PREINFORME ACADEMICO
Route::post('cali_preinforme_academico_almacenar_anotacion','Calificaciones\PreinformeAcademicoController@almacenar_anotacion');
Route::post('cali_preinforme_academico_crear', 'Calificaciones\PreinformeAcademicoController@crear');
Route::resource('cali_preinforme_academico', 'Calificaciones\PreinformeAcademicoController');


// Reportes, INFORMES Y LISTADOS
Route::post('calificaciones/ajax_reporte_consolidado_por_curso','Calificaciones\ReporteController@ajax_reporte_consolidado_por_curso');
Route::resource('calificaciones/informe_final', 'Calificaciones\InformeFinalController');


Route::post('calificaciones/consolidado_periodo_por_curso','Calificaciones\ReporteController@consolidado_periodo_por_curso');
Route::post('calificaciones/cuadro_honor_estudiantes','Calificaciones\ReporteController@cuadro_honor_estudiantes');
Route::post('calificaciones/promedio_acumulado_periodos','Calificaciones\ReporteController@promedio_acumulado_periodos');
Route::post('calificaciones/promedio_proyectado_asignaturas','Calificaciones\ReporteController@promedio_proyectado_asignaturas');
Route::post('calificaciones/promedio_consolidado_asignaturas','Calificaciones\ReporteController@promedio_consolidado_asignaturas');
Route::post('calificaciones_consulta_notas_auxiliares','Calificaciones\ReporteController@consulta_notas_auxiliares');

Route::post('calificaciones_rendimiento_areas_asignaturas','Calificaciones\ReporteController@rendimiento_areas_asignaturas');

Route::post('cali_repo_certificado_notas','Calificaciones\ReporteController@certificado_notas');

//Boletines
// Formulario de revisar
Route::get('calificaciones/boletines/revisar', 'Calificaciones\BoletinController@revisar1'); 

// Resultado de revisar1
Route::post('calificaciones/boletines/revisar2', ['as' => 'revision', 'uses' =>'Calificaciones\BoletinController@revisar2']); 

Route::get('/calificaciones/boletines/imprimir', 'Calificaciones\BoletinController@imprimir');
Route::post('/calificaciones/boletines/generarPDF', 'Calificaciones\BoletinController@generarPDF');

// Calcular puesto
Route::get('calificaciones/boletines/calcular_puesto', 'Calificaciones\BoletinController@calcular_puesto_g');
Route::post('calificaciones/boletines/calcular_puesto', 'Calificaciones\BoletinController@calcular_puesto_p');

Route::get('calificaciones/boletines/calcular_puesto_informe_final', 'Calificaciones\InformeFinalController@calcular_puesto_g');
Route::post('calificaciones/boletines/calcular_puesto_informe_final', 'Calificaciones\InformeFinalController@calcular_puesto_p');



Route::post('calificaciones/observaciones_boletin/observaciones_create2', 'Calificaciones\ObservacionBoletinController@observaciones_create2');
Route::post('calificaciones/guardar_observacion', 'Calificaciones\ObservacionBoletinController@guardar_observacion');
Route::resource('calificaciones/observaciones_boletin', 'Calificaciones\ObservacionBoletinController', ['except' => ['show']]);

Route::resource('calificaciones/boletines', 'Calificaciones\BoletinController', ['except' => ['show']]);

Route::get('calificaciones/index2', 'Calificaciones\CalificacionController@index2')->name('calificaciones.index2');
Route::post('calificaciones/almacenar_calificacion','Calificaciones\CalificacionController@almacenar_calificacion');
Route::get('calificaciones/pesos/{curso}/{periodo}/{asignatura}/verificar', 'Calificaciones\CalificacionController@verificar_peso')->name('calificaciones.verificar_peso');
Route::get('calificaciones/pesos/{curso}/{periodo}/{asignatura}/{celda}/listar', 'Calificaciones\CalificacionController@get_peso')->name('calificaciones.get_peso');


// ENCABEZADOS CALIFICACIONES
Route::resource('calificaciones_encabezados', 'Calificaciones\EncabezadoCalificacionController');


// Este resource debe ser el último
Route::resource('calificaciones', 'Calificaciones\CalificacionController', ['except' => ['show']]);



// PENSUM 
// Formulario

Route::get('calificaciones_cambiar_orden_asignatura/{periodo_lectivo_id}/{curso_id}/{asignatura_id}/{nueva_posicion}', 'Calificaciones\PensumController@cambiar_orden_asignatura');

Route::get('calificaciones_cambiar_intensidad_horaria_asignatura/{periodo_lectivo_id}/{curso_id}/{asignatura_id}/{nueva_ih}', 'Calificaciones\PensumController@cambiar_intensidad_horaria_asignatura');

Route::get('calificaciones/asignar_asignaturas', 'Calificaciones\PensumController@asignar_asignaturas');
Route::post('calificaciones/guardar_asignacion_asignatura', 'Calificaciones\PensumController@guardar_asignacion_asignatura');
Route::get('calificaciones/eliminar_asignacion_asignatura/{periodo_lectivo_id}/{curso_id}/{asignatura_id}', 'Calificaciones\PensumController@eliminar_asignacion_asignatura');
Route::get('calificaciones_opciones_select_asignaturas_del_curso/{curso_id}/{area_id}/{periodo_lectivo_id}/{estado_asignaturas}', 'Calificaciones\PensumController@opciones_select_asignaturas_del_curso');

// Listado de asignaciones
Route::get('calificaciones/revisar_asignaciones', 'Calificaciones\PensumController@revisar_asignaciones');
Route::get('calificaciones/copiar_asignaciones', 'Calificaciones\PensumController@copiar_asignaciones');
Route::get('calificaciones/copiar_asignaciones/procesar/{periodo_origen_id}/{periodo_destino_id}', 'Calificaciones\PensumController@copiar_asignaciones_procesar');
Route::get('calificaciones/periodo_lectivo_tiene_asignaciones/{periodo_lectivo_id}', 'Calificaciones\PensumController@periodo_lectivo_tiene_asignaciones'); // devuelve 0 o 1

// Obtener la tabla de las asignturas que ya tiene el curso
Route::get('calificaciones/asignar_asignaturas/get_tabla_asignaturas_asignadas/{periodo_lectivo_id}/{curso_id}', 'Calificaciones\PensumController@get_tabla_asignaturas_asignadas');




// AJAX
Route::get('get_select_periodos/{periodo_id}', 'Calificaciones\CalificacionController@get_select_periodos');
Route::get('get_select_asignaturas/{curso_id}/{periodo_lectivo_id?}', 'Calificaciones\CalificacionController@get_select_asignaturas');
Route::get('get_select_escala_valoracion/{periodo_id}/{curso_id}/{asignatura_id}', 'Calificaciones\CalificacionController@get_select_escala_valoracion');


Route::get('get_id_periodo_lectivo_del_periodo/{periodo_id}', 'Calificaciones\CalificacionController@get_id_periodo_lectivo_del_periodo');


/*
		PROCESOS
*/

Route::get('form_generar_promedio_notas_periodo_final', 'Calificaciones\ProcesoController@form_generar_promedio_notas_periodo_final');
Route::get('consultar_periodos_periodo_lectivo/{periodo_lectivo_id}', 'Calificaciones\ProcesoController@consultar_periodos_periodo_lectivo');
Route::get('calcular_promedio_notas_periodo_final/{periodo_lectivo_id}', 'Calificaciones\ProcesoController@calcular_promedio_notas_periodo_final');


Route::get('sga_consultar_areas_asignaturas_pesos/{periodo_lectivo_id}/{grado_id}', 'Calificaciones\ProcesoController@consultar_areas_asignaturas_pesos');
Route::post('sga_almacenar_pesos_asignaturas_areas', 'Calificaciones\ProcesoController@almacenar_pesos_asignaturas_areas');