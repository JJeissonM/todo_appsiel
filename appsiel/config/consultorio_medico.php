<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Variables globales 
    |--------------------------------------------------------------------------
    |
    | Variable globales
    |
    */

    'secciones_consulta' => '{
    							"0":{ 
    									"nombre_seccion":"Historia Médica Ocupacional",
                                        "url_vista_show":"consultorio_medico.consultas.historia_medica_ocupacional",
                                        "activo":1,
                                        "orden":1
    								}, 
    							"1":{ 
    									"nombre_seccion":"Exámenes",
    									"url_vista_show":"consultorio_medico.consultas.examenes",
    									"activo":1,
    									"orden":2
    								}, 
    							"2":{ 
    									"nombre_seccion":"Fórmula Óptica",
    									"url_vista_show":"consultorio_medico.consultas.formula_optica",
    									"activo":1,
    									"orden":3
    								}, 
    							"3":{ 
    									"nombre_seccion":"Diagnóstico",
    									"url_vista_show":"consultorio_medico.consultas.diagnostico",
    									"activo":0,
    									"orden":4
    								}, 
    							"4":{ 
    									"nombre_seccion":"Prescripción Farmacológica",
    									"url_vista_show":"consultorio_medico.consultas.prescripciones_farmacologicas",
    									"activo":0,
    									"orden":5
    								}, 
    							"5":{ 
    									"nombre_seccion":"Observaciones",
    									"url_vista_show":"consultorio_medico.consultas.observaciones",
    									"activo":0,
    									"orden":6
    								}, 
    							"6":{ 
    									"nombre_seccion":"Remisión",
    									"url_vista_show":"consultorio_medico.consultas.remision",
    									"activo":0,
    									"orden":7
    								}, 
    							"7":{ 
    									"nombre_seccion":"Plan y/o tratamiento",
    									"url_vista_show":"consultorio_medico.consultas.plan_de_tratamiento",
    									"activo":0,
    									"orden":8
    								}, 
    							"8":{ 
    									"nombre_seccion":"Revisión por Sistemas",
    									"url_vista_show":"consultorio_medico.consultas.revision_por_sistemas",
    									"activo":0,
    									"orden":9
    								}, 
    							"9":{ 
    									"nombre_seccion":"Paraclínicos",
    									"url_vista_show":"consultorio_medico.consultas.paraclinicos",
    									"activo":0,
    									"orden":10
    								}, 
                                "10":{ 
                                        "nombre_seccion":"Resultados de la consulta",
                                        "url_vista_show":"consultorio_medico.consultas.resultados",
                                        "activo":1,
                                        "orden":4
                                    }, 
                                "11":{ 
                                        "nombre_seccion":"Anamnesis",
                                        "url_vista_show":"consultorio_medico.consultas.anamnesis",
                                        "activo":1,
                                        "orden":1
                                    } 
    						}',
    'mostrar_datos_laborales_paciente' => '1'
];