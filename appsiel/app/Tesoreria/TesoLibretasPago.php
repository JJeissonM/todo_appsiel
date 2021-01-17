<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoLibretasPago extends Model
{
    protected $table = 'teso_libretas_pagos';

    protected $fillable = ['id_estudiante','matricula_id','fecha_inicio','valor_matricula','valor_pension_anual','numero_periodos','valor_pension_mensual','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Curso', 'Cód. Matricula', 'Fecha inicio', 'Vlr. Matrícula', 'Vlr. Pensión anual', 'No. periodos', 'Vlr. Pensión mes', 'Estado'];

    public function estudiante()
    {
        return $this->belongsTo( 'App\Matriculas\Estudiante', 'id_estudiante' );
    }

    public function lineas_registros_plan_pagos()
    {
        return $this->hasMany( TesoPlanPagosEstudiante::class, 'id_libreta' );
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return TesoLibretasPago::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'teso_libretas_pagos.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_matriculas', 'sga_matriculas.id', '=', 'teso_libretas_pagos.matricula_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                'sga_cursos.descripcion AS campo2',
                'sga_matriculas.codigo AS campo3',
                'teso_libretas_pagos.fecha_inicio AS campo4',
                'teso_libretas_pagos.valor_matricula AS campo5',
                'teso_libretas_pagos.valor_pension_anual AS campo6',
                'teso_libretas_pagos.numero_periodos AS campo7',
                'teso_libretas_pagos.valor_pension_mensual AS campo8',
                'teso_libretas_pagos.estado AS campo9',
                'teso_libretas_pagos.id AS campo10'
            )
            ->where(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.codigo", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.fecha_inicio", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_matricula", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_anual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.numero_periodos", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_mensual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.estado", "LIKE", "%$search%")

            ->orderBy('teso_libretas_pagos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = TesoLibretasPago::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'teso_libretas_pagos.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_matriculas', 'sga_matriculas.id', '=', 'teso_libretas_pagos.matricula_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE'),
                'sga_cursos.descripcion AS CURSO',
                'sga_matriculas.codigo AS CÓD._MATRICULA',
                'teso_libretas_pagos.fecha_inicio AS FECHA_INICIO',
                'teso_libretas_pagos.valor_matricula AS VLR._MATRÍCULA',
                'teso_libretas_pagos.valor_pension_anual AS VLR._PENSIÓN_ANUAL',
                'teso_libretas_pagos.numero_periodos AS NO._PERIODOS',
                'teso_libretas_pagos.valor_pension_mensual AS VLR._PENSIÓN_MES',
                'teso_libretas_pagos.estado AS ESTADO'
            )
            ->where(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.codigo", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.fecha_inicio", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_matricula", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_anual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.numero_periodos", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_mensual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.estado", "LIKE", "%$search%")

            ->orderBy('teso_libretas_pagos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE LIBRETAS DE PAGO";
    }

    public static function consultar_un_registro($id)
    {       
        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS campo1';

        $registros = TesoLibretasPago::leftJoin('sga_estudiantes','sga_estudiantes.id','=','teso_libretas_pagos.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->leftJoin('sga_matriculas','sga_matriculas.id','=','teso_libretas_pagos.matricula_id')
                    ->leftJoin('sga_cursos','sga_cursos.id','=','sga_matriculas.curso_id')
                    ->where('teso_libretas_pagos.id', $id)
                    ->select(
                                DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1' ),
                                'sga_cursos.descripcion AS campo2',
                                'sga_matriculas.codigo AS campo3',
                                'teso_libretas_pagos.fecha_inicio AS campo4',
                                'teso_libretas_pagos.valor_matricula AS campo5',
                                'teso_libretas_pagos.valor_pension_anual AS campo6',
                                'teso_libretas_pagos.numero_periodos AS campo7',
                                'teso_libretas_pagos.valor_pension_mensual AS campo8',
                                'teso_libretas_pagos.estado AS campo9',
                                'teso_libretas_pagos.id AS campo10')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
