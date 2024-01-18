<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use App\Core\Colegio;
use Illuminate\Support\Facades\Auth;

class PeriodoLectivoAux extends PeriodoLectivo
{
    protected $table='sga_periodos_lectivos';

    public static function opciones_campo_select()
    {
        $user = Auth::user();
        $colegio = Colegio::where('empresa_id', $user->empresa_id)->get()[0];

        $opciones = PeriodoLectivo::where('id_colegio',$colegio->id)
                                ->where('estado','Activo')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
