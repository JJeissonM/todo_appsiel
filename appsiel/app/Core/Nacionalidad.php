<?php

namespace App\Core;

class Nacionalidad extends Pais
{
    public static function opciones_campo_select()
    {
        $opciones = self::orderBy('gentilicio')->orderBy('descripcion')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $label = trim($opcion->gentilicio);
            if ($label == '') {
                $label = $opcion->descripcion;
            }

            $vec[$opcion->id] = $label;
        }

        return $vec;
    }
}
