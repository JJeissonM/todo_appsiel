<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Database\Eloquent\Model;

use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;

use App\Calificaciones\Logro;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Periodo;
use App\Calificaciones\Boletin;
use App\Calificaciones\Calificacion;
use App\Calificaciones\ObservacionesBoletin;
use App\Calificaciones\ObservacionIngresada;

use App\AcademicoDocente\CursoTieneDirectorGrupo;


use App\Core\Colegio;

use Input;
use DB;
use PDF;
use View;
use Auth;

class ObservacionBoletinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //determinar la cantidad de registros a mostrar
        $nro_registros = 10;
        $temp = Input::get('nro_registros');
        if ($temp != null) {
            $nro_registros = $temp;
        }
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $id_colegio = $colegio->id;

        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS campo4';

        $user = Auth::user();

        if ($user->hasRole('SuperAdmin') || $user->hasRole('Admin Colegio') || $user->hasRole('Colegio - Vicerrector')) {
            $registros = ObservacionesBoletin::consultar_registros($nro_registros);
        } else {
            $registros = ObservacionesBoletin::consultar_registros_director_grupo();
        }

        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Observaciones boletín']
        ];

        $titulo_tabla = '';

        $id_app = Input::get('id');
        $id_modelo = Input::get('id_modelo');

        $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año', 'Periodo', 'Curso', 'Estudiante', 'Puesto', 'Observación'];

        $url_crear = 'calificaciones/observaciones_boletin/create?id=' . Input::get('id');
        $url_edit = '';
        $url_print = '';
        $url_ver = '';
        $url_estado = '';
        $url_eliminar = '';

        $source = "BOLETIN";

        return view('layouts.index', compact('registros', 'nro_registros', 'source', 'id_app', 'id_modelo', 'miga_pan', 'url_crear', 'titulo_tabla', 'encabezado_tabla', 'url_crear', 'url_edit', 'url_print', 'url_ver', 'url_estado', 'url_eliminar'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $user = Auth::user();
        $colegio = Colegio::where('empresa_id', $user->empresa_id)->get()->first();

        // SELECT DE CURSOS
        $opciones1 = Curso::where(['id_colegio' => $colegio->id, 'estado' => 'Activo'])
            ->OrderBy('nivel_grado')->get();

        $vec1[''] = '';

        if ($user->hasRole('SuperAdmin') || $user->hasRole('Admin Colegio') || $user->hasRole('Colegio - Vicerrector')) {
            foreach ($opciones1 as $opcion) {
                $vec1[$opcion->id] = $opcion->descripcion;
            }
        } else {
            foreach ($opciones1 as $opcion) {
                $esta = CursoTieneDirectorGrupo::where('curso_id', $opcion->id)->where('user_id', $user->id)->get()->first();

                if (!is_null($esta)) {
                    $vec1[$opcion->id] = $opcion->descripcion;
                }
            }
        }

        $cursos = $vec1;


        // SELECT PERIODOS
        $periodos = Periodo::opciones_campo_select();

        $miga_pan = [
            ['url' => 'calificaciones/observaciones_boletin?id=' . Input::get('id'), 'etiqueta' => 'Observaciones boletín'],
            ['url' => 'NO', 'etiqueta' => 'Ingresar']
        ];

        return view('calificaciones.boletines.observaciones_create', compact('cursos', 'periodos', 'miga_pan'));

        // Lo datos del formulario observaciones_create se envía vía post al método observaciones_create2
    }


    /**
     * Llamar al formulario de Ingreso/Edición de observaciones.
     *
     */
    public function observaciones_create2(Request $request)
    {
        // Se obtienen los estudiantes con matriculas activas en el curso y año indicado
        $periodo = Periodo::find($request->id_periodo);
        $anio = explode("-", $periodo->fecha_desde)[0];

        $estudiantes = Matricula::estudiantes_matriculados($request->curso_id, $periodo->periodo_lectivo_id, 'Activo');

        // Se obtienen las descripciones del curso y el perioro
        $nom_curso = Curso::find($request->curso_id)->descripcion;

        $nom_periodo = $periodo->descripcion;


        $miga_pan = [
            ['url' => 'calificaciones/observaciones_boletin?id=' . Input::get('id'), 'etiqueta' => 'Observaciones boletín'],
            ['url' => 'calificaciones/observaciones_boletin/create?id=' . Input::get('id'), 'etiqueta' => 'Ingresar'],
            ['url' => 'NO', 'etiqueta' => 'Periodo: ' . $nom_periodo]
        ];

        // Verificar si ya tiene observaciones para los datos seleccionados (anio-periodo-curso-asignatura)
        $observaciones = ObservacionIngresada::cantidad_x_periodo_curso($request->id_periodo, $request->curso_id);

        if ($observaciones > 0) {
            // SI ya tienen observaciones, se modifican
            $vec_estudiantes = array();
            $i = 0;
            foreach ($estudiantes as $estudiante) {
                $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
                $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo;

                $observacion_est = ObservacionesBoletin::get_x_estudiante($request->id_periodo, $request->curso_id, $estudiante->id_estudiante);

                $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;
                $vec_estudiantes[$i]['id_observacion'] = "no";
                $vec_estudiantes[$i]['observacion'] = "";

                if (!is_null($observacion_est)) {
                    $vec_estudiantes[$i]['id_observacion'] = $observacion_est->id;
                    $vec_estudiantes[$i]['observacion'] = $observacion_est->observacion;
                }

                $i++;
            }

            return view('calificaciones.boletines.observaciones_editar1', [
                'vec_estudiantes' => $vec_estudiantes,
                'cantidad_estudiantes' => count($estudiantes),
                'anio' => $anio,
                'curso_id' => $request->curso_id,
                'nom_curso' => $nom_curso,
                'id_periodo' => $request->id_periodo,
                'nom_periodo' => $nom_periodo,
                'miga_pan' => $miga_pan
            ]);
        } else {
            // Si no tienen observaciones, se crean por primera vez
            return view('calificaciones.boletines.observaciones_hacer2', [
                'estudiantes' => $estudiantes,
                'anio' => $anio,
                'curso_id' => $request->curso_id,
                'nom_curso' => $nom_curso,
                'id_periodo' => $request->id_periodo,
                'nom_periodo' => $nom_periodo,
                'miga_pan' => $miga_pan
            ]);
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();

        $datos = ['id_colegio' => $colegio->id] +
            ['anio' => $request->anio] +
            ['id_periodo' => $request->id_periodo] +
            ['curso_id' => $request->curso_id];

        // Guardar en tabla auxiliar para indicar que ya se ingresaron observaciones o puestos
        // del curso en de ese año-periodo. 
        // Esta tabla es para saber si se están creando los registros por primera vez 
        // de observaciones o puesto; para determinar si se van a INSERTAR o ACTUALIZAR
        ObservacionIngresada::create($datos);

        // Guardar las observaciones para cada estudiante
        for ($i = 0; $i < $request->cantidad_estudiantes; $i++) {
            ObservacionesBoletin::create(
                $datos +
                    ['codigo_matricula' => $request->input('codigo_matricula.' . $i)] +
                    ['id_estudiante' => $request->input('estudiante.' . $i)] +
                    ['observacion' => $request->input('observacion.' . $i)]
            );
        }

        $curso = Curso::find($request->curso_id);

        return redirect('/calificaciones/observaciones_boletin/create?id=' . $request->id_app)->with('flash_message', 'Observaciones ingresadas correctamente. Curso: ' . $curso->descripcion);
    }

    // AJAX para guardar una sola observación
    public function guardar_observacion(Request $request)
    {
        $observacion = ObservacionesBoletin::find($request->observacion_id);

        if (is_null($observacion)) {
            // Crear nueva
            $observacion = ObservacionesBoletin::create($request->all());
        } else {
            // Actualizar
            $observacion->fill($request->all());
            $observacion->save();
        }

        return [$observacion->id];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();

        for ($i = 0; $i < $request->cantidad_estudiantes; $i++) {
            if ($request->input('id_observacion.' . $i) != "no") {
                ObservacionesBoletin::where(['id' => $request->input('id_observacion.' . $i)])
                    ->update(['observacion' => $request->input('observacion.' . $i)]);
            } else {

                $datos = ['id_colegio' => $colegio->id] +
                    ['anio' => $request->anio] +
                    ['id_periodo' => $request->id_periodo] +
                    ['curso_id' => $request->curso_id];


                ObservacionesBoletin::create(
                    $datos +
                        ['codigo_matricula' => $request->input('codigo_matricula.' . $i)] +
                        ['id_estudiante' => $request->input('estudiante.' . $i)] +
                        ['observacion' => $request->input('observacion.' . $i)]
                );
            }
        }

        $curso = Curso::find($request->curso_id);

        return redirect('/calificaciones/observaciones_boletin/create?id=' . $request->id_app)->with('flash_message', 'Observaciones ingresadas correctamente. Curso: ' . $curso->descripcion);
    }
}
