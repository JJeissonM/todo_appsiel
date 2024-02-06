<?php

namespace App\Http\Controllers\AcademicoDocente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Sistema\Html\MigaPan;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;

use App\User;

use App\Matriculas\Curso;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;
use App\AcademicoDocente\AsignacionProfesor;
use App\AcademicoDocente\Profesor;
use App\Core\Colegio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

/*
        CARGA ACADÉMICA
*/
class AsignacionProfesorController extends Controller
{
	
    protected $app, $modelo, $transaccion, $variables_url, $colegio;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function set_variables_globales()
    {
        $this->app = Aplicacion::find( Input::get('id') );
        $this->modelo = Modelo::find( Input::get('id_modelo') );

        $this->colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();

        $this->variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
    }

    /**
     * Muestra el formulario para asignar la carga académica al profesor.
     *
     */
    public function create_asignacion( $user_id )
    {
        $this->set_variables_globales();
        
        $profesores = Profesor::opciones_campo_select();

        $profesor = User::findOrFail( $user_id );
        $periodo_lectivo = PeriodoLectivo::get_actual();
        
        $listado_asignaciones = AsignacionProfesor::get_asignaturas_x_curso( $user_id, $periodo_lectivo->id );

        $periodos_lectivos = PeriodoLectivo::get_array_activos();

        $cursos = Curso::opciones_campo_select();

        $miga_pan = MigaPan::get_array( $this->app, $this->modelo, 'Asignación carga académica' );

        return view('academico_docente.profesores.create_asignacion',compact( 'periodos_lectivos','periodo_lectivo', 'profesor', 'listado_asignaciones', 'cursos', 'miga_pan', 'profesores'));
    }

    /*
        La tabla de asignaciones del profesor (user)
        Y las opciones para el input select de asignaturas pendientes (No asignadas del curso)
    */
    public function get_tabla_carga_academica( $user_id, $periodo_lectivo_id = null )
    {
        if ( is_null( $periodo_lectivo_id ) )
        {
            $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        }

        $periodo_lectivo = PeriodoLectivo::find( $periodo_lectivo_id );
        $profesor = User::find( $user_id );

        $listado_asignaciones = AsignacionProfesor::get_asignaturas_x_curso( $user_id, $periodo_lectivo_id );

        return View::make( 'academico_docente.profesores.asignacion_academica_tabla', compact( 'periodo_lectivo', 'profesor', 'listado_asignaciones' ) )->render();
    }

    /**
     * Buscar asignaturas para llenar select
     *
     */
    public function buscar_asignaturas( $curso_id, $periodo_lectivo_id)
    {
        return AsignacionProfesor::get_opciones_select_asignaturas_pendientes( $periodo_lectivo_id, $curso_id );
    }

    /**
     * Guardar asignación del PROFESOR
     *
     */
    public function guardar_asignacion(Request $request)
    {
        $verificar = AsignacionProfesor::get_asignacion_x_user_curso_asignatura( $request->id_user, $request->curso_id, $request->id_asignatura, $request->periodo_lectivo_id );

        if ( is_null( $verificar ) )
        {
            $asignacion = AsignacionProfesor::create( $request->all() );

            $intensidad_horaria = CursoTieneAsignatura::intensidad_horaria_asignatura_curso( $request->periodo_lectivo_id, $request->curso_id, $request->id_asignatura );

            $fila = View::make( 'academico_docente.profesores.asignacion_academica_tabla_fila', [
                                                                'curso_descripcion' => Curso::find( $request->curso_id )->descripcion,
                                                                'asignatura_descripcion' => Asignatura::find( $request->id_asignatura )->descripcion,
                                                                'intensidad_horaria' => $intensidad_horaria, 
                                                                'asignacion_id' => $asignacion->id
                                                            ] )
                        ->render();

        }else{
            $fila = 'No';
            $intensidad_horaria = 0;
        }

        return [$fila,$intensidad_horaria];
    }

    /**
     * Elimina una asignación y genera el listado de las asignaciones restantes para el profesor.
     *
     */
    public function eliminar_asignacion($user_id)
    {
        $asignacion = AsignacionProfesor::find( $user_id );

        $ih = CursoTieneAsignatura::intensidad_horaria_asignatura_curso( $asignacion->periodo_lectivo_id, $asignacion->curso_id, $asignacion->id_asignatura );

        $asignacion->delete();

        return $ih;
    }

    /**
     * Revisar las asignaciones por curso
     *
     */
    public function revisar_asignaciones()
    {
        $this->set_variables_globales();

        $todas_las_asignaturas_x_cursos = CursoTieneAsignatura::asignaturas_del_curso( null, null, 'todos' );

        $i=0;
        $listado = [];
        foreach( $todas_las_asignaturas_x_cursos as $fila)
        {
            $listado[$i]['curso_descripcion'] = $fila->curso_descripcion;
            $listado[$i]['periodo_lectivo_descripcion'] = $fila->periodo_lectivo_descripcion;
            $listado[$i]['asignatura_descripcion'] = $fila->descripcion;
            $listado[$i]['asignatura_intensidad_horaria'] = $fila->intensidad_horaria;
            
            $user = AsignacionProfesor::get_user_segun_curso_asignatura( $fila->curso_id, $fila->asignatura_id, $fila->periodo_lectivo_id );

            if ( !is_null( $user ) )
            {
                $listado[$i]['profesor'] = $user->name;
            }else{
                $listado[$i]['profesor'] = 'No';
            }

            $i++;
        }


        $miga_pan = MigaPan::get_array( $this->app, $this->modelo, 'Revisar Asignaciones de carga académica' );

        return view('academico_docente.profesores.revisar_asignaciones',compact('listado','miga_pan'));
    }


    /**
     * Formulario para Copiar todas las asignaciones de un periodo lectivo a otro
     *
     */
    public function copiar_carga_academica()
    {
        $periodos_lectivos = PeriodoLectivo::get_array_activos();

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Copiar carga académica']
                    ];

        return view('academico_docente.profesores.copiar_carga_academica',compact( 'periodos_lectivos', 'miga_pan' ) );
    }

    public function periodo_lectivo_tiene_carga_academica( $periodo_lectivo_id )
    {
        $carga_academica = AsignacionProfesor::get_asignaturas_x_curso( null, $periodo_lectivo_id );

        if ( !empty( $carga_academica->toArray() ) )
        {
            return 1; // Ya hay asignaciones para el periodo lectivo
        }

        return 0;
    }

    public function copiar_carga_academica_procesar( $periodo_lectivo_origen_id, $periodo_lectivo_destino_id )
    {
        $asignaciones = AsignacionProfesor::get_asignaturas_x_curso( null, $periodo_lectivo_origen_id );

        foreach( $asignaciones as $fila )
        {
            $fila->periodo_lectivo_id = $periodo_lectivo_destino_id;

            AsignacionProfesor::create( $fila->toArray() );
        }

        return 1;
    }


    public function get_carga_academica( $user_id )
    {
        if ( $user_id = "null" ) // "null" es un string
        {
            $user_id = Auth::user()->id;
        }

        $asignaciones = AsignacionProfesor::get_asignaturas_x_curso( $user_id );
        
        $datos = '{';
        $primero = true;
        $i = 0;
        foreach ($asignaciones as $fila)
        {
            if ( $primero) {
                $datos .= '"'.$i.'":{"curso_id":"'.$fila->curso_id.'","asignatura_id":"'.$fila->asignatura_id.'"}';
                $primero = false;
            }else{
                $datos .= ', "'.$i.'":{"curso_id":"'.$fila->curso_id.'","asignatura_id":"'.$fila->asignatura_id.'"}';
            }
            $i++;
        }
        $datos .= '}';

        return $datos;
    }
}