<?php

namespace App\Http\Controllers\Calificaciones;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Matricula;

use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ProcesoController extends ModeloController
{

    public function form_generar_promedio_notas_periodo_final()
    {

        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id=' . Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: Generar promedio de notas periodo final']
                    ];

        $periodos_lectivos = PeriodoLectivo::get_array_activos();

        return view( 'calificaciones.procesos.generar_promedio_notas_periodo_final', compact( 'miga_pan', 'periodos_lectivos') );
    }


    public function consultar_periodos_periodo_lectivo( $periodo_lectivo_id )
    {
        $periodo_lectivo = PeriodoLectivo::find( $periodo_lectivo_id );

        if ( is_null($periodo_lectivo) )
        {
            return [ '', '<span style="color: red;">Error en la obtención de los datos por favor intente nuevamente.</span>' ];
        }
        
        $periodos = $periodo_lectivo->periodos->where('estado','Activo')->all();

        //$periodos = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );

        $tabla = '<table class="table table-bordered">';

        $fila_periodos_promediar = '<tr> <td> <b>Periodos a promediar:</b> </td> <td>';

        $el_primero = true;
        foreach ($periodos as $fila)
        {
            if ( !$fila->periodo_de_promedios )
            {
                if ( $el_primero )
                {
                    $fila_periodos_promediar .= $fila->descripcion;
                    $el_primero = false;
                }else{
                    $fila_periodos_promediar .= ', '.$fila->descripcion;
                }
            }
        }
        $fila_periodos_promediar .= '</td> </tr>';

        $hay_periodo_final = 0; // Solo debe valer hasta 1
        
        $fila_periodo_final = '<tr> <td> <b>Periodo de promedios:</b> </td> <td>';
        $el_primero = true;
        foreach ($periodos as $fila)
        {
            if ( $fila->periodo_de_promedios )
            {
                if ( $el_primero )
                {
                    $fila_periodo_final .= $fila->descripcion;
                    $el_primero = false;
                }else{
                    $fila_periodo_final .= ', '.$fila->descripcion;
                }
                $hay_periodo_final++;
            }
        }
        $fila_periodo_final .= '</td> </tr>';

        if ( $hay_periodo_final == 0)
        {
            $fila_periodo_final = '<tr> <td> <b>Periodo de promedios:</b> </td> <td> <span style="color: red;">No creado</span> </td> </tr>';
        }

        $tabla .= $fila_periodos_promediar.$fila_periodo_final.'</table>';
        
        return [ $tabla, $hay_periodo_final ];
    }

    public function calcular_promedio_notas_periodo_final( $periodo_lectivo_id )
    {
        $usuario_email = Auth::user()->email;

        $periodo_lectivo = PeriodoLectivo::find( $periodo_lectivo_id );
        
        $periodo_final_id = 0;

        //$periodos = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );

        if ( $periodo_lectivo == null )
        {
            return [ '', '<span style="color: red;">Error en la obtención de los datos por favor intente nuevamente.</span>' ];
        }
        
        $periodos = $periodo_lectivo->periodos->where('estado','Activo')->all();
        
        $array_ids_periodos_promediar = []; // Se excluye el de promedio (FINAL)
        $i = 0;
        foreach ($periodos as $fila)
        {
            if ( !$fila->periodo_de_promedios )
            {
                $array_ids_periodos_promediar[$i] = $fila->id;
                $i++;
            }else{
                $periodo_final_id = $fila->id;
            }
        }

        // PASO 1. Vaciar los datos del periodo final en ese periodo lectivo
        Calificacion::where('id_periodo',$periodo_final_id)->delete();

        // PASO 2. Calcular y almacenar las nuevas calificaciones promedios
        $cursos_del_periodo_lectivo = Curso::get_registros_del_periodo_lectivo( $periodo_lectivo_id );        
        $cantidad_registros = 0;

        foreach ($cursos_del_periodo_lectivo as $curso)
        {
            $asignaturas_del_curso_y_periodo_lectivo = Asignatura::asignadas_al_curso( $periodo_lectivo_id, $curso->id );

            // Listado de estudiantes con matriculas en el curso y año indicados
            $estudiantes = Matricula::estudiantes_matriculados( $curso->id, $periodo_lectivo_id, null );

            foreach ($asignaturas_del_curso_y_periodo_lectivo as $asignatura)
            {
                foreach ($estudiantes as $estudiante)
                {
                    
                    $prom = Calificacion::get_calificacion_promedio_asignatura_estudiante_periodos($array_ids_periodos_promediar, $curso->id, $estudiante->id, $asignatura->id);

                    Calificacion::create( [
                                            'codigo_matricula' => $estudiante->codigo,
                                            'id_colegio' => $estudiante->id_colegio,
                                            'anio' => explode('-', $periodo_lectivo->fecha_desde)[0] ,
                                            'id_periodo' => $periodo_final_id,
                                            'curso_id' =>$curso->id,
                                            'id_estudiante' => $estudiante->id,
                                            'id_asignatura' => $asignatura->id,
                                            'calificacion' => (float)$prom,
                                            'creado_por' => $usuario_email  
                                        ] );
                    /**/
                    
                    $cantidad_registros++;
                }

            }
        }
        
        return $cantidad_registros;
    }


    /**
     *      PENDIENTE      POR    TERMINAR
     */
    public function calcular_promedio_notas_periodo_final_curso( $periodo_lectivo_id, $curso_id )
    {
        $usuario_email = Auth::user()->email;

        $periodo_lectivo = PeriodoLectivo::find( $periodo_lectivo_id );
        
        $periodo_final_id = 0;

        if ( $periodo_lectivo == null )
        {
            return [ '', '<span style="color: red;">Error en la obtención de los datos por favor intente nuevamente.</span>' ];
        }
        
        $periodos = $periodo_lectivo->periodos->where('estado','Activo')->all();
        
        $array_ids_periodos_promediar = []; // Se excluye el de promedio (FINAL)
        $i = 0;
        foreach ($periodos as $fila)
        {
            if ( !$fila->periodo_de_promedios )
            {
                $array_ids_periodos_promediar[$i] = $fila->id;
                $i++;
            }else{
                $periodo_final_id = $fila->id;
            }
        }

        // PASO 1. Vaciar los datos del periodo final en ese periodo lectivo para curso enviado
        Calificacion::where([
            ['id_periodo', '=', $periodo_final_id],
            ['curso_id', '=', $curso_id],
            ])->delete();

        // PASO 2. Calcular y almacenar las nuevas calificaciones promedios       
        $cantidad_registros = 0;

        $asignaturas_del_curso_y_periodo_lectivo = Asignatura::asignadas_al_curso( $periodo_lectivo_id, $curso_id );

        // Listado de estudiantes con matriculas en el curso y año indicados
        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, $periodo_lectivo_id, null );

        foreach ($asignaturas_del_curso_y_periodo_lectivo as $asignatura)
        {
            foreach ($estudiantes as $estudiante)
            {
                
                $prom = Calificacion::get_calificacion_promedio_asignatura_estudiante_periodos($array_ids_periodos_promediar, $curso_id, $estudiante->id, $asignatura->id);

                Calificacion::create( [
                                        'codigo_matricula' => $estudiante->codigo,
                                        'id_colegio' => $estudiante->id_colegio,
                                        'anio' => explode('-', $periodo_lectivo->fecha_desde)[0] ,
                                        'id_periodo' => $periodo_final_id,
                                        'curso_id' =>$curso_id,
                                        'id_estudiante' => $estudiante->id,
                                        'id_asignatura' => $asignatura->id,
                                        'calificacion' => (float)$prom,
                                        'creado_por' => $usuario_email  
                                    ] );
                /**/
                
                $cantidad_registros++;
            }

        }
        
        return $cantidad_registros;
    }

    public function consultar_areas_asignaturas_pesos( $periodo_lectivo_id, $grado_id )
    {
        $array_cursos_id = Curso::where('sga_grado_id',$grado_id)->get()->pluck('id')->toArray();

        $asignaturas_del_periodo_lectivo = CursoTieneAsignatura::asignaturas_del_grado( $array_cursos_id, $periodo_lectivo_id );
        //dd($asignaturas_del_periodo_lectivo);
        $vista = View::make('calificaciones.procesos.formulario_asignaturas_por_area', [ 'asignaturas' => $asignaturas_del_periodo_lectivo, 'periodo_lectivo_id' => $periodo_lectivo_id, 'grado_id' => $grado_id ] )->render();
        
        return $vista;
    }

    public function almacenar_pesos_asignaturas_areas( Request $request )
    {
        $cursos = Curso::where('sga_grado_id', $request->grado_id)->get();
        
        foreach ($cursos as $curso)
        {
            $asignaturas = $request->asignatura_id;
            $pesos = $request->peso;
            foreach ($asignaturas as $key => $asignatura_id)
            {
                CursoTieneAsignatura::where([
                                                ['periodo_lectivo_id', '=', $request->periodo_lectivo_id],
                                                ['curso_id', '=', $curso->id],
                                                ['asignatura_id', '=', (int)$asignatura_id] ]
                                            )
                                    ->update( ['peso' => $pesos[$key] ] );
            }                
        }        
        
        return redirect( 'index_procesos/calificaciones.procesos.asignar_pesos_asignaturas_por_area?id=2&id_modelo=0' )->with('flash_message','Pesos de asignaturas almacenados correctamente.'); 
    }


    /*
        form_copiar_logros_de_un_periodo: 
            PENDIENTE POR TERMINAR
    */
    public function form_copiar_logros_de_un_periodo()
    {
        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: Copiar logros de un periodo a otro']
                    ];

        $vec = Periodo::get_activos_periodo_lectivo();
        $periodos['']='';
        foreach ($vec as $opcion)
        {
            $periodos[$opcion->id] = $opcion->periodo_lectivo_descripcion . ' > ' . $opcion->descripcion;
        }

        return view( 'calificaciones.procesos.copiar_logros_de_un_periodo', compact( 'miga_pan', 'periodos') );
    }

    public function copiar_logros_de_un_periodo( Request $request )
    {
        $cursos = Curso::where('sga_grado_id', $request->grado_id)->get();
        
        foreach ($cursos as $curso)
        {
            $asignaturas = $request->asignatura_id;
            $pesos = $request->peso;
            foreach ($asignaturas as $key => $asignatura_id)
            {
                CursoTieneAsignatura::where([
                                                ['periodo_lectivo_id', '=', $request->periodo_lectivo_id],
                                                ['curso_id', '=', $curso->id],
                                                ['asignatura_id', '=', (int)$asignatura_id] ]
                                            )
                                    ->update( ['peso' => $pesos[$key] ] );
            }                
        }        
        
        return redirect( 'index_procesos/calificaciones.procesos.asignar_pesos_asignaturas_por_area?id=2&id_modelo=0' )->with('flash_message','Pesos de asignaturas almacenados correctamente.'); 
    }

}
