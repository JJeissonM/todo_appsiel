<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

class Pais extends Model
{
    protected $table = 'core_paises'; 

    protected $fillable = ['descripcion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código','Descripción'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Pais::select(
                            'core_paises.id AS campo1',
                            'core_paises.descripcion AS campo2',
                            'core_paises.id AS campo3')
                        ->where("core_paises.id", "LIKE", "%$search%")
                        ->orWhere("core_paises.descripcion", "LIKE", "%$search%")
                        ->orderBy('core_paises.descripcion')
                        ->paginate($nro_registros);
    }

    public static function opciones_campo_select()
    {
        $opciones = Pais::all();

        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

}
