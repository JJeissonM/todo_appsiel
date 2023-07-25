<?php

namespace App\Calificaciones;

use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class AsistenciaClase extends Model
{
    protected $table = 'sga_asistencia_clases';
    
    protected $fillable = [ 'id_estudiante', 'curso_id', 'asignatura_id', 'fecha', 'asistio', 'anotacion' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Estudiante', 'Curso', 'Asignatura', 'Asistió?', 'Anotación'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class,'id_estudiante');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class,'curso_id');
    }

    public function asignatura()
    {
        return $this->belongsTo(Estudiante::class,'asignatura_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $array_wheres = [['sga_asistencia_clases.id', '>', '0']]; // todos los registros

        if ( Input::get('curso_id') != null) {
            $array_wheres = array_merge($array_wheres, [['sga_asistencia_clases.curso_id', '=', Input::get('curso_id')]]);
        }

        if ( Input::get('asignatura_id') != null) {
            $array_wheres = array_merge($array_wheres, [['sga_asistencia_clases.asignatura_id', '=', Input::get('asignatura_id')]]);
        }

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo2';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo2';
        }

        return AsistenciaClase::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_asistencia_clases.id_estudiante')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                                ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_asistencia_clases.curso_id')
                                ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_asistencia_clases.asignatura_id')
                                ->where($array_wheres)
                                ->select(
                                    'sga_asistencia_clases.fecha AS campo1',
                                    DB::raw($raw_nombre_completo),
                                    'sga_cursos.descripcion AS campo3',
                                    'sga_asignaturas.descripcion AS campo4',
                                    'sga_asistencia_clases.asistio AS campo5',
                                    'sga_asistencia_clases.anotacion AS campo6',
                                    'sga_asistencia_clases.id AS campo7'
                                )->where("sga_asistencia_clases.fecha", "LIKE", "%$search%")
                                ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
                                ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
                                ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
                                ->orWhere("sga_asistencia_clases.asistio", "LIKE", "%$search%")
                                ->orWhere("sga_asistencia_clases.anotacion", "LIKE", "%$search%")
                                ->orderBy('sga_asistencia_clases.created_at', 'DESC')
                                ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $array_wheres = [['sga_asistencia_clases.id', '>', '0']]; // todos los registros

        if ( Input::get('curso_id') != null) {
            $array_wheres = array_merge($array_wheres, [['sga_asistencia_clases.curso_id', '=', Input::get('curso_id')]]);
        }

        if ( Input::get('asignatura_id') != null) {
            $array_wheres = array_merge($array_wheres, [['sga_asistencia_clases.asignatura_id', '=', Input::get('asignatura_id')]]);
        }

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }
        
        $string = AsistenciaClase::leftJoin('sga_estuzzdiantes', 'sga_estudiantes.id', '=', 'sga_asistencia_clases.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_idzz')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_asistencia_clases.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_asistencia_clases.asignatura_id')
            ->where($array_wheres)
            ->select(
                'sga_asistencia_clases.id',
                'sga_asistencia_clases.fecha AS FECHA',
                DB::raw($raw_nombre_completo),
                'sga_cursos.descrssipcion AS CURSO',
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_asistencia_clases.asistio AS ASISTIÓ',
                'sga_asistencia_clases.anotacion AS ANOTACIÓN'
            )->where("sga_asistencia_clases.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw($raw_nombre_completo), "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asistencia_clases.asistio", "LIKE", "%$search%")
            ->orWhere("sga_asistencia_clases.anotacion", "LIKE", "%$search%")
            ->orderBy('sga_asistencia_clases.created_at', 'DESC')
            ->toSql();
            
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ASISTENCIA A CLASE";
    }

    /**
     * Obtener todas las asistencia_clases con sus daos relacionados
     */
    public static function get_asistecia_clases( $estudiante_id, $curso_id, $asignatura_id )
    {
        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS campo4';

        $array_wheres = [];

        if ( $estudiante_id != null ) {
            $array_wheres = array_merge($array_wheres, ['asistencia_clases.estudiante_id' => $estudiante_id]);
        }

        if ( $curso_id != null ) {
            $array_wheres = array_merge($array_wheres, ['asistencia_clases.curso_id' => $curso_id]);
        }

        if ( $asignatura_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['asistencia_clases.asignatura_id' => $asignatura_id] );
        }

        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS campo2';

        $registros = AsistenciaClase::where($array_wheres)
                    ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'asistencia_clases.id_estudiante')
                    ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'asistencia_clases.curso_id')
                    ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'asistencia_clases.asignatura_id')
                    ->select('asistencia_clases.fecha AS campo1',
                            DB::raw($select_raw),
                            'sga_cursos.descripcion AS campo3',
                            'sga_asignaturas.descripcion AS campo4',
                            'asistencia_clases.asistio AS campo5',
                            'asistencia_clases.anotacion AS campo6',
                            'asistencia_clases.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_inasistencias( $curso_id, $fecha_inicial, $fecha_final, $estudiante_id, $asignatura_id)
    {
        $array_wheres = [ ['asistio', '=', 'No'] ];

        if ($curso_id != null)
        {
            $array_wheres = array_merge($array_wheres, [ ['curso_id', '=', $curso_id] ] );
        }

        if ($estudiante_id != null)
        {
            $array_wheres = array_merge($array_wheres, [ ['id_estudiante', '=', $estudiante_id] ] );
        }

        if ($asignatura_id != null)
        {
            $array_wheres = array_merge($array_wheres, [ ['asignatura_id', '=', $asignatura_id] ] );
        }

        return AsistenciaClase::whereBetween('fecha', [$fecha_inicial, $fecha_final])
                            ->where( $array_wheres )
                            ->select(
                                        DB::raw( 'count(*) as cantidad, id_estudiante, asignatura_id, curso_id')
                                    )
                            ->groupBy('id_estudiante')
                            ->get();
    }
}
