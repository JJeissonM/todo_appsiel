<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Configuracion de guias academicas
     |--------------------------------------------------------------------------
     |
     | La configuracion que sigue permite definir cuantas guias debe entregar
     | un docente por curso y asignatura. Si no se encuentra una definicion
     | especifica, se usa la cantidad por defecto.
     |
     */
    'cantidad_por_defecto' => 4,

    'cantidad_por_curso' => [
        // '35' => 3, // ejemplo: el curso con ID 35 requiere 3 guías
    ],

    'cantidad_por_curso_asignatura' => [
        // '35' => [
        //     '12' => 4, // ejemplo: el curso 35 + asignatura 12 requiere 4 guías
        // ],
    ],
];
