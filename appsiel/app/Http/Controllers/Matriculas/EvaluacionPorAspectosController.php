<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;
use NumerosEnLetras;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Empresa;
use App\Core\Colegio;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\CatalogoAspecto;
use App\Matriculas\ResultadoEvaluacionAspectoEstudiante;
use App\Matriculas\TiposAspecto;

use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Periodo;

use App\Calificaciones\Calificacion;
use App\Calificaciones\CalificacionAuxiliar;

use App\Calificaciones\EscalaValoracion;

use App\Matriculas\Matricula;
use App\Calificaciones\Area;
use App\Calificaciones\Logro;

use App\AcademicoDocente\AsignacionProfesor;
use App\AcademicoDocente\ConsolidadoEvaluacionAspectoEstudiante;

class EvaluacionPorAspectosController extends Controller
{
    public $array_convenciones = ['','Alto','Medio','Bajo'];

    public function __construct()
    {
        $this->middleware('auth');

        $this->aplicacion = Aplicacion::find(Input::get('id'));

        if (Auth::check()) {
            $this->colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();
        }
    }

    public function ingresar_valoracion( $curso_id, $asignatura_id, $fecha_valoracion )
    {
        $usuario = Auth::user();
        $periodo_lectivo = PeriodoLectivo::get_actual();

        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, $periodo_lectivo->id, null);

        $curso = Curso::find( $curso_id );
        $asignatura = Asignatura::find( $asignatura_id );

        $cursos_profesor = Curso::opciones_campo_select();
        $asignaturas_curso = CursoTieneAsignatura::opciones_select_asignaturas_del_curso( $curso->id, null, $periodo_lectivo->id, null );
        
        $datos_asignatura = CursoTieneAsignatura::get_datos_asignacion( $periodo_lectivo->id, $curso->id, $asignatura->id );

        if ( is_null($datos_asignatura) )
        {
            return redirect()->back()->with('mensaje_error', 'Hay problemas en la asignación de la asignatura al curso. Consulte con el administrador.');
        }

        // Se crea un array con los valores de las evaluaciones de cada estudiante
        $vec_estudiantes = array();
        $i = 0;

        $tipos_aspectos = TiposAspecto::where('estado', 'Activo')->get();
        $items_aspectos = CatalogoAspecto::where('estado', 'Activo')->orderBy('id_tipo_aspecto')->orderBy('orden')->get();

        $cantidad_items_aspectos = count( $items_aspectos->toArray() );

        foreach ($estudiantes as $estudiante)
        {
            $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
            $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo; //." ".$estudiante->apellido2." ".$estudiante->nombres;
            $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;

            $valoraciones_aspectos = [];
            foreach ( $items_aspectos as $item_aspecto )
            {
                $item_valoracion_est = ResultadoEvaluacionAspectoEstudiante::where([
                                                                                    'estudiante_id' => $estudiante->id_estudiante,
                                                                                    'asignatura_id' => $asignatura->id,
                                                                                    'item_aspecto_id' => $item_aspecto->id,
                                                                                    'fecha_valoracion' => $fecha_valoracion,
                                                                                ])
                                                                            ->get()
                                                                            ->first();
                
                //dd( [ $estudiante->id_estudiante, $asignatura->id, $item_aspecto->id, $fecha_valoracion, $item_valoracion_est ] );

                if( !is_null($item_valoracion_est) )
                {
                    $valoracion = $item_valoracion_est->convencion_valoracion_id;
                }else{
                    $valoracion = 0;
                }

                $key = "valores_item_" . $item_aspecto->id;
                $valoraciones_aspectos[$key] = $valoracion;
            }

            $vec_estudiantes[$i]['valoraciones_aspectos'] = $valoraciones_aspectos;
            $i++;
        }

        $convenciones = $this->array_convenciones;
        $creado_por = Auth::user()->email;
        $modificado_por = Auth::user()->email;

        $miga_pan = [
                        ['url' => $this->aplicacion->app . '?id=' . Input::get('id'), 'etiqueta' => $this->aplicacion->descripcion],
                        ['url' => 'NO', 'etiqueta' => 'Ingresos de valoraciones por aspectos de estudiantes.']
                    ];

        return view('matriculas.observador.evaluacion_por_aspectos.formulario', [
                                                    'vec_estudiantes' => $vec_estudiantes,
                                                    'cantidad_estudiantes' => count($estudiantes),
                                                    'tipos_aspectos' => $tipos_aspectos,
                                                    'cantidad_items_aspectos' => $cantidad_items_aspectos,
                                                    'items_aspectos' => $items_aspectos,
                                                    'convenciones' => $convenciones,
                                                    'curso' => $curso,
                                                    'cursos_profesor' => $cursos_profesor,
                                                    'asignaturas_curso' => $asignaturas_curso,
                                                    'asignatura' => $asignatura,
                                                    'fecha_valoracion' => $fecha_valoracion,
                                                    'periodo_lectivo' => $periodo_lectivo,
                                                    'datos_asignatura' => $datos_asignatura,
                                                    'miga_pan' => $miga_pan,
                                                    'creado_por' => $creado_por,
                                                    'modificado_por' => $modificado_por,
                                                    'id_colegio' => $this->colegio->id
                                                ]);
    }


    public function almacenar_valoracion(Request $request)
    {
        $estudiantes = $request->id_estudiante;

        foreach ($estudiantes as $key => $estudiante_id )
        {
            for( $c=1; $c <= $request->cantidad_items_aspectos; $c++ )
            {
                $variable_item = 'valores_item_'.$c;
                $valor_item = $request->$variable_item[$key];
                
                $item_valoracion_est = ResultadoEvaluacionAspectoEstudiante::where([
                                                                                'estudiante_id' => (int)$estudiante_id,
                                                                                'asignatura_id' => $request->id_asignatura,
                                                                                'item_aspecto_id' => $c,
                                                                                'fecha_valoracion' => $request->fecha_valoracion,
                                                                            ])
                                                                        ->get()
                                                                        ->first();
                if( is_null($item_valoracion_est) )
                {
                    if ( (int)$valor_item != 0 )
                    {
                        // Crear nuevo
                        ResultadoEvaluacionAspectoEstudiante::create([
                                                                        'estudiante_id' => (int)$estudiante_id,
                                                                        'asignatura_id' => $request->id_asignatura,
                                                                        'item_aspecto_id' => $c,
                                                                        'fecha_valoracion' => $request->fecha_valoracion,
                                                                        'convencion_valoracion_id' => (int)$valor_item,
                                                                        'creado_por' => $request->creado_por
                                                                    ]);
                    }
                }else{
                    if ( (int)$valor_item != 0 )
                    {
                        // Actualizar
                        $item_valoracion_est->convencion_valoracion_id = (int)$valor_item;
                        $item_valoracion_est->modificado_por = $request->modificado_por;
                        $item_valoracion_est->save();
                    }else{
                        $item_valoracion_est->delete();
                    }
                }
                
            }
        }       

        return redirect( 'sga_observador_evaluacion_por_aspectos_ingresar_valoracion/' . $request->curso_id . '/' . $request->id_asignatura . '/' . $request->fecha_valoracion . '?id=5' )->with('flash_message', 'Evaluación por aspectos ingresada correctamente.');
    }

    public function consolidar(Request $request)
    {
        $periodo_lectivo = PeriodoLectivo::get_actual();

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo_lectivo->id, null);

        $curso = Curso::find( $request->curso_id );
        $asignatura = Asignatura::find( $request->asignatura_id );

        $datos_asignatura = CursoTieneAsignatura::get_datos_asignacion( $periodo_lectivo->id, $curso->id, $asignatura->id );

        if ( is_null($datos_asignatura) )
        {
            return redirect()->back()->with('mensaje_error', 'Hay problemas en la asignación de la asignatura al curso. Consulte con el administrador.');
        }

        // Se crea un array con los valores de las evaluaciones de cada estudiante
        $vec_estudiantes = array();
        $i = 0;

        $tipos_aspectos = TiposAspecto::where('estado', 'Activo')->get();
        $items_aspectos = CatalogoAspecto::where('estado', 'Activo')->orderBy('id_tipo_aspecto')->orderBy('orden')->get();

        $cantidad_items_aspectos = count( $items_aspectos->toArray() );

        foreach ($estudiantes as $estudiante)
        {
            $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
            $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo; //." ".$estudiante->apellido2." ".$estudiante->nombres;
            $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;

            $valoraciones_aspectos = [];
            $valoraciones_aspectos_id = [];
            foreach ( $items_aspectos as $item_aspecto )
            {
                $valoracion = $this->get_consolidado_valoracion( $estudiante->id_estudiante, $asignatura->id, $item_aspecto->id, $request->fecha_desde, $request->fecha_hasta );

                $key = "valores_item_" . $item_aspecto->id;
                $valoraciones_aspectos[$key] = $valoracion->lbl_valoracion;
                $valoraciones_aspectos_id[] = $valoracion->value_valoracion;
            }

            $vec_estudiantes[$i]['valoraciones_aspectos'] = $valoraciones_aspectos;
            $vec_estudiantes[$i]['valoraciones_aspectos_ids'] = $valoraciones_aspectos_id;
            $i++;
        }

        $convenciones = ['','Alto','Medio','Bajo'];
        $creado_por = Auth::user()->email;
        $modificado_por = Auth::user()->email;

        $observaciones = [ '',
                            'Aclara y construye ideas del docente', 
                            'Analiza la información brindada, argumenta y genera debates*Asume sus deberes y responsabilidades con gusto y autonomía', 
                            'Demuestra iniciativa para trabajar en clases', 
                            'Emplea un lenguaje claro y preciso',
                            'Entrega tareas y trabajos a tiempo',
                            'Equilibra el equipo', 
                            'Es receptivo a las orientaciones dadas por el docente', 
                            'Está conectado con el grupo', 
                            'Facilita el trabajo en equipo',
                            'Hace uso correcto del micrófono y del chat', 
                            'Ingresa puntual al aula virtual',
                            'Mantiene el ambiente virtual sano', 
                            'Mantiene la atención y concentración en el aula de clases', 
                            'Mantiene la participación activa en el aula de clases', 
                            'Mantiene una adecuada presentación en el aula virtual',
                            'Muestra sentido de pertenecía por medio de actividades extracurriculares',
                            'Participa constantemente en clases', 
                            'Presenta el material de trabajo organizado',
                            'Promueve la discusión inteligente', 
                            'Respeta a la autoridad', 
                            'Respeta el turno de participación de los compañeros',
                            'Respeta las opiniones ajenas', 
                            'Se destaca por ser empático con los docentes y compañeros', 
                            'Se expresa correctamente', 
                            'Se muestra colaborativo en el aula virtual', 
                            'Se preocupa por la clase',
                            'Utiliza un trato amable y respetuoso hacia los compañeros y docentes'];

        return view('matriculas.observador.evaluacion_por_aspectos.consolidados', [
                                                    'vec_estudiantes' => $vec_estudiantes,
                                                    'cantidad_estudiantes' => count($estudiantes),
                                                    'tipos_aspectos' => $tipos_aspectos,
                                                    'cantidad_items_aspectos' => $cantidad_items_aspectos,
                                                    'items_aspectos' => $items_aspectos,
                                                    'convenciones' => $convenciones,
                                                    'curso' => $curso,
                                                    'asignatura' => $asignatura,
                                                    'observaciones' => $observaciones,
                                                    'fecha_desde' => $request->fecha_desde,
                                                    'fecha_hasta' => $request->fecha_hasta,
                                                    'periodo_lectivo' => $periodo_lectivo,
                                                    'datos_asignatura' => $datos_asignatura,
                                                    'creado_por' => $creado_por,
                                                    'modificado_por' => $modificado_por,
                                                    'id_colegio' => $this->colegio->id
                                                ]);
    }

    public function get_consolidado_valoracion( $estudiante_id, $asignatura_id, $item_aspecto_id, $fecha_desde, $fecha_hasta )
    {
        $valoraciones_est = ResultadoEvaluacionAspectoEstudiante::where([
                                                                            'estudiante_id' => $estudiante_id,
                                                                            'asignatura_id' => $asignatura_id,
                                                                            'item_aspecto_id' => $item_aspecto_id,
                                                                        ])
                                                                ->whereBetween('fecha_valoracion', [$fecha_desde, $fecha_hasta])
                                                                ->orderBy('fecha_valoracion','DESC')
                                                                ->take(3)  // Se toman las tres últimas valoraciones
                                                                ->get();
                                        //dd( $valoraciones_est );
        
        $array_valoracion = [];
        $title = '';
        $hay_alto = 0;
        $hay_medio = 0;
        $hay_bajo = 0;
        foreach ($valoraciones_est as $valoracion )
        {
            $title .= '***Fecha: ' . $valoracion->fecha_valoracion . ', Valoración: ' . $this->array_convenciones[ $valoracion->convencion_valoracion_id ] . '    '; 
            //$array_valoracion[] = $valoracion->convencion_valoracion_id;
            switch ( $valoracion->convencion_valoracion_id )
            {
                case '1':
                    $hay_alto++;
                break;
              
                case '2':
                    $hay_medio++;
                break;
              
                case '3':
                    $hay_bajo++;
                break;
              
                default:
                    break;
            }
        }

        $color_fondo = 'yellow';
        $color_texto = 'black';
        $lbl_valoracion = '--';
        $value_valoracion = 0;

        if ( $hay_alto == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( ($hay_alto == 1 || $hay_alto == 2 || $hay_alto == 3 ) && ($hay_medio == 0 || $hay_bajo == 0 ) )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( $hay_alto == 1 && $hay_medio == 2 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $this->array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( ($hay_medio == 1 || $hay_medio == 2 || $hay_medio == 3 ) && ($hay_alto == 0 || $hay_bajo == 0 ) )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $this->array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 1 && $hay_medio == 1 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $this->array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $this->array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_medio == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_alto == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( ($hay_bajo == 1 || $hay_bajo == 2 || $hay_bajo == 3 ) && ($hay_alto == 0 || $hay_medio == 0 ) )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        $valoracion = '<span style="background: ' . $color_fondo . '; color:' . $color_texto . ';" title="' . $title . '">' . $lbl_valoracion . '</span>';

        return (object)[ 'lbl_valoracion' => $valoracion, 'value_valoracion' => $value_valoracion ];
    }

    public function almacenar_consolidado(Request $request)
    {
        $estudiantes = $request->id_estudiante;

        foreach ($estudiantes as $key => $estudiante_id )
        {
            for( $c=1; $c <= $request->cantidad_items_aspectos; $c++ )
            {
                $variable_item = 'valores_item_'.$c;
                $valor_item = $request->$frecuencia[$key];

                //::create( [ 'estudiante_id', 'asignatura_id', 'convencion_valoracion_id_final', 'frecuencia', 'cantidad_dias', 'observacion', 'creado_por', 'modificado_por' ] );

                
                $valor_cosolidado_estudiante = ConsolidadoEvaluacionAspectoEstudiante::where([
                                                                                'estudiante_id' => (int)$estudiante_id,
                                                                                'asignatura_id' => $request->id_asignatura,
                                                                                'item_aspecto_id' => $c,
                                                                                'fecha_valoracion' => $request->fecha_valoracion,
                                                                            ])
                                                                        ->get()
                                                                        ->first();
                if( is_null($valor_cosolidado_estudiante) )
                {
                    if ( (int)$valor_item != 0 )
                    {
                        // Crear nuevo
                        ConsolidadoEvaluacionAspectoEstudiante::create([
                                                                        'estudiante_id' => (int)$estudiante_id,
                                                                        'asignatura_id' => $request->id_asignatura,
                                                                        'item_aspecto_id' => $c,
                                                                        'fecha_valoracion' => $request->fecha_valoracion,
                                                                        'convencion_valoracion_id' => (int)$valor_item,
                                                                        'creado_por' => $request->creado_por
                                                                    ]);
                    }
                }else{
                    if ( (int)$valor_item != 0 )
                    {
                        // Actualizar
                        $valor_cosolidado_estudiante->convencion_valoracion_id = (int)$valor_item;
                        $valor_cosolidado_estudiante->modificado_por = $request->modificado_por;
                        $valor_cosolidado_estudiante->save();
                    }else{
                        $valor_cosolidado_estudiante->delete();
                    }
                }
                
            }
        }       

        return redirect( 'sga_observador_evaluacion_por_aspectos_ingresar_valoracion/' . $request->curso_id . '/' . $request->id_asignatura . '/' . $request->fecha_valoracion . '?id=5' )->with('flash_message', 'Evaluación por aspectos ingresada correctamente.');
    }
   
}