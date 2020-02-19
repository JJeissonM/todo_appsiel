<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoMedioRecaudo extends Model
{
    protected $table = 'teso_medios_recaudo';

    protected $fillable = ['descripcion','comportamiento','por_defecto','maneja_puntos'];

    public $encabezado_tabla = ['Descripción','Comportamiento','Por defecto','Maneja puntos','Acción'];

    public static function consultar_registros()
    {
    	$registros = TesoMedioRecaudo::select('teso_medios_recaudo.descripcion AS campo1','teso_medios_recaudo.comportamiento AS campo2','teso_medios_recaudo.por_defecto AS campo3','teso_medios_recaudo.maneja_puntos AS campo4','teso_medios_recaudo.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
