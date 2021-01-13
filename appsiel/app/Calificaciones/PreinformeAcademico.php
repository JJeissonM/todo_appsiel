<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use Auth;
use Input;
use DB;

use App\Matriculas\Curso;
use App\Calificaciones\Asignatura;

use App\AcademicoDocente\AsignacionProfesor;


class PreinformeAcademico extends Model
{
    protected $table = 'sga_preinformes_academicos';
    protected $fillable = ['codigo_matricula', 'id_colegio', 'anio', 'id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'anotacion', 'creado_por', 'modificado_por'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Cód. matrícula', 'Periodo', 'Curso', 'Estudiante', 'Asignatura', 'Anotación'];

    public $vistas = '{"index":"layouts.index3","create":"calificaciones.preinformes_academicos.pre-create"}';

    public static function consultar_registros2($nro_registros, $search)
    {
        $user = Auth::user();

        $array_wheres = [['sga_preinformes_academicos.id', '>', 0]];

        if ($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) {
            $asignaturas = AsignacionProfesor::get_asignaturas_x_curso($user->id, $periodo_lectivo_id = null);

            $vec_cursos = array_column($asignaturas->toArray(), 'curso_id');

            $vec_asignaturas = array_column($asignaturas->toArray(), 'id_asignatura');

            return PreinformeAcademico::whereIn('sga_preinformes_academicos.id_asignatura', $vec_asignaturas)
                ->whereIn('sga_preinformes_academicos.curso_id', $vec_cursos)
                ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_preinformes_academicos.id_periodo')
                ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_preinformes_academicos.curso_id')
                ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_preinformes_academicos.id_estudiante')
                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_preinformes_academicos.id_asignatura')
                ->select(
                    'sga_preinformes_academicos.codigo_matricula AS campo1',
                    'sga_periodos.descripcion AS campo2',
                    'sga_cursos.descripcion AS campo3',
                    DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4'),
                    'sga_asignaturas.descripcion AS campo5',
                    'sga_preinformes_academicos.anotacion AS campo6',
                    'sga_preinformes_academicos.id AS campo7'
                )->where("sga_preinformes_academicos.codigo_matricula", "LIKE", "%$search%")
                ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
                ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
                ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
                ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
                ->orWhere("sga_preinformes_academicos.anotacion", "LIKE", "%$search%")
                ->orderBy('sga_preinformes_academicos.created_at', 'DESC')
                ->paginate($nro_registros);
        }



        return PreinformeAcademico::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_preinformes_academicos.id_periodo')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_preinformes_academicos.curso_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_preinformes_academicos.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_preinformes_academicos.id_asignatura')
            ->select(
                'sga_preinformes_academicos.codigo_matricula AS campo1',
                'sga_periodos.descripcion AS campo2',
                'sga_cursos.descripcion AS campo3',
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4'),
                'sga_asignaturas.descripcion AS campo5',
                'sga_preinformes_academicos.anotacion AS campo6',
                'sga_preinformes_academicos.id AS campo7'
            )->where("sga_preinformes_academicos.codigo_matricula", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_preinformes_academicos.anotacion", "LIKE", "%$search%")
            ->orderBy('sga_preinformes_academicos.created_at', 'DESC')
            ->paginate(100);
    }

    public static function sqlString($search)
    {
        $user = Auth::user();

        $array_wheres = [['sga_preinformes_academicos.id', '>', 0]];

        if ($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) {
            $asignaturas = AsignacionProfesor::get_asignaturas_x_curso($user->id, $periodo_lectivo_id = null);

            $vec_cursos = array_column($asignaturas->toArray(), 'curso_id');

            $vec_asignaturas = array_column($asignaturas->toArray(), 'id_asignatura');

            $string = PreinformeAcademico::whereIn('sga_preinformes_academicos.id_asignatura', $vec_asignaturas)
                ->whereIn('sga_preinformes_academicos.curso_id', $vec_cursos)
                ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_preinformes_academicos.id_periodo')
                ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_preinformes_academicos.curso_id')
                ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_preinformes_academicos.id_estudiante')
                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_preinformes_academicos.id_asignatura')
                ->select(
                    'sga_preinformes_academicos.codigo_matricula AS CÓD_MATRÍCULA',
                    'sga_periodos.descripcion AS PERIODO',
                    'sga_cursos.descripcion AS CURSO',
                    DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE'),
                    'sga_asignaturas.descripcion AS ASIGNATURA',
                    'sga_preinformes_academicos.anotacion AS ASIGNATURA',
                    'sga_preinformes_academicos.id AS ANOTACIÓN'
                )->where("sga_preinformes_academicos.codigo_matricula", "LIKE", "%$search%")
                ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
                ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
                ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
                ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
                ->orWhere("sga_preinformes_academicos.anotacion", "LIKE", "%$search%")
                ->orderBy('sga_preinformes_academicos.created_at', 'DESC')
                ->toSql();
            return str_replace('?', '"%' . $search . '%"', $string);
        }



        $string = PreinformeAcademico::where($array_wheres)
            ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_preinformes_academicos.id_periodo')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_preinformes_academicos.curso_id')
            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_preinformes_academicos.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_preinformes_academicos.id_asignatura')
            ->select(
                'sga_preinformes_academicos.codigo_matricula AS CÓD_MATRÍCULA',
                'sga_periodos.descripcion AS PERIODO',
                'sga_cursos.descripcion AS CURSO',
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE'),
                'sga_asignaturas.descripcion AS ASIGNATURA',
                'sga_preinformes_academicos.anotacion AS ASIGNATURA',
                'sga_preinformes_academicos.id AS ANOTACIÓN'
            )->where("sga_preinformes_academicos.codigo_matricula", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_preinformes_academicos.anotacion", "LIKE", "%$search%")
            ->orderBy('sga_preinformes_academicos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PREINFORME ACADEMICO";
    }

    public static function opciones_campo_select()
    {
        $opciones = PreinformeAcademico::where('sga_preinformes_academicos.estado', 'Activo')
            ->select('sga_preinformes_academicos.id', 'sga_preinformes_academicos.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public static function get_campos_adicionales_create($lista_campos)
    {
        $user = Auth::user();

        // Enviar formulario vacío. Se evita la creación, si se presiona el botón desde Académico Docente, pues no se han enviado ni el curos ni la asignatura 
        if (($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) && is_null(Input::get('curso_id'))) {
            return [
                [
                    "id" => 999,
                    "descripcion" => "Label no se puede ingresar registros desde esta opción.",
                    "tipo" => "personalizado",
                    "name" => "lbl_planilla",
                    "opciones" => "",
                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> No se pueden ingresar registros desde esta opción. </b> </label>
                                                    <br>
                                                    <a href="' . url('academico_docente?id=' . Input::get('id')) . '" class="btn btn-sm btn-info"> <i class="fa fa-th-large"></i> Ir a mi listado de asignaturas. </a>
                                                    <input name="plantilla_plan_clases_id" id="plantilla_plan_clases_id" type="hidden" value="" required="required"/>       
                                                </div>',
                    "atributos" => [],
                    "definicion" => "",
                    "requerido" => 0,
                    "editable" => 1,
                    "unico" => 0
                ]
            ];
        }


        /*
            Personalizar los campos
            Para Académico docente
        */
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            // Cuando se envían la asignatura y el curso en la URL
            if (!is_null(Input::get('curso_id'))) {
                switch ($lista_campos[$i]['name']) {
                    case 'curso_id':
                        $curso = Curso::find(Input::get('curso_id'));
                        $lista_campos[$i]['opciones'] = [$curso->id => $curso->descripcion];
                        break;
                    case 'id_asignatura':
                        $asignatura = Asignatura::find(Input::get('asignatura_id'));
                        $lista_campos[$i]['opciones'] = [$asignatura->id => $asignatura->descripcion];
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }

        array_push($lista_campos, [
            "id" => 999,
            "descripcion" => 'user_id',
            "tipo" => "hidden",
            "name" => "user_id",
            "opciones" => "",
            "value" => Auth::user()->id,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        return $lista_campos;
    }
}
