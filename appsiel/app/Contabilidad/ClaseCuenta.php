<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ClaseCuenta extends Model
{
    protected $table = 'contab_cuenta_clases';

    protected $fillable = ['descripcion', 'naturaleza', 'tipo'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Naturaleza', 'Tipo'];

    public static function consultar_registros($nro_registros)
    {
        return ClaseCuenta::select(
            'contab_cuenta_clases.descripcion AS campo1',
            'contab_cuenta_clases.naturaleza AS campo2',
            'contab_cuenta_clases.tipo AS campo3',
            'contab_cuentas.id AS campo4'
        )
            ->orderBy('contab_cuentas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function opciones_campo_select()
    {
        $opciones = ClaseCuenta::all();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
