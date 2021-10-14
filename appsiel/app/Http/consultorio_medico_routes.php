<?php

Route::resource('consultorio_medico/pacientes', 'Salud\PacienteController');

Route::resource('consultorio_medico', 'Salud\ConsultorioMedicoController', ['except' => ['show']]);
Route::post('consultorio_medico/eliminar_paciente', 'Salud\PacienteController@eliminar');

Route::resource('consultorio_medico/odontograma', 'Salud\OdontogramaController');

Route::get('consultorio_medico/consultas/{consulta_id}/print', 'Salud\ConsultaController@imprimir');
Route::get('consultorio_medico/consultas/{consulta_id}/delete', 'Salud\ConsultaController@delete');



Route::get('consultorio_medico_create_consulta', 'Salud\ConsultaController@create2');
Route::resource('consultorio_medico/consultas', 'Salud\ConsultaController');

Route::resource('consultorio_medico/profesionales', 'Salud\ProfesionalSaludController');

Route::post('consultorio_medico/eliminar_resultado_examen_medico', 'Salud\ResultadoExamenMedicoController@eliminar_resultado_examen_medico');
Route::resource('consultorio_medico/resultado_examen_medico', 'Salud\ResultadoExamenMedicoController');

Route::get('consultorio_medico_get_tabla_resultado_examen/{consulta_id}/{paciente_id}/{examen_id}', 'Salud\ResultadoExamenMedicoController@get_tabla_resultado_examen');

Route::post('consultorio_medico/eliminar_formula_optica', 'Salud\FormulaOpticaController@eliminar_formula_optica');
Route::get('consultorio_medico/formulas_opticas/{formula_id}/print', 'Salud\FormulaOpticaController@imprimir');
Route::get('consultorio_medico/asociar_examen/formulas_opticas/{formula_id}/{examen_id}', 'Salud\FormulaOpticaController@asociar_examen_a_formula');
Route::get('consultorio_medico/quitar_examen/formulas_opticas/{formula_id}/{examen_id}', 'Salud\FormulaOpticaController@quitar_examen_de_formula');

Route::get('formula_optica_enviar_email/{formula_id}', 'Salud\FormulaOpticaController@enviar_por_email');

Route::resource('consultorio_medico/formulas_opticas', 'Salud\FormulaOpticaController');


Route::get('form_agregar_formula_factura', 'Salud\FormulaOpticaController@form_agregar_formula_factura');


Route::resource('consultorio_medico/anamnesis', 'Salud\AnamnesisController');

Route::post('ventas_anular_factura_medica', 'Ventas\FacturaMedicaController@anular_factura');
Route::resource('factura_medica', 'Ventas\FacturaMedicaController');


//		SALUD OCUPACIONAL
Route::get('salud_imprimir_historia_medica_ocupacional/{consulta_id}', 'Salud\SaludOcupacionalController@imprimir_historia_medica_ocupacional');
Route::get('salud_imprimir_certificado_aptitud/{consulta_id}', 'Salud\SaludOcupacionalController@imprimir_certificado_aptitud');

//		ODONTOLOGIA
Route::get('salud_imprimir_historia_clinica_odontologica/{consulta_id}', 'Salud\OdontologiaController@imprimir_historia_clinica');

Route::resource('salud_endodoncia', 'Salud\EndodonciaController');
Route::resource('salud_diagnostico_cie', 'Salud\DiagnosticoCieController');
Route::resource('salud_procedimiento_cups', 'Salud\ProcedimientoCupsController');
Route::resource('salud_rips', 'Salud\RipsController');



//CITAS MEDICAS
Route::resource('citas_medicas', 'Salud\CitasController');
Route::get('citas_medicas/{id}/delete', 'Salud\CitasController@destroy')->name('citas_medicas.delete');
Route::get('citas_medicas/agenda/citas', 'Salud\CitasController@citas')->name('citas_medicas.citas');
Route::get('citas_medicas/agenda/citas/{id}/delete', 'Salud\CitasController@citas_delete')->name('citas_medicas.citas_delete');
Route::get('citas_medicas/agenda/citas/{id}/estado/{estado}/cambiar', 'Salud\CitasController@citas_estado')->name('citas_medicas.citas_estado');
Route::post('citas_medicas/agenda/citas/store', 'Salud\CitasController@store_cita')->name('citas_medicas.store_cita');
Route::get('citas_medicas/agenda/citas/{fecha}/{hi}/{hf}/{consultorio}/{profesional}/verificar', 'Salud\CitasController@citas_verificar')->name('citas_medicas.citas_verificar');

// REPORTES
Route::post('reportes_consultorio_medico/resumen_consultas', 'Salud\ReporteController@resumen_consultas');
Route::post('reportes_consultorio_medico/citas_control_vencidas', 'Salud\ReporteController@citas_control_vencidas');
