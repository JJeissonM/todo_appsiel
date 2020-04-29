<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ClaseCuenta extends Model
{
    protected $table = 'contab_cuenta_clases';
    
    protected $fillable = ['descripcion','naturaleza','tipo'];

    public $encabezado_tabla = ['Descripción','Naturaleza','Tipo','Acción'];

    public static function consultar_registros()
    {
        return ClaseCuenta::select(
                                'contab_cuenta_clases.descripcion AS campo1',
                                'contab_cuenta_clases.naturaleza AS campo2',
                                'contab_cuenta_clases.tipo AS campo3',
                                'contab_cuentas.id AS campo4')
                            ->get()
                            ->toArray();
    }

    public static function opciones_campo_select()
    {
        $opciones = ClaseCuenta::all();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

}
