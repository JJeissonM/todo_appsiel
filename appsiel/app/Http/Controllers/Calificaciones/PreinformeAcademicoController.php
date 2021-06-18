<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\Sistema\ModeloController;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Periodo;

use App\Calificaciones\PreinformeAcademico;


use App\Matriculas\Matricula;
use App\Calificaciones\Area;
use App\Calificaciones\Logro;

use App\Core\Colegio;
use App\Sistema\Aplicacion;

use Input;
use DB;
use PDF;
use View;
use Auth;
use Lava;

class PreinformeAcademicoController extends ModeloController
{
    protected $escala_valoracion;
    protected $colegio;


    /**
     * Llamar al formulario de Ingreso/Edición de calificaciones.
     *
     */
    public function crear(Request $request)
    {        
        $this->colegio = Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();

        $periodo = Periodo::find( $request->id_periodo );

        $periodo_lectivo = PeriodoLectivo::find( $periodo->periodo_lectivo_id );

        // Se obtienen los estudiantes con matriculas activas en el curso y el periodo lectivo
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, 'Activo'  );

        // Warning!!! No usar funciones de Eloquent en el controller (acoplamiento al framework) 
        $curso = Curso::find($request->curso_id);
        
        $datos_asignatura = CursoTieneAsignatura::get_datos_asignacion( $periodo->periodo_lectivo_id, $request->curso_id, $request->id_asignatura );

        if ( is_null( $datos_asignatura ) ) 
        {
            return redirect()->back()->with('mensaje_error', 'Hay problemas en la asignación de la asignatura al curso. Consulte con el administrador. Curso: '.$curso->descripcion. ', Asinatura: NO ENCONTRADA ');
        }

        $creado_por = Auth::user()->email;
        $modificado_por = ''; 

        // Se crea un array con los valores de las anotaciones de cada estudiante
        $vec_estudiantes = array();
        $i=0;
        foreach($estudiantes as $estudiante)
        {
            $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
            $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo;

            $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;
            $vec_estudiantes[$i]['id_anotacion'] = "no";
            $vec_estudiantes[$i]['anotacion'] = '';

            // Se verifica si cada estudiante tiene calificación creada
            $anotacion_est = PreinformeAcademico::where( [ 'id_periodo'=>$request->id_periodo,
                                'curso_id'=>$request->curso_id,'id_asignatura'=>$request->id_asignatura,
                                'id_estudiante'=>$estudiante->id_estudiante])
                                ->get()
                                ->first();
            
            // Si el estudiante tiene anotacion se envian los datos de esta para editar
            if( !is_null($anotacion_est) )
            {
                $creado_por = $anotacion_est->creado_por;
                $modificado_por = Auth::user()->email;
                
                $vec_estudiantes[$i]['id_anotacion'] = $anotacion_est->id;
                $vec_estudiantes[$i]['anotacion'] = $anotacion_est->anotacion;

            }
            $i++;
        }

        $id_app = $request->id_app;

        $miga_pan = [
                        [ 'url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta' => $this->aplicacion->descripcion ],
                        ['url'=>'NO','etiqueta'=>'Ingresar periodo '.$periodo->descripcion]
                    ];

        return view('calificaciones.preinformes_academicos.create',[ 'vec_estudiantes'=>$vec_estudiantes,
                'cantidad_estudiantes'=>count($estudiantes),
                'curso'=>$curso,
                'periodo'=>$periodo,
                'periodo_lectivo'=>$periodo_lectivo,
                'datos_asignatura'=>$datos_asignatura,
                'ruta'=>$request->ruta,
                'miga_pan'=>$miga_pan,
                'creado_por'=>$creado_por,
                'modificado_por'=>$modificado_por,
                'id_colegio'=>$this->colegio->id]);
        
    }

    // Recibe las anotaciones de una fila (un estudiante)
    public static function almacenar_anotacion(Request $request)
    {
        $id_anotacion = $request->id_anotacion;
        $anotacion_texto = $request->anotacion;

        // Se verifica si la calificación ya existe
        $anotacion = PreinformeAcademico::find( $request->id_anotacion );

        if ( is_null($anotacion) ) 
        {
            // Crear nuevos registros
            if ( $request->anotacion != '') 
            {
                $anotacion_creada = PreinformeAcademico::create( $request->all() );

                $id_anotacion = $anotacion_creada->id;
                $anotacion_texto = $anotacion_creada->anotacion;
            }

        }else{

            // Si la calificación ENVIADA está vacía, se borra de la BD
            if ( $request->anotacion == '') 
            {
                $anotacion->delete();
                $id_anotacion = 'no';
                $anotacion_texto = '';
            }else{

                // Si no, se actualizan la calificación y las auxiliares
                $anotacion->fill( $request->all() );
                $anotacion->save();
            }
        }

        return [ $id_anotacion, $anotacion_texto ];

    }
	

    // LLenar select dependiente
    public function get_select_periodos( $periodo_lectivo_id )
    {
        $registros = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $opcion)
        {
            $opciones .= '<option value="'.$opcion->id.'">'.$opcion->descripcion.'</option>';
        }

        return $opciones;
    }

    // LLenar select dependiente
    public function get_select_asignaturas( $curso_id, $periodo_id = null)
    {
        if ( is_null($periodo_id) )
        {
            $periodo_lectivo = PeriodoLectivo::get_actual();
        }else{
            $periodo_lectivo = PeriodoLectivo::get_segun_periodo( $periodo_id );
        }

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo->id );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($asignaturas as $campo) {
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

}