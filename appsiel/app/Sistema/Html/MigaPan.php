<?php

namespace App\Sistema\Html;

use DB;
use Input;
use View;

class MigaPan
{
    // Retorna el array para la miga de pan
    // WARNING!!! Solo para la ruta "web" de los modelos
    public static function get_array( $aplicacion, $modelo_crud, $etiqueta_final)
    {

        $ruta = 'web';
        
        return [
                  [ 
                    'url' => $aplicacion->app.'?id='.$aplicacion->id,
                    'etiqueta' => $aplicacion->descripcion
                    ],
                  [ 
                    'url' => $ruta.'?id='.$aplicacion->id.'&id_modelo='.$modelo_crud->id,
                    'etiqueta' => $modelo_crud->descripcion
                    ],
                  [ 
                    'url' => 'NO',
                    'etiqueta' => $etiqueta_final
                    ]
                ];
    }

}
