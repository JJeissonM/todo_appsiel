<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    protected $table = 'cte_propietarios';
    protected $fillable = ['id', 'tercero_id', 'genera_planilla', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Propietario::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.core_tercero_id')
                            ->select('cte_propietarios.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->numero_identificacion.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
