<?php

namespace App\Calificaciones;

use App\Calificaciones\Calificacion;
use Illuminate\Database\Eloquent\Model;

use DB;
use App\Matriculas\PeriodoLectivo;

class LogroAnterior extends Logro
{
    protected $table = 'sga_logros';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Cód.', 'Año lectivo', 'Periodo', 'Curso', 'Asignatura', 'Escala de valoración', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;

        return Logro::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_logros.periodo_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_logros.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_logros.asignatura_id')
            ->leftJoin('sga_escala_valoracion', 'sga_escala_valoracion.id', '=', 'sga_logros.escala_valoracion_id')
            ->where('sga_periodos.periodo_lectivo_id', '<', $periodo_lectivo_id)
            ->select(
                'sga_logros.codigo AS campo1',
                'sga_periodos_lectivos.descripcion AS campo2',
                'sga_periodos.descripcion AS campo3',
                'sga_cursos.descripcion AS campo4',
                'sga_asignaturas.descripcion AS campo5',
                DB::raw('CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")") AS campo6'),
                'sga_logros.descripcion AS campo7',
                'sga_logros.estado AS campo8',
                'sga_logros.id AS campo9'
            )
            ->where("sga_logros.codigo", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")")'), "LIKE", "%$search%")
            ->orWhere("sga_logros.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.estado", "LIKE", "%$search%")
            ->orderBy('sga_logros.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;

        $string = Logro::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_logros.periodo_id')
            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_logros.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_logros.asignatura_id')
            ->leftJoin('sga_escala_valoracion', 'sga_escala_valoracion.id', '=', 'sga_logros.escala_valoracion_id')
            ->where('sga_periodos.periodo_lectivo_id', '<', $periodo_lectivo_id)
            ->select(
                'sga_logros.codigo AS CODIGO',
                'sga_periodos_lectivos.descripcion AS AÑO_LECTIVO',
                'sga_periodos.descripcion AS PERIODO',
                'sga_cursos.descripcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                DB::raw('CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")") AS ESCALA_DE_VALORACIÓN'),
                'sga_logros.descripcion AS DESCRIPCIÓN',
                'sga_logros.estado AS ESTADO'
            )
            ->where("sga_logros.codigo", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")")'), "LIKE", "%$search%")
            ->orWhere("sga_logros.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_logros.estado", "LIKE", "%$search%")
            ->orderBy('sga_logros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE LOGROS ANTERIORES";
    }
}
