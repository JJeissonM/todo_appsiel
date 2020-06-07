<?php 

Route::resource('consultorio_medico', 'Salud\ConsultorioMedicoController', ['except' => ['show']]);
Route::post('consultorio_medico/eliminar_paciente', 'Salud\PacienteController@eliminar');
Route::resource('consultorio_medico/pacientes', 'Salud\PacienteController');


Route::get('consultorio_medico/consultas/{consulta_id}/print', 'Salud\ConsultaController@imprimir');
Route::get('consultorio_medico/consultas/{consulta_id}/delete', 'Salud\ConsultaController@delete');



Route::resource('consultorio_medico/consultas', 'Salud\ConsultaController');

Route::resource('consultorio_medico/profesionales', 'Salud\ProfesionalSaludController');

Route::post('consultorio_medico/eliminar_resultado_examen_medico', 'Salud\ResultadoExamenMedicoController@eliminar_resultado_examen_medico');
Route::resource('consultorio_medico/resultado_examen_medico', 'Salud\ResultadoExamenMedicoController');

Route::get('consultorio_medico_get_tabla_resultado_examen/{consulta_id}/{paciente_id}/{examen_id}', 'Salud\ResultadoExamenMedicoController@get_tabla_resultado_examen');

Route::post('consultorio_medico/eliminar_formula_optica', 'Salud\FormulaOpticaController@eliminar_formula_optica');
Route::get('consultorio_medico/formulas_opticas/{formula_id}/print', 'Salud\FormulaOpticaController@imprimir');
Route::get('consultorio_medico/asociar_examen/formulas_opticas/{formula_id}/{examen_id}', 'Salud\FormulaOpticaController@asociar_examen_a_formula');
Route::get('consultorio_medico/quitar_examen/formulas_opticas/{formula_id}/{examen_id}', 'Salud\FormulaOpticaController@quitar_examen_de_formula');
Route::resource('consultorio_medico/formulas_opticas', 'Salud\FormulaOpticaController');


Route::get('form_agregar_formula_factura', 'Salud\FormulaOpticaController@form_agregar_formula_factura');



Route::resource('consultorio_medico/anamnesis', 'Salud\AnamnesisController');


// REPORTES
Route::post('reportes_consultorio_medico/resumen_consultas', 'Salud\ReporteController@resumen_consultas');
Route::post('reportes_consultorio_medico/citas_control_vencidas', 'Salud\ReporteController@citas_control_vencidas');
