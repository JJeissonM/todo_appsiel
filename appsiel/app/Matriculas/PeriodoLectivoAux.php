<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use App\Core\Colegio;
use Auth;
use DB;

class PeriodoLectivoAux extends PeriodoLectivo
{
    protected $table='sga_periodos_lectivos';

    public static function opciones_campo_select()
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

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
