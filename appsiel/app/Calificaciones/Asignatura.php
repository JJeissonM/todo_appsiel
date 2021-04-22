<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Core\Colegio;
use App\Calificaciones\CursoTieneAsignatura;
use App\Core\Foro;

class Asignatura extends Model
{
    protected $table = 'sga_asignaturas';

    protected $fillable = ['id_colegio', 'descripcion', 'abreviatura', 'estado', 'area_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Área', 'Descripción', 'Abreviatura', 'Estado'];

    public function area()
    {
        return $this->belongsTo( Area::class, 'area_id');
    }

    public function foros()
    {
        return $this->hasMany(Foro::class);
    }

    public static function consultar_registros($nro_registros, $search)
    {
        /*$select_raw = 'IF(sga_asignaturas.maneja_calificacion=0,REPLACE(sga_asignaturas.maneja_calificacion,0,"No"),REPLACE(sga_asignaturas.maneja_calificacion,1,"Si")) AS campo5';*/

        $registros = Asignatura::leftJoin('sga_areas', 'sga_areas.id', '=', 'sga_asignaturas.area_id')
            ->select(
                'sga_areas.descripcion AS campo1',
                'sga_asignaturas.descripcion AS campo2',
                'sga_asignaturas.abreviatura AS campo3',
                'sga_asignaturas.estado AS campo4',
                'sga_asignaturas.id AS campo5'
            )->where("sga_areas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.abreviatura", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.estado", "LIKE", "%$search%")
            ->orderBy('sga_asignaturas.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Asignatura::leftJoin('sga_areas', 'sga_areas.id', '=', 'sga_asignaturas.area_id')
            ->select(
                'sga_areas.descripcion AS ÁREA',
                'sga_asignaturas.descripcion AS DESCRIPCIÓN',
                'sga_asignaturas.abreviatura AS ABREVIATURA',
                'sga_asignaturas.estado AS ESTADO'
            )->where("sga_areas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.abreviatura", "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.estado", "LIKE", "%$search%")
            ->orderBy('sga_asignaturas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ASIGNATURAS";
    }

    public static function get_array_to_select()
    {
        $opciones = Asignatura::where('estado', '=', 'Activo')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function opciones_campo_select()
    {
        $opciones = Asignatura::where('estado', '=', 'Activo')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function asignadas_al_curso($periodo_lectivo_id, $curso_id)
    {
        return Asignatura::leftJoin('sga_areas', 'sga_areas.id', '=', 'sga_asignaturas.area_id')
            ->leftJoin('sga_curso_tiene_asignaturas', 'sga_curso_tiene_asignaturas.asignatura_id', '=', 'sga_asignaturas.id')
            ->where('sga_curso_tiene_asignaturas.periodo_lectivo_id', $periodo_lectivo_id)
            ->where('sga_curso_tiene_asignaturas.curso_id', $curso_id)
            ->select(
                'sga_asignaturas.id',
                'sga_asignaturas.descripcion',
                'sga_asignaturas.abreviatura'
            )
            ->orderBy('sga_curso_tiene_asignaturas.orden_boletin')
            ->get();
    }

    public static function get_registros_estado_activo()
    {
        return Asignatura::where('estado', 'Activo')->get();
    }

    public static function asignaturas_calificadas($curso_id, $periodo_id)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $array_wheres = [
            ['sga_curso_tiene_asignaturas.curso_id', $curso_id],
            ['sga_asignaturas.id_colegio', $colegio->id],
            ['sga_asignaturas.estado', "Activo"]
        ];

        if ($area_id != null) {
            $array_wheres += ['sga_asignaturas.area_id', $area_id];
        }

        return Asignatura::leftJoin('sga_curso_tiene_asignaturas', 'sga_curso_tiene_asignaturas.asignatura_id', '=', 'sga_asignaturas.id')
            ->leftJoin('sga_areas', 'sga_areas.id', '=', 'sga_asignaturas.area_id')
            ->where($array_wheres)
            ->select(
                'sga_asignaturas.id',
                'sga_asignaturas.abreviatura',
                'sga_asignaturas.descripcion',
                'sga_curso_tiene_asignaturas.intensidad_horaria',
                'sga_curso_tiene_asignaturas.orden_boletin',
                'sga_curso_tiene_asignaturas.maneja_calificacion',
                'sga_areas.descripcion as area',
                'sga_areas.orden_listados as orden'
            )
            ->orderBy('sga_areas.orden_listados', 'ASC')
            ->orderBy('sga_curso_tiene_asignaturas.orden_boletin', 'ASC')
            ->get();
    }

    public static function get_registros_del_periodo_lectivo( $periodo_lectivo_id )
    {
        return CursoTieneAsignatura::leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_curso_tiene_asignaturas.asignatura_id')
                            ->leftJoin('sga_areas','sga_areas.id','=','sga_asignaturas.area_id')
                            ->where( 'sga_curso_tiene_asignaturas.periodo_lectivo_id', $periodo_lectivo_id )
                            ->select(
                                        'sga_curso_tiene_asignaturas.asignatura_id',
                                        'sga_asignaturas.descripcion AS asignatura_descripcion',
                                        'sga_areas.id',
                                        'sga_areas.descripcion',
                                        'sga_areas.orden_listados',
                                        'sga_curso_tiene_asignaturas.intensidad_horaria',
                                        'sga_curso_tiene_asignaturas.orden_boletin',
                                        'sga_curso_tiene_asignaturas.maneja_calificacion')
                            ->orderBy('sga_areas.orden_listados','ASC')
                            ->get()
                            ->unique('asignatura_id');
    }

    public static function asignadas_al_grado( $periodo_lectivo_id, $grado_id )
    {
        return CursoTieneAsignatura::leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_curso_tiene_asignaturas.asignatura_id')
                            ->leftJoin('sga_areas','sga_areas.id','=','sga_asignaturas.area_id')
                            ->leftJoin('sga_cursos','sga_cursos.id','=','sga_curso_tiene_asignaturas.curso_id')
                            ->where( 'sga_curso_tiene_asignaturas.periodo_lectivo_id', $periodo_lectivo_id )
                            ->where( 'sga_cursos.sga_grado_id', $grado_id )
                            ->select(
                                        'sga_curso_tiene_asignaturas.asignatura_id',
                                        'sga_asignaturas.descripcion AS asignatura_descripcion',
                                        'sga_areas.id',
                                        'sga_areas.descripcion',
                                        'sga_areas.orden_listados',
                                        'sga_curso_tiene_asignaturas.intensidad_horaria',
                                        'sga_curso_tiene_asignaturas.orden_boletin',
                                        'sga_curso_tiene_asignaturas.maneja_calificacion')
                            ->orderBy('sga_areas.orden_listados','ASC')
                            ->get()
                            ->unique('asignatura_id');
    }

    public function validar_eliminacion($id)
    {
        /*
         11 Tablas a validar:
            actividades_escolares, asignaciones_profesores, asignaturas_calificadas, asistencia_clases, calificaciones, calificaciones_auxiliares, logros, sga_calificaciones_encabezados, sga_control_disciplinario, sga_curso_tiene_asignaturas, sga_metas, 

            EN ALGUNAS TABLAS, LA LLAVE FORANEA ESTA COMO id_asignatura y EN OTRA COMO asignatura_id
        */
        $tablas_relacionadas = '{
                            "0":{
                                    
                                    "tabla":"sga_calificaciones_auxiliares",
                                    "llave_foranea":"id_asignatura",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "1":{
                                    "tabla":"sga_asignaciones_profesores",
                                    "llave_foranea":"id_asignatura",
                                    "mensaje":"Tiene Profesor asignado."
                                },
                            "2":{
                                    "tabla":"sga_asistencia_clases",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene asistencia a clases relacionadas."
                                },
                            "3":{
                                    "tabla":"sga_asistencia_clases",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene asistencia a clases relacionadas."
                                },
                            "4":{
                                    "tabla":"sga_calificaciones",
                                    "llave_foranea":"id_asignatura",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "5":{
                                    "tabla":"sga_actividades_escolares",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene actividades escolares relacionadas."
                                },
                            "6":{
                                    "tabla":"sga_logros",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene logros creados."
                                },
                            "7":{
                                    "tabla":"sga_calificaciones_encabezados",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene encabezados de calificaciones relacionados."
                                },
                            "8":{
                                    "tabla":"sga_control_disciplinario",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene anotaciones de control disciplinario."
                                },
                            "9":{
                                    "tabla":"sga_curso_tiene_asignaturas",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Ya pertence a un curso."
                                },
                            "10":{
                                    "tabla":"sga_metas",
                                    "llave_foranea":"asignatura_id",
                                    "mensaje":"Tiene metas creadas."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        //$cantidad = count($tablas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                //dd([ $una_tabla->tabla, $una_tabla->llave_foranea, $id, $registro, $una_tabla->mensaje ] );
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
