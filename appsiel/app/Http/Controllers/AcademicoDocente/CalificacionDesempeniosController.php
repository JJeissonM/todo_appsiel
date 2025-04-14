<?php

namespace App\Http\Controllers\AcademicoDocente;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Matriculas\ObservadorEstudianteController;

use App\AcademicoDocente\AsignacionProfesor;

use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\Asignatura;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CalificacionAuxiliar;
use App\Calificaciones\CalificacionDesempenio;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\EncabezadoCalificacion;
use App\Calificaciones\Periodo;
use App\Calificaciones\Logro;
use App\Calificaciones\NotaNivelacion;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Services\AsignaturasService;
use App\Matriculas\CatalogoAspecto;
use App\Matriculas\TiposAspecto;
use App\Matriculas\AspectosObservador;
use App\Matriculas\NovedadesObservador;
use App\Matriculas\FodaEstudiante;


use App\Core\Colegio;
use App\Matriculas\Services\ObservadorEstudianteService;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class CalificacionDesempeniosController extends Controller
{

    protected $escala_valoracion;
    protected $colegio, $aplicacion;

    public function __construct()
    {
        $this->middleware('auth');

        $this->aplicacion = Aplicacion::find(Input::get('id'));

        if (Auth::check()) {
            $this->colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();
        }
    }


    //Selección de datos para calificar
    public function calificar_desempenios1($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $cursos = Curso::opciones_campo_select();

        $periodos = Periodo::opciones_campo_select();

        $periodo_lectivo_id = null;
        
        $periodo_lectivo_actual = PeriodoLectivo::get_actual();
        if ($periodo_lectivo_actual != null) {
            $periodo_lectivo_id = $periodo_lectivo_actual->id;
        }

        $registros_asignaturas = (new AsignaturasService())->get_asignaturas_del_curso_por_usuario(  $curso_id, $periodo_lectivo_id, 'Activo' );

        $asignaturas[''] = '';
        foreach ($registros_asignaturas as $opcion)
        {
            $asignaturas[$opcion->asignatura->id] = $opcion->asignatura->descripcion;
        }

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar calificaciones']
        ];

        return view('academico_docente.calificar_desempenios.create_calificacion', compact('periodos', 'cursos', 'miga_pan', 'periodo_lectivo_id', 'curso_id', 'asignatura_id', 'asignaturas'));
    }

    /**
     * Llamar vía AJAX al formulario de Ingreso/Edición de calificaciones.
     *
     */
    public function calificar2(Request $request)
    {
        $periodo = Periodo::find($request->id_periodo);
        $datos_asignatura = CursoTieneAsignatura::get_datos_asignacion($periodo->periodo_lectivo_id, $request->curso_id, $request->id_asignatura);

        if (is_null($datos_asignatura)) {
            return redirect()->back()->with('mensaje_error', 'Hay problemas en la asignación de la asignatura al curso. Consulte con el administrador.');
        }

        // Warning!!!! El año se toma del periodo.
        $anio = explode("-", $periodo->fecha_desde)[0];

        $periodo_lectivo = PeriodoLectivo::find($periodo->periodo_lectivo_id);

        // Se obtienen los estudiantes con matriculas activas en el curso y el periodo lectivo
        $estudiantes = Matricula::estudiantes_matriculados($request->curso_id, $periodo->periodo_lectivo_id, 'Activo');

        // Warning!!! No usar funciones de Eloquent en el controller (acoplamiento al framework) 
        $curso = Curso::find($request->curso_id);

        $creado_por = Auth::user()->email;
        $modificado_por = '';

        // Se crea un array con los valores de las calificaciones de cada estudiante
        $vec_estudiantes = array();
        $i = 0;
        foreach ($estudiantes as $estudiante) {
            $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
            $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo; //." ".$estudiante->apellido2." ".$estudiante->nombres;
            $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;
            $vec_estudiantes[$i]['matricula_id'] = $estudiante->id;
            $vec_estudiantes[$i]['id_calificacion'] = "no";
            $vec_estudiantes[$i]['calificacion'] = 0;
            $vec_estudiantes[$i]['logros'] = '';
            $vec_estudiantes[$i]['id_calificacion_aux'] = "no";
            for ($c = 1; $c < 16; $c++) {
                $key = "C" . $c;
                $vec_estudiantes[$i][$key] = 0;
            }

            // Se verifica si cada estudiante tiene calificación creada
            $calificacion_est = Calificacion::where([
                'anio' => $anio,
                'id_periodo' => $request->id_periodo,
                'curso_id' => $request->curso_id,
                'id_asignatura' => $request->id_asignatura,
                'id_estudiante' => $estudiante->id_estudiante
            ])
                ->get()
                ->first();

            // Si el estudiante tiene calificacion se envian los datos de esta para editarp
            if ($calificacion_est != null) {
                $creado_por = $calificacion_est->creado_por;
                $modificado_por = Auth::user()->email;
                // Obtener la calificación auxiliar del estudiante
                $calificacion_aux = CalificacionAuxiliar::where([
                    'anio' => $anio,
                    'id_periodo' => $request->id_periodo,
                    'curso_id' => $request->curso_id,
                    'id_asignatura' => $request->id_asignatura,
                    'id_estudiante' => $estudiante->id_estudiante
                ])
                    ->get()
                    ->first();

                if (is_null($calificacion_aux)) {
                    $obj_calificacion_aux = new CalificacionAuxiliar;

                    $calificacion_aux = $obj_calificacion_aux->get_registro_vacio();
                }

                $vec_estudiantes[$i]['id_calificacion'] = $calificacion_est->id;
                $vec_estudiantes[$i]['calificacion'] = $calificacion_est->calificacion;
                $vec_estudiantes[$i]['logros'] = $calificacion_est->logros;
                $vec_estudiantes[$i]['id_calificacion_aux'] = $calificacion_aux->id;

                for ($c = 1; $c < 16; $c++) {
                    $key = "C" . $c;
                    $vec_estudiantes[$i][$key] = $calificacion_aux->$key;
                }
            }
            $i++;
        }

        $escalas_valoracion = EscalaValoracion::opciones_campo_select();

        $pesos_encabezados = EncabezadoCalificacion::where([
            ['periodo_id', '=', $request->id_periodo],
            ['curso_id', '=', $request->curso_id],
            ['asignatura_id', '=', $request->id_asignatura],
            ['peso', '>', 0]
        ])
            ->select('columna_calificacion', 'peso')
            ->orderBy('columna_calificacion')
            ->get();

        $array_pesos = array_fill(0, 16, 0);
        $hay_pesos = false;
        $suma_porcentajes = 0;
        foreach ($pesos_encabezados as $peso_encabezado) {
            $array_pesos[(int)str_replace('C', '', $peso_encabezado->columna_calificacion)] = $peso_encabezado->peso;
            $hay_pesos = true;
            $suma_porcentajes += $peso_encabezado->peso;
        }

        $logros = Logro::where([
            'periodo_id' => $request->id_periodo,
            'curso_id' => $request->curso_id,
            'asignatura_id' => $request->id_asignatura,
            'escala_valoracion_id' => 0
        ])->get();

        $todas_las_calificaciones = CalificacionDesempenio::where([
            'periodo_id' => $request->id_periodo,
            'curso_id' => $request->curso_id,
            'asignatura_id' => $request->id_asignatura
        ])->get();

        return view('academico_docente.calificar_desempenios.calificar2', [
            'vec_estudiantes' => $vec_estudiantes,
            'cantidad_estudiantes' => count($estudiantes),
            'todas_las_calificaciones' => $todas_las_calificaciones,
            'anio' => $anio,
            'curso' => $curso,
            'periodo' => $periodo,
            'logros' => $logros,
            'periodo_lectivo' => $periodo_lectivo,
            'datos_asignatura' => $datos_asignatura,
            'ruta' => $request->ruta,
            'escalas_valoracion' => $escalas_valoracion,
            'array_pesos' => $array_pesos,
            'hay_pesos' => $hay_pesos,
            'suma_porcentajes' => $suma_porcentajes,
            'creado_por' => $creado_por,
            'modificado_por' => $modificado_por,
            'id_colegio' => $this->colegio->id
        ]);
    }

    public function almacenar_linea_calificacion_estudiante($periodo_id, $curso_id, $asignatura_id, $matricula_id, $logro_id, $escala_valoracion_id)
    {
        $calificacion = CalificacionDesempenio::where([
            'periodo_id' => $periodo_id,
            'curso_id' => $curso_id,
            'asignatura_id' => $asignatura_id,
            'matricula_id' => $matricula_id,
            'logro_id' => $logro_id
        ])->get()->first();

        if ((int)$escala_valoracion_id == 0)
        {
            if ( $calificacion == null ) {
                return response()->json([
                    'icon' => "info",
                    'title' => "Transacción Exitosa!",
                    'text' => 'Sin cambios.'
                ]);
            }

            $calificacion->delete();
            return response()->json([
                'icon' => "warning",
                'title' => "Transacción Exitosa!",
                'text' => 'Calificación eliminada.'
            ]);

        }else{

            if ( $calificacion == null ) {

                $calificacion = CalificacionDesempenio::create([
                    'periodo_id' => $periodo_id,
                    'curso_id' => $curso_id,
                    'asignatura_id' => $asignatura_id,
                    'matricula_id' => $matricula_id,
                    'logro_id' => $logro_id,
                    'escala_valoracion_id' => (int)$escala_valoracion_id,
                    'creado_por' => Auth::user()->email
                ]);

                return response()->json([
                    'icon' => "success",
                    'title' => "Transacción Exitosa!",
                    'text' => 'Nueva Calificación almacenada.'
                ]);
            }

            $calificacion->escala_valoracion_id = (int)$escala_valoracion_id;
            $calificacion->modificado_por = Auth::user()->email;
            $calificacion->save();

        }
        
        return response()->json([
            'icon' => "info",
            'title' => "Transacción Exitosa!",
            'text' => 'Calificación actualizada.'
        ]);
    }

    public function revisar_calificaciones($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        //determinar la cantidad de registros a mostrar
        $nro_registros = 10;
        $temp = Input::get('nro_registros');
        if ($temp != null) {
            $nro_registros = $temp;
        }
        //determinar la busqueda
        $search = "";
        $temp2 = Input::get('search');
        if ($temp2 != null) {
            $search = trim($temp2);
        }

        $registros = Calificacion::get_calificaciones($colegio->id, $curso_id, $asignatura_id, null, $nro_registros, $search);

        $miga_pan = [
            ['url' => 'academico_docente?id=' . Input::get('id'), 'etiqueta' => 'Académico docente'],
            ['url' => 'NO', 'etiqueta' => 'Calificaciones']
        ];

        $source = "INDEX4";
        $id_app = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $url_ver = null;
        $url_estado = null;
        $url_print = null;
        $url_edit = null;
        $url_eliminar = null;
        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $sqlString = Calificacion::sqlString($colegio->id, $curso_id, $asignatura_id, null, $search);
        $tituloExport = Calificacion::tituloExport();

        $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año', 'Periodo', 'Curso', 'Estudiante', 'Asignatura', 'Calificación'];

        return view('layouts.index', compact('registros', 'curso', 'asignatura', 'url_eliminar', 'url_edit', 'nro_registros', 'id_app', 'id_modelo', 'url_ver', 'url_estado', 'url_print', 'source', 'search', 'sqlString', 'tituloExport', 'encabezado_tabla', 'miga_pan'));
    }
}
