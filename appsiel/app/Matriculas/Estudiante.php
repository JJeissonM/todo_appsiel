<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Matriculas\Matricula;
use App\Core\Colegio;
use App\Core\Tercero;

class Estudiante extends Model
{
    protected $table = 'sga_estudiantes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //inactivamos campos que ahora se guardarán en otra tabla: terceros, sga_responsableestudiantes, papa y mama
    //protected $fillable = ['imagen', 'id_colegio', 'core_tercero_id', 'genero', 'fecha_nacimiento', 'ciudad_nacimiento', 'papa', 'cedula_papa', 'ocupacion_papa', 'telefono_papa', 'email_papa', 'mama', 'cedula_mama', 'ocupacion_mama', 'telefono_mama', 'email_mama', 'grupo_sanguineo', 'alergias', 'medicamentos', 'eps', 'user_id'];

    protected $fillable = ['imagen', 'id_colegio', 'core_tercero_id', 'genero', 'fecha_nacimiento', 'ciudad_nacimiento', 'grupo_sanguineo', 'alergias', 'medicamentos', 'eps', 'user_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Documento', 'Género', 'Fecha nacimiento', 'Teléfono', 'Dirección', 'Email'];

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'core_tercero_id');
    }

    /**
     * Obtener todas las matriculas del estudiante.
     */
    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'id_estudiante');
    }

    public function matricula_activa()
    {
        return Matricula::where('id_estudiante', $this->id)->where('estado', 'Activo')->first();
    }

    public function registros_cartera()
    {
        return $this->hasMany('App\Tesoreria\TesoPlanPagosEstudiante', 'id_estudiante');
    }

    public function responsableestudiantes()
    {
        return $this->hasMany(Responsableestudiante::class);
    }

    public function responsable_financiero()
    {
        return Responsableestudiante::where('estudiante_id', $this->id)->where('tiporesponsable_id', 3)->first();
    }

    public function mama()
    {
        return Responsableestudiante::where('estudiante_id', $this->id)->where('tiporesponsable_id', 2)->first();
    }

    public function papa()
    {
        return Responsableestudiante::where('estudiante_id', $this->id)->where('tiporesponsable_id', 1)->first();
    }

    public function getTercero($id)
    {
        $e = Estudiante::find($id);
        return Tercero::find($e->core_tercero_id);
    }


    public static function consultar_registros($nro_registros, $search)
    {
        return Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS campo2'),
                'sga_estudiantes.genero AS campo3',
                'sga_estudiantes.fecha_nacimiento AS campo4',
                'core_terceros.telefono1 AS campo5',
                'core_terceros.direccion1 AS campo6',
                'core_terceros.email AS campo7',
                'sga_estudiantes.id AS campo8'
            )->where("sga_estudiantes.genero", "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.fecha_nacimiento", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_tipos_docs_id.abreviatura,' ',core_terceros.numero_identificacion)"), "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.email_papa", "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.email_mama", "LIKE", "%$search%")
            ->orderBy('sga_estudiantes.id', 'desc')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS NOMBRE'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS DOCUMENTO'),
                'sga_estudiantes.genero AS GENERO',
                'sga_estudiantes.fecha_nacimiento AS FECHA_DE_NACIMIENTO',
                'core_terceros.telefono1 AS TELEFONO',
                'core_terceros.direccion1 AS EMAIL_PAPÁ',
                'core_terceros.email AS EMAIL_MAMÁ',
                'sga_estudiantes.id AS ESTUDIANTE_ID',
                'core_terceros.id AS TERCERO_ID'
            )
            ->where("sga_estudiantes.genero", "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.fecha_nacimiento", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_tipos_docs_id.abreviatura,' ',core_terceros.numero_identificacion)"), "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.email_papa", "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.email_mama", "LIKE", "%$search%")
            ->orderBy('sga_estudiantes.id', 'desc')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ESTUDIANTES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->select('sga_estudiantes.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_nombre_completo($id, $modo_ordenamiento = 1)
    {
        switch ($modo_ordenamiento) {
            case '1': // Apellidos_Nombre
                $select_raw = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';
                break;

            case '2': // Nombre_Apellidos
                $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
                break;

            default:
                # code...
                break;
        }

        return Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')->where('sga_estudiantes.id', $id)->select(DB::raw($select_raw))->value('campo1');
    }


    public static function get_cantidad_estudiantes_x_curso($periodo_lectivo_id, $estado_matricula = 'Activo')
    {
        $array_wheres = [];

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.id_colegio' => Colegio::get_colegio_user()->id]);

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.periodo_lectivo_id' => $periodo_lectivo_id]);

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.estado' => $estado_matricula]);

        return Matricula::where($array_wheres)
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->select(DB::raw('COUNT(sga_matriculas.id_estudiante) AS Cantidad'), 'sga_cursos.descripcion AS curso')
            ->groupBy('sga_matriculas.curso_id')
            ->OrderBy('Cantidad', 'DESC')
            ->get();
    }

    public static function get_cantidad_estudiantes_x_genero($periodo_lectivo_id, $estado_matricula = 'Activo')
    {
        $array_wheres = [];

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.id_colegio' => Colegio::get_colegio_user()->id]);

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.periodo_lectivo_id' => $periodo_lectivo_id]);

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.estado' => $estado_matricula]);

        return Matricula::where($array_wheres)
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
            ->select(DB::raw('COUNT(sga_matriculas.id_estudiante) AS Cantidad'), 'sga_estudiantes.genero AS Genero')
            ->groupBy('sga_estudiantes.genero')
            ->get();
    }

    public static function get_estudiantes_x_antiguedad($periodo_lectivo_id, $estado_matricula = 'Activo')
    {
        $array_wheres = [];

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.id_colegio' => Colegio::get_colegio_user()->id]);

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.periodo_lectivo_id' => $periodo_lectivo_id]);

        $array_wheres = array_merge($array_wheres, ['sga_matriculas.estado' => $estado_matricula]);

        $matriculas_nuevas = Matricula::where($array_wheres)->get();

        $antiguos = 0;
        $nuevos = 0;
        //dd($matriculas_nuevas);
        foreach ($matriculas_nuevas as $matricula) {
            $cant_matriculas_estudiante = Matricula::where('id_estudiante', $matricula->id_estudiante)->count();
            if ($cant_matriculas_estudiante > 1) {
                $antiguos++;
            } else {
                $nuevos++;
            }
        }

        return [
            ['Antiguos', $antiguos],
            ['Nuevos', $nuevos],
        ];
    }

    public static function get_estudiante_x_tercero_id($tercero_id)
    {
        $array_wheres = [];

        $array_wheres = array_merge($array_wheres, ['core_tercero_id' => $tercero_id]);

        $estudiante = Estudiante::where($array_wheres)->get()->first();

        if (!is_null($estudiante)) {
            return Estudiante::get_datos_basicos($estudiante->id);
        }

        return null;
    }

    public static function get_datos_basicos($estudiante_id)
    {
        return Estudiante::leftJoin('users', 'users.id', '=', 'sga_estudiantes.user_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('core_ciudades', 'core_ciudades.id', '=', 'core_terceros.codigo_ciudad')
            ->where('sga_estudiantes.id', $estudiante_id)
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS tipo_y_numero_documento_identidad'),
                'sga_estudiantes.genero',
                'sga_estudiantes.fecha_nacimiento',
                'sga_estudiantes.ciudad_nacimiento',
                'sga_estudiantes.core_tercero_id',
                'sga_estudiantes.papa',
                'sga_estudiantes.cedula_papa',
                'sga_estudiantes.ocupacion_papa',
                'sga_estudiantes.telefono_papa',
                'sga_estudiantes.email_papa',
                'sga_estudiantes.mama',
                'sga_estudiantes.cedula_mama',
                'sga_estudiantes.ocupacion_mama',
                'sga_estudiantes.telefono_mama',
                'sga_estudiantes.email_mama',
                'sga_estudiantes.grupo_sanguineo',
                'sga_estudiantes.alergias',
                'sga_estudiantes.medicamentos',
                'sga_estudiantes.eps',
                'sga_estudiantes.user_id',
                'sga_estudiantes.imagen',
                'core_terceros.nombre1',
                'core_terceros.otros_nombres',
                'core_terceros.apellido1',
                'core_terceros.apellido2',
                'core_terceros.telefono1',
                'core_terceros.id_tipo_documento_id',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.barrio',
                'core_ciudades.descripcion AS ciudad',
                'core_terceros.email',
                'sga_estudiantes.id'
            )
            ->get()
            ->first();
    }


    public function validar_eliminacion($id)
    {
        // Esta tabla ya no se usa: sga_estudiante_tiene_actividad_escolar 

        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_control_disciplinario",
                                    "llave_foranea":"estudiante_id",
                                    "mensaje":"Tiene registros de control disciplinario."
                                },
                            "1":{
                                    "tabla":"sga_responsableestudiantes",
                                    "llave_foranea":"estudiante_id",
                                    "mensaje":"Tiene responsable relacionado."
                                },
                            "2":{
                                    "tabla":"sga_respuestas_cuestionarios",
                                    "llave_foranea":"estudiante_id",
                                    "mensaje":"Tiene resgistros de respuestas a cuestionarios."
                                },
                            "3":{
                                    "tabla":"sga_asistencia_clases",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de asistencia a clases."
                                },
                            "4":{
                                    "tabla":"sga_aspectos_observador",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Ya registros de ascpectos en el observador."
                                },
                            "5":{
                                    "tabla":"sga_calificaciones",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "6":{
                                    "tabla":"sga_calificaciones_auxiliares",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Ya Tiene calificaciones."
                                },
                            "7":{
                                    "tabla":"sga_calificaciones_encabezados",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene encabezados de calificaciones relacionados."
                                },
                            "8":{
                                    "tabla":"sga_foda_estudiantes",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de análisis DOFA."
                                },
                            "9":{
                                    "tabla":"sga_matriculas",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene matrículas asociadas."
                                },
                            "10":{
                                    "tabla":"sga_novedades_observador",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de novedades en el observador."
                                },
                            "11":{
                                    "tabla":"sga_observaciones_boletines",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de observaciones en boletines."
                                },
                            "12":{
                                    "tabla":"sga_preinformes_academicos",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros en preinformes académicos."
                                },
                            "13":{
                                    "tabla":"teso_cartera_estudiantes",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene registros de cartera (Plan de pagos) asociados."
                                },
                            "14":{
                                    "tabla":"teso_libretas_pagos",
                                    "llave_foranea":"id_estudiante",
                                    "mensaje":"Tiene libretas de pago asociadas."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
