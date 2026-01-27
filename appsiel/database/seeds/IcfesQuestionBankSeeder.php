<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcfesQuestionBankSeeder extends Seeder
{
    public function run()
    {
        $examBanks = [
            [
                'tipo_icfes' => 'saber_3',
                'descripcion' => 'Saber 3° - Razonamiento verbal',
                'detalle' => 'Preguntas breves para trabajar sinónimos, antónimos y comprensión literal.',
                'preguntas' => [
                    [
                        'descripcion' => '¿Cuál es el antónimo de "benevolente"?',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Generoso',
                            'B' => 'Malévolo',
                            'C' => 'Paciente',
                            'D' => 'Amable',
                        ],
                        'respuesta_correcta' => 'B',
                    ],
                    [
                        'descripcion' => 'El texto dice "Leer es viajar sin salir de casa". ¿Qué idea transmite?',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Los viajes son caros',
                            'B' => 'La lectura amplía perspectivas',
                            'C' => 'Es mejor quedarse en casa',
                            'D' => 'Viajar es innecesario',
                        ],
                        'respuesta_correcta' => 'B',
                    ],
                ],
            ],
            [
                'tipo_icfes' => 'saber_5',
                'descripcion' => 'Saber 5° - Matemáticas básicas',
                'detalle' => 'Operaciones aritméticas y razonamiento numérico.',
                'preguntas' => [
                    [
                        'descripcion' => 'Si cada caja contiene 3 tabletas y se tienen 4 cajas, ¿cuántas tabletas hay en total?',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => '7',
                            'B' => '12',
                            'C' => '15',
                            'D' => '16',
                        ],
                        'respuesta_correcta' => 'B',
                    ],
                    [
                        'descripcion' => '¿Cuál es el área de un rectángulo de base 3 m y altura 5 m?',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => '8 m²',
                            'B' => '15 m²',
                            'C' => '10 m²',
                            'D' => '12 m²',
                        ],
                        'respuesta_correcta' => 'B',
                    ],
                ],
            ],
            [
                'tipo_icfes' => 'saber_9',
                'descripcion' => 'Saber 9° - Ciencias naturales',
                'detalle' => 'Preguntas sobre ecosistemas y procesos biológicos.',
                'preguntas' => [
                    [
                        'descripcion' => '¿Cuál es la función principal de la fotosíntesis?',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Convertir energía solar en alimento',
                            'B' => 'Producir oxígeno en la atmósfera',
                            'C' => 'Mover los músculos',
                            'D' => 'Regular la temperatura corporal',
                        ],
                        'respuesta_correcta' => 'A',
                    ],
                    [
                        'descripcion' => 'Cuando un ratón es consumido por una serpiente, ésta actúa como:',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Productor primario',
                            'B' => 'Consumidor primario',
                            'C' => 'Consumidor secundario',
                            'D' => 'Descomponedor',
                        ],
                        'respuesta_correcta' => 'C',
                    ],
                ],
            ],
            [
                'tipo_icfes' => 'pre_saber_11',
                'descripcion' => 'Pre Saber 11° - Ciudadanía',
                'detalle' => 'Derechos, deberes y participación democrática.',
                'preguntas' => [
                    [
                        'descripcion' => 'Un deber ciudadano en democracia es:',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Votar en elecciones',
                            'B' => 'Ignorar las leyes',
                            'C' => 'Evitar los debates públicos',
                            'D' => 'Conservar el dinero sin tributar',
                        ],
                        'respuesta_correcta' => 'A',
                    ],
                    [
                        'descripcion' => 'La libertad de expresión protege el derecho a:',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Vender información falsa',
                            'B' => 'Insultar sin consecuencias',
                            'C' => 'Ignorar opiniones contrarias',
                            'D' => 'Expresar opiniones sin censura',
                        ],
                        'respuesta_correcta' => 'D',
                    ],
                ],
            ],
            [
                'tipo_icfes' => 'saber_11',
                'descripcion' => 'Saber 11° - Competencias comunicativas',
                'detalle' => 'Argumentación, tesis y evidencia.',
                'preguntas' => [
                    [
                        'descripcion' => '¿Cuál es el propósito de una tesis en un texto argumentativo?',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Contar una anécdota',
                            'B' => 'Presentar un argumento claro',
                            'C' => 'Describir una escena',
                            'D' => 'Hacer un resumen sin juicio',
                        ],
                        'respuesta_correcta' => 'B',
                    ],
                    [
                        'descripcion' => 'Un elemento que fortalece un argumento es:',
                        'tipo' => 'Seleccion multiple única respuesta',
                        'opciones' => [
                            'A' => 'Opiniones sin respaldo',
                            'B' => 'Repetir lo mismo varias veces',
                            'C' => 'Uso de evidencia confiable',
                            'D' => 'Ignorar a quien disiente',
                        ],
                        'respuesta_correcta' => 'C',
                    ],
                ],
            ],
        ];

        $subjectBanks = [
            [
                'subject' => 'Música',
                'tipo_icfes' => 'musica',
                'descripcion' => 'Música - Fundamentos artísticos',
                'detalle' => 'Preguntas para reforzar valores musicales clásicos en los exámenes ICFES.',
                'concepts' => ['ritmo', 'melodía', 'instrumentación', 'dinámica', 'partitura'],
            ],
            [
                'subject' => 'Comportamiento',
                'tipo_icfes' => 'comportamiento',
                'descripcion' => 'Comportamiento - Hábitos para la convivencia',
                'detalle' => 'Banco diseñado para revisar principios de conducta y convivencia.',
                'concepts' => ['respeto', 'empatía', 'autocontrol', 'responsabilidad', 'trabajo en equipo'],
            ],
            [
                'subject' => 'Habilidades Motrices',
                'tipo_icfes' => 'habilidades_motrices',
                'descripcion' => 'Habilidades motrices - Coordinación y control corporal',
                'detalle' => 'Preguntas que fortalecen la comprensión del cuerpo y sus movimientos.',
                'concepts' => ['coordinación', 'equilibrio', 'agilidad', 'fuerza', 'destreza'],
            ],
            [
                'subject' => 'Matemáticas',
                'tipo_icfes' => 'matematicas',
                'descripcion' => 'Matemáticas - Razonamiento cuantitativo',
                'detalle' => 'Cuestionario con preguntas numéricas, de lógica y resolución de problemas.',
                'concepts' => ['operaciones básicas', 'razonamiento lógico', 'fracciones mixtas', 'patrones', 'modelado'],
            ],
            [
                'subject' => 'Tecnología',
                'tipo_icfes' => 'tecnologia',
                'descripcion' => 'Tecnología - Innovación digital',
                'detalle' => 'Preguntas sobre herramientas, programación y seguridad digital.',
                'concepts' => ['seguridad digital', 'programación', 'innovación', 'herramientas', 'algoritmos'],
            ],
            [
                'subject' => 'Religión',
                'tipo_icfes' => 'religion',
                'descripcion' => 'Religión - Valores y tradición',
                'detalle' => 'Banco para recordar prácticas, textos sagrados y vivencias religiosas.',
                'concepts' => ['oración', 'valores', 'textos sagrados', 'servicio', 'mitos'],
            ],
            [
                'subject' => 'Ciencias Sociales',
                'tipo_icfes' => 'ciencias_sociales',
                'descripcion' => 'Ciencias Sociales - Ciudadanía y territorio',
                'detalle' => 'Preguntas sobre historia, sociedad, economía y gobierno.',
                'concepts' => ['ciudadanía', 'gobierno', 'cultura', 'economía', 'territorio'],
            ],
            [
                'subject' => 'Ciencias',
                'tipo_icfes' => 'ciencias',
                'descripcion' => 'Ciencias - Naturaleza y método',
                'detalle' => 'Ejercicios para explorar el método científico, energía y medio ambiente.',
                'concepts' => ['método científico', 'energía', 'ecosistemas', 'células', 'clima'],
            ],
            [
                'subject' => 'Educación Socioemocional',
                'tipo_icfes' => 'educacion_socioemocional',
                'descripcion' => 'Educación socioemocional - Gestión emocional',
                'detalle' => 'Preguntas centradas en autoconocimiento, empatía y relaciones saludables.',
                'concepts' => ['autoconocimiento', 'gestión emocional', 'empatía', 'resiliencia', 'relaciones'],
            ],
            [
                'subject' => 'Ética',
                'tipo_icfes' => 'etica',
                'descripcion' => 'Ética - Juicio moral',
                'detalle' => 'Banco para repasar conceptos como justicia, verdad y derechos.',
                'concepts' => ['justicia', 'verdad', 'integridad', 'responsabilidad', 'derechos'],
            ],
            [
                'subject' => 'Estadística',
                'tipo_icfes' => 'estadistica',
                'descripcion' => 'Estadística - Datos y gráficos',
                'detalle' => 'Preguntas sobre medidas de tendencia y representación de datos.',
                'concepts' => ['media', 'mediana', 'moda', 'probabilidad', 'gráfica'],
            ],
            [
                'subject' => 'Geometría',
                'tipo_icfes' => 'geometria',
                'descripcion' => 'Geometría - Formas y espacios',
                'detalle' => 'Preguntas con énfasis en ángulos, polígonos y simetrías.',
                'concepts' => ['ángulos', 'polígonos', 'simetría', 'perímetros', 'teoremas'],
            ],
            [
                'subject' => 'Aritmética',
                'tipo_icfes' => 'aritmetica',
                'descripcion' => 'Aritmética - Cálculo mental',
                'detalle' => 'Cuestionario para reforzar operaciones básicas y fracciones.',
                'concepts' => ['suma', 'resta', 'multiplicación', 'división', 'fracciones'],
            ],
            [
                'subject' => 'Español',
                'tipo_icfes' => 'espanol',
                'descripcion' => 'Español - Comunicación escrita',
                'detalle' => 'Banco con preguntas de gramática, ortografía y comprensión.',
                'concepts' => ['gramática', 'ortografía', 'literatura', 'comprensión', 'expresión'],
            ],
            [
                'subject' => 'Artes del Lenguaje',
                'tipo_icfes' => 'artes_del_lenguaje',
                'descripcion' => 'Artes del lenguaje - Creatividad textual',
                'detalle' => 'Preguntas sobre narrativa, poesía y discurso persuasivo.',
                'concepts' => ['narrativa', 'poesía', 'ensayo', 'discurso', 'persuasión'],
            ],
            [
                'subject' => 'Catedrales',
                'tipo_icfes' => 'catedrales',
                'descripcion' => 'Catedrales - Patrimonio arquitectónico',
                'detalle' => 'Preguntas sobre estilos, elementos y símbolos de las catedrales.',
                'concepts' => ['arquitectura', 'gótico', 'vitral', 'nave', 'capilla'],
            ],
            [
                'subject' => 'Artes',
                'tipo_icfes' => 'artes',
                'descripcion' => 'Artes - Técnicas plásticas',
                'detalle' => 'Banco para discutir pintura, escultura y composición.',
                'concepts' => ['pintura', 'escultura', 'grabado', 'color', 'composición'],
            ],
            [
                'subject' => 'Educación Física',
                'tipo_icfes' => 'educacion_fisica',
                'descripcion' => 'Educación física - Movimiento y salud',
                'detalle' => 'Preguntas sobre entrenamiento, resistencia y valores del deporte.',
                'concepts' => ['resistencia', 'flexibilidad', 'juego limpio', 'salud', 'entrenamiento'],
            ],
            [
                'subject' => 'Expresión Corporal',
                'tipo_icfes' => 'expresion_corporal',
                'descripcion' => 'Expresión corporal - Comunicación no verbal',
                'detalle' => 'Preguntas para fortalecer gestos, improvisación y espacio corporal.',
                'concepts' => ['gestualidad', 'improvisación', 'espacio', 'ritmo', 'alineación'],
            ],
        ];

        foreach ($subjectBanks as $subjectBank) {
            $examBanks[] = [
                'tipo_icfes' => $subjectBank['tipo_icfes'],
                'descripcion' => $subjectBank['descripcion'],
                'detalle' => $subjectBank['detalle'],
                'preguntas' => $this->buildSubjectQuestions($subjectBank['subject'], $subjectBank['concepts']),
            ];
        }

        $timestamp = Carbon::now();

        foreach ($examBanks as $exam) {
            $cuestionarioId = DB::table('sga_cuestionarios')->insertGetId([
                'colegio_id' => 1,
                'descripcion' => $exam['descripcion'],
                'detalle' => $exam['detalle'],
                'activar_resultados' => 0,
                'estado' => 'Activo',
                'tipo_icfes' => $exam['tipo_icfes'],
                'created_by' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $orden = 1;
            foreach ($exam['preguntas'] as $pregunta) {
                $preguntaId = DB::table('sga_preguntas')->insertGetId([
                    'descripcion' => $pregunta['descripcion'],
                    'tipo' => $pregunta['tipo'],
                    'opciones' => json_encode($pregunta['opciones'], JSON_UNESCAPED_UNICODE),
                    'respuesta_correcta' => $pregunta['respuesta_correcta'],
                    'estado' => 'Activo',
                    'created_by' => 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                DB::table('sga_cuestionario_tiene_preguntas')->insert([
                    'orden' => $orden++,
                'cuestionario_id' => $cuestionarioId,
                'pregunta_id' => $preguntaId,
            ]);
            }
        }
    }

    private function buildSubjectQuestions(string $subject, array $concepts): array
    {
        $questions = [];
        $templates = [
            'Describe el rol de %s en %s.',
            '¿Cómo se aplica %s para resolver situaciones propias de %s?',
            '¿Qué característica distingue a %s dentro de %s?',
        ];

        foreach ($concepts as $conceptIndex => $concept) {
            foreach ($templates as $templateIndex => $template) {
                $questions[] = [
                    'descripcion' => ucfirst(sprintf($template, $concept, $subject)),
                    'tipo' => 'Seleccion multiple única respuesta',
                    'opciones' => [
                        'A' => "Es el aspecto clave de {$concept} en {$subject}.",
                        'B' => "Representa una idea que se estudia en otro campo.",
                        'C' => "Describe una práctica que no corresponde a {$subject}.",
                        'D' => "Es un enfoque técnico que no se aplica a {$concept}.",
                    ],
                    'respuesta_correcta' => 'A',
                ];
            }
        }

        return $questions;
    }
    
}
