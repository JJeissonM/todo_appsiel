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
use Cache;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Empresa;
use App\Core\Colegio;
use App\Core\SemanasCalendario;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\CatalogoAspecto;
use App\Matriculas\ResultadoEvaluacionAspectoEstudiante;
use App\Matriculas\ConsolidadoEvaluacionAspectoEstudiante;
use App\Matriculas\CatalogoObservacionesEvaluacionAspecto;
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

        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, $periodo_lectivo->id, 'Activo');
        $semana_calendario = SemanasCalendario::where([
                                                        ['fecha_inicio', '<=', $fecha_valoracion],
                                                        ['fecha_fin', '>=', $fecha_valoracion]
                                                    ])
                                                ->get()->first();/*all();*/
        //dd([$fecha_valoracion,$semana_calendario]);
        if ( is_null($semana_calendario) )
        {
            $semana_calendario = (object)['fecha_inicio'=>'0000-00-00','fecha_fin'=>'0000-00-00','descripcion'=>'NO ENCONTRADA'];
        }
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
            $fechas_valoraciones_aspectos = [];
            foreach ( $items_aspectos as $item_aspecto )
            {
                $item_valoracion_est = ResultadoEvaluacionAspectoEstudiante::where([
                                                                                    'estudiante_id' => $estudiante->id_estudiante,
                                                                                    'asignatura_id' => $asignatura->id,
                                                                                    'item_aspecto_id' => $item_aspecto->id
                                                                                ])
                                                                            ->whereBetween('fecha_valoracion',[$semana_calendario->fecha_inicio,$semana_calendario->fecha_fin])
                                                                            ->get()
                                                                            ->first();
                
                //dd( [ $estudiante->id_estudiante, $asignatura->id, $item_aspecto->id, $fecha_valoracion, $item_valoracion_est ] );

                if( !is_null($item_valoracion_est) )
                {
                    $valoracion = $item_valoracion_est->convencion_valoracion_id;
                    $fecha_valoracion_item = $item_valoracion_est->fecha_valoracion;
                }else{
                    $valoracion = 0;
                    $fecha_valoracion_item = $fecha_valoracion;
                }

                $key = "valores_item_" . $item_aspecto->id;
                $valoraciones_aspectos[$key] = $valoracion;
                $fechas_valoraciones_aspectos[$key] = $fecha_valoracion_item;
            }

            $vec_estudiantes[$i]['valoraciones_aspectos'] = $valoraciones_aspectos;
            $vec_estudiantes[$i]['fechas_valoraciones_aspectos'] = $fechas_valoraciones_aspectos;
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
                                                    'semana_calendario' => $semana_calendario,
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
                $fechas_valores_item = 'fechas_valores_item_'.$c;
                $valor_item = $request->$variable_item[$key];
                $fecha_valoracion = $request->$fechas_valores_item[$key];
                
                $item_valoracion_est = ResultadoEvaluacionAspectoEstudiante::where([
                                                                                'estudiante_id' => (int)$estudiante_id,
                                                                                'asignatura_id' => $request->id_asignatura,
                                                                                'item_aspecto_id' => $c,
                                                                                'fecha_valoracion' => $fecha_valoracion,
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
                                                                        'fecha_valoracion' => $fecha_valoracion,
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
        $semana_calendario = SemanasCalendario::find( $request->semana_calendario_id );
        $periodo_lectivo = $semana_calendario->periodo_lectivo;

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo_lectivo->id, 'Activo');

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
                $valoracion = $this->get_consolidado_valoracion( $estudiante->id_estudiante, $asignatura->id, $item_aspecto->id, $semana_calendario->fecha_inicio, $semana_calendario->fecha_fin );

                $key = "valores_item_" . $item_aspecto->id;
                $valoraciones_aspectos[$key] = $valoracion->lbl_valoracion;
                $valoraciones_aspectos_id[] = $valoracion->value_valoracion;
            }

            $vec_estudiantes[$i]['valoraciones_aspectos'] = $valoraciones_aspectos;
            $vec_estudiantes[$i]['valoraciones_aspectos_ids'] = $valoraciones_aspectos_id;
            $vec_estudiantes[$i]['observacion_id'] = $this->get_observacion( $estudiante->id_estudiante, $asignatura->id, $semana_calendario->id );
            $i++;
        }

        $convenciones = ['','Alto','Medio','Bajo'];
        $creado_por = Auth::user()->email;
        $modificado_por = Auth::user()->email;

        $observaciones = CatalogoObservacionesEvaluacionAspecto::opciones_campo_select();
        
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
                                                    'semana_calendario' => $semana_calendario,
                                                    'periodo_lectivo' => $periodo_lectivo,
                                                    'datos_asignatura' => $datos_asignatura,
                                                    'creado_por' => $creado_por,
                                                    'modificado_por' => $modificado_por,
                                                    'id_colegio' => $this->colegio->id
                                                ]);
    }

    public function reporte_consolidados(Request $request)
    {
        $semana_calendario = SemanasCalendario::find( $request->semana_calendario_id );
        $periodo_lectivo = $semana_calendario->periodo_lectivo;

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo_lectivo->id, 'Activo' );

        $curso = Curso::find( $request->curso_id );

        $asignaturas_del_curso = CursoTieneAsignatura::asignaturas_del_curso( $curso->id, null, $periodo_lectivo->id, null );

        if ( is_null($asignaturas_del_curso) )
        {
            return redirect()->back()->with('mensaje_error', 'Hay problemas en la asignación de la asignatura al curso. Consulte con el administrador.');
        }

        // Se crea un array con los valores de las evaluaciones de cada estudiante
        $vec_estudiantes = array();
        $i = 0;

        $tipos_aspectos = TiposAspecto::where('estado', 'Activo')->get();
        $items_aspectos = CatalogoAspecto::where('estado', 'Activo')->orderBy('id_tipo_aspecto')->orderBy('orden')->get();

        $cantidad_items_aspectos = count( $items_aspectos->toArray() );

        $view = '';
        foreach( $asignaturas_del_curso as $asignacion )
        {
            $cantidad_estudiantes = count($estudiantes);
            foreach ($estudiantes as $estudiante)
            {
                $observacion_id = $this->get_observacion( $estudiante->id_estudiante, $asignacion->asignatura->id, $semana_calendario->id );
                
                if ( $observacion_id == 0 )
                {
                    $cantidad_estudiantes--;
                    continue;
                }

                $vec_estudiantes[$i]['observacion_descripcion'] = CatalogoObservacionesEvaluacionAspecto::find( $observacion_id )->observacion;
                $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
                $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo; //." ".$estudiante->apellido2." ".$estudiante->nombres;
                $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;

                $valoraciones_aspectos = [];
                $valoraciones_aspectos_id = [];
                foreach ( $items_aspectos as $item_aspecto )
                {
                    $valoracion = $this->get_consolidado_valoracion( $estudiante->id_estudiante, $asignacion->asignatura->id, $item_aspecto->id, $semana_calendario->fecha_inicio, $semana_calendario->fecha_fin );

                    $key = "valores_item_" . $item_aspecto->id;
                    $valoraciones_aspectos[$key] = $valoracion->lbl_valoracion . '<br>Fecha: ' . $valoracion->fecha_valoracion;
                    if ( $valoracion->value_valoracion == 0 )
                    {
                        $valoraciones_aspectos[$key] = $valoracion->lbl_valoracion;
                    }
                    $valoraciones_aspectos_id[] = $valoracion->value_valoracion;
                }

                $vec_estudiantes[$i]['valoraciones_aspectos'] = $valoraciones_aspectos;
                $vec_estudiantes[$i]['valoraciones_aspectos_ids'] = $this->get_frecuencia( $valoraciones_aspectos_id, $i )->lbl_valoracion;
                $i++;
            }
            
            if ( $cantidad_estudiantes <= 0 )
            {
                continue;
            }

            $convenciones = ['','Alto','Medio','Bajo'];
            
            $view .= View::make( 'matriculas.observador.evaluacion_por_aspectos.reporte_consolidados', [
                                                        'vec_estudiantes' => $vec_estudiantes,
                                                        'cantidad_estudiantes' => $cantidad_estudiantes,
                                                        'tipos_aspectos' => $tipos_aspectos,
                                                        'cantidad_items_aspectos' => $cantidad_items_aspectos,
                                                        'items_aspectos' => $items_aspectos,
                                                        'convenciones' => $convenciones,
                                                        'curso' => $curso,
                                                        'descripcion_asignatura' => $asignacion->asignatura->descripcion,
                                                        'semana_calendario' => $semana_calendario,
                                                        'periodo_lectivo' => $periodo_lectivo,
                                                        'id_colegio' => $this->colegio->id
                                                    ]) ->render();
        }

        $font_size = 11;

        $vista = View::make( 'layouts.pdf3', compact('view','font_size') )->render();

        Cache::forever( 'pdf_reporte_consolidados_evaluacion_por_aspectos', $vista ); // Siempre debe empzar por "pdf_reporte_"

        return $view;
    }

    public function congratulations(Request $request)
    {
        $semana_calendario = SemanasCalendario::find( $request->semana_calendario_id );
        $periodo_lectivo = $semana_calendario->periodo_lectivo;

        $vec_asignaturas_profesor = AsignacionProfesor::get_asignaturas_x_curso( Auth::user()->id, $periodo_lectivo->id )->pluck('id_asignatura');

        $colegio = $this->colegio;

        $valores_consolidados_estudiantes = ConsolidadoEvaluacionAspectoEstudiante::where([
                                                                                            'valoracion_id_final' => 1,
                                                                                            'semana_calendario_id' => $request->semana_calendario_id,
                                                                                        ])
                                                                                    ->whereIn('asignatura_id',$vec_asignaturas_profesor)
                                                                                    ->orderBy('curso_id')
                                                                                    ->orderBy('estudiante_id')
                                                                                    //->orderBy('asignatura_id')
                                                                                    ->get();

        return view('matriculas.observador.evaluacion_por_aspectos.congratulations', [
                                                    'valores_consolidados_estudiantes' => $valores_consolidados_estudiantes,
                                                    'colegio' => $colegio,
                                                ]);
    }

    public function estadisticas_por_curso(Request $request)
    {
        $semana_calendario = SemanasCalendario::find( $request->semana_calendario_id );
        $periodo_lectivo = $semana_calendario->periodo_lectivo;

        $vec_cursos_profesor = AsignacionProfesor::get_asignaturas_x_curso( Auth::user()->id, $periodo_lectivo->id )->pluck('curso_id');

        $colegio = $this->colegio;

        foreach ( $vec_cursos_profesor as $key => $curso_id )
        {
            $vce = ConsolidadoEvaluacionAspectoEstudiante::where( 'semana_calendario_id', $request->semana_calendario_id )
                                                        ->where('curso_id', $curso_id)
                                                        ->selectRaw('count(valoracion_id_final) AS cantidad_valoracion, valoracion_id_final, curso_id')
                                                        ->groupBy('valoracion_id_final')
                                                        ->get();
            //if( $curso_id == 4 )
            if( !empty($vce->toArray() ) )
            {
                $this->grafica_valoracion_x_curso( $vce );
            }
        }
            

        return view('matriculas.observador.evaluacion_por_aspectos.congratulations', [
                                                    'valores_consolidados_estudiantes' => $valores_consolidados_estudiantes,
                                                    'colegio' => $colegio,
                                                ]);
    }

    public function grafica_valoracion_x_curso( $valores_consolidados )
    {
        $stocksTable = Lava::DataTable();
        
        $stocksTable->addStringColumn('Frecuencia')
                    ->addNumberColumn('Cantidad');
        
        foreach( $valores_consolidados as $frecuencia )
        {
            $stocksTable->addRow([
              $registro->Genero, (int)$registro->Cantidad
            ]);
        }

        Lava::PieChart('Generos', $stocksTable);
        
        return $generos;
    }

    public function get_observacion( $estudiante_id, $id_asignatura, $semana_calendario_id )
    {
        $valor_consolidado_estudiante = ConsolidadoEvaluacionAspectoEstudiante::where([
                                                                            'estudiante_id' => (int)$estudiante_id,
                                                                            'asignatura_id' => $id_asignatura,
                                                                            'semana_calendario_id' => $semana_calendario_id,
                                                                        ])
                                                                    ->get()
                                                                    ->first();
        if ( is_null($valor_consolidado_estudiante) )
        {
            return 0;
        }

        return $valor_consolidado_estudiante->observacion_id;
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
        
        $array_valoracion = [];
        $title = '';
        $una_fecha = '';
        $hay_alto = 0;
        $hay_medio = 0;
        $hay_bajo = 0;
        foreach ( $valoraciones_est as $valoracion )
        {
            $title .= '***Fecha: ' . $valoracion->fecha_valoracion . ', Valoración: ' . $this->array_convenciones[ $valoracion->convencion_valoracion_id ] . '    ';
            $una_fecha = $valoracion->fecha_valoracion;

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

        if ( ($hay_alto == 1 || $hay_alto == 2 || $hay_alto == 3 ) && $hay_medio == 0 && $hay_bajo == 0 )
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

        if ( ($hay_medio == 1 || $hay_medio == 2 || $hay_medio == 3 ) && $hay_alto == 0 && $hay_bajo == 0 )
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

        if ( ($hay_bajo == 1 || $hay_bajo == 2 || $hay_bajo == 3 ) && $hay_alto == 0 && $hay_medio == 0 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $this->array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        $valoracion_aux = '<span style="background: ' . $color_fondo . '; color:' . $color_texto . ';" title="' . $title . '">' . $lbl_valoracion . '</span>';

        return (object)[ 'lbl_valoracion' => $valoracion_aux, 'value_valoracion' => $value_valoracion, 'fecha_valoracion' => $una_fecha ];
    }

    /*
        valoracion_id_final = Frecuencia
    */
    public function almacenar_consolidado(Request $request)
    {
        $estudiantes = $request->id_estudiante;
        $fila = 0;
        foreach ($estudiantes as $key => $estudiante_id )
        {
            $valoracion_id_final = (int)$request->valoracion_id_final[$fila];
            $observacion_id = (int)$request->observacion_id[$fila];
            
            $valor_cosolidado_estudiante = ConsolidadoEvaluacionAspectoEstudiante::where([
                                                                            'estudiante_id' => (int)$estudiante_id,
                                                                            'asignatura_id' => $request->id_asignatura,
                                                                            'curso_id' => $request->curso_id,
                                                                            'semana_calendario_id' => $request->semana_calendario_id,
                                                                        ])
                                                                    ->get()
                                                                    ->first();
            if( is_null($valor_cosolidado_estudiante) )
            {
                if ( $observacion_id != 0 )
                {
                    // Crear nuevo
                    ConsolidadoEvaluacionAspectoEstudiante::create([
                                                                    'estudiante_id' => (int)$estudiante_id,
                                                                    'curso_id' => $request->curso_id,
                                                                    'asignatura_id' => $request->id_asignatura,
                                                                    'semana_calendario_id' => $request->semana_calendario_id,
                                                                    'valoracion_id_final' => $valoracion_id_final,
                                                                    'observacion_id' => $observacion_id,
                                                                    'creado_por' => $request->creado_por
                                                                ]);
                }
            }else{
                if ( $observacion_id != 0 )
                {
                    // Actualizar
                    $valor_cosolidado_estudiante->valoracion_id_final = $valoracion_id_final;
                    $valor_cosolidado_estudiante->observacion_id = $observacion_id;
                    $valor_cosolidado_estudiante->modificado_por = $request->modificado_por;
                    $valor_cosolidado_estudiante->save();
                }else{
                    $valor_cosolidado_estudiante->delete();
                }
            }
            $fila++;
        }       

        return redirect( 'index_procesos/matriculas.procesos.consolidado_evaluacion_por_aspectos?id=5&semana_calendario_id=' . $request->semana_calendario_id )->with('flash_message', 'Consolidado almacenado correctamente.'); // . '&curso_id=' . $request->curso_id . '&id_asignatura=' . $request->id_asignatura
    }

    public function get_frecuencia( $valoraciones_est, $numero_fila )
    {
        $array_convenciones = ['','Alto','Medio','Bajo'];

        $array_valoracion = [];
        $title = '';
        $hay_alto = 0;
        $hay_medio = 0;
        $hay_bajo = 0;
        foreach ( $valoraciones_est as $key => $convencion_valoracion_id )
        {
            //

            if ( $numero_fila == 1 )
            {
                //dd( $valoraciones_est, $convencion_valoracion_id );
            }

            switch ( $convencion_valoracion_id )
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

        if ( $numero_fila == 1 )
        {
            //dd([$hay_alto, $hay_medio, $hay_bajo]);
        }
            
        $color_fondo = 'yellow';
        $color_texto = 'black';
        $lbl_valoracion = '';
        $value_valoracion = 0;

        if ( $hay_alto == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( ($hay_alto == 1 || $hay_alto == 2 || $hay_alto == 3 ) && $hay_medio == 0 && $hay_bajo == 0 )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( $hay_alto == 1 && $hay_medio == 2 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( ($hay_medio == 1 || $hay_medio == 2 || $hay_medio == 3 ) && $hay_alto == 0 && $hay_bajo == 0 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 1 && $hay_medio == 1 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_medio == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_alto == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( ($hay_bajo == 1 || $hay_bajo == 2 || $hay_bajo == 3 ) && $hay_alto == 0 && $hay_medio == 0 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        $valoracion = '<span style="background: ' . $color_fondo . '; color:' . $color_texto . ';" title="' . $title . '">' . $lbl_valoracion . '</span>';

        return (object)[ 'lbl_valoracion' => $valoracion, 'value_valoracion' => $value_valoracion ];
    }
   
}