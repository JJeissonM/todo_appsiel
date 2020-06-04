<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;

use App\Sistema\Modelo;

class ExamenMedicoConsulta extends ExamenMedico
{

    protected $table = 'salud_examenes';

    public static function opciones_campo_select()
    {
        $opciones = ExamenMedicoConsulta::leftJoin('salud_resultados_examenes','salud_resultados_examenes.examen_id','=','salud_examenes.id')
                                        ->where('salud_resultados_examenes.paciente_id', Input::get('paciente_id') )
                                        ->where('salud_resultados_examenes.consulta_id', Input::get('consulta_id') )
                                        ->select('salud_examenes.id','salud_examenes.descripcion')
                                        ->groupBy('salud_examenes.id')
                                        ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
