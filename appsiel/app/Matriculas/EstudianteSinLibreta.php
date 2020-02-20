<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Matriculas\Matricula;
use App\Core\Colegio;

class EstudianteSinLibreta extends Estudiante
{
    protected $table = 'sga_estudiantes';

    public static function opciones_campo_select()
    {
        $opciones = Matricula::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                            ->leftJoin('teso_libretas_pagos', 'teso_libretas_pagos.matricula_id', '=', 'sga_matriculas.id')
                            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
                            ->whereNull('teso_libretas_pagos.matricula_id')
                            ->select(
                                        'sga_matriculas.id',
                                        'sga_cursos.descripcion AS curso_descripcion',
                                        DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo' ),
                                        'core_terceros.numero_identificacion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->numero_identificacion.' '.$opcion->nombre_completo.' ('.$opcion->curso_descripcion.')';
        }

        return $vec;
    }
}