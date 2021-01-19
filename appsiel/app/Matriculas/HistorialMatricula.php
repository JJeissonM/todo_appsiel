<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;
use App\Matriculas\Estudiante;

use Auth;
use DB;
use App\Matriculas\Curso;
use App\Core\Colegio;

class HistorialMatricula extends Matricula
{
    protected $table = 'sga_matriculas';

    protected $fillable = ['periodo_lectivo_id', 'id_colegio', 'codigo', 'fecha_matricula', 'id_estudiante', 'curso_id', 'requisitos', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Fecha matricula', 'Año lectivo', 'Nombres', 'Apellidos', 'Doc. Identidad', 'Curso', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public function periodo_lectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class, 'periodo_lectivo_id');
    }
    
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }
    
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function libretas_pagos()
    {
        return $this->hasMany( 'App\Tesoreria\TesoLibretasPago', 'matricula_id' );
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Matricula::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_matriculas.periodo_lectivo_id')
            ->select(
                'sga_matriculas.codigo AS campo1',
                'sga_matriculas.fecha_matricula AS campo2',
                'sga_periodos_lectivos.descripcion AS campo3',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4'),
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2) AS campo5'),
                'core_terceros.numero_identificacion AS campo6',
                'sga_cursos.descripcion AS campo7',
                'sga_matriculas.estado AS campo8',
                'sga_matriculas.id AS campo9'
            )->where("sga_matriculas.codigo", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.fecha_matricula", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2)"), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.estado", "LIKE", "%$search%")
            ->orderBy('sga_matriculas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Matricula::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_matriculas.periodo_lectivo_id')
            ->select(
                'sga_matriculas.codigo AS CODIGO',
                'sga_matriculas.fecha_matricula AS FECHA_DE_MATRICULA',
                'sga_periodos_lectivos.descripcion AS DESCRIPCIÓN',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres) AS NOMBRES'),
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2) AS APELLIDOS'),
                'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
                'sga_cursos.descripcion AS DESCRIPCIÓN',
                'sga_matriculas.estado AS ESTADO'
            )->where("sga_matriculas.codigo", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.fecha_matricula", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2)"), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.estado", "LIKE", "%$search%")
            ->orderBy('sga_matriculas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MATRICULAS DE ESTUIANTES";
    }

    public function store_adicional( $datos, $registro )
    {
        $id_colegio = Colegio::get_colegio_user()->id;

        $registro->id_colegio = $id_colegio;
        $registro->estado = 'Inactivo';
        $registro->requisitos = '-----';
        $registro->save();
    }

}
