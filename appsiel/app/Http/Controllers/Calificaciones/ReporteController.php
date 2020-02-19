<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;

use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\ObservacionesBoletin;
use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Area;

use App\Core\Colegio;

use Input;
use DB;
use PDF;
use View;
use Auth;
use Cache;

class ReporteController extends Controller
{
    protected $escala_valoracion;
    protected $colegio;

    public function __construct()
    {
		$this->middleware('auth');

        if( Auth::check() ) 
        {
            $this->colegio = Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();
        }
    }

    /**
     * generar_reporte_consolidado_por_curso por periodo
     *
     */
    public function consolidado_periodo_por_curso(Request $request)
    {
        $tope_escala_valoracion_minima = EscalaValoracion::orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;
        
        $periodo = Periodo::find($request->periodo_id);

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, $request->periodo_id );


        $estado_matricula = null; // Todas las matriculas. ¿Está bien así?
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, $estado_matricula );

        //dd($estudiantes);
        $curso = Curso::find($request->curso_id);

        $areas = Area::orderBy('orden_listados')->get();

        $vec_areas = [];
        $vec_asignaturas = [];
        $as = 0;
        $celdas_encabezado_areas='';
        foreach ($areas as $un_area) 
        {
            $asignaturas_area = Asignatura::where('area_id', $un_area->id)->get();

            $cant_asig_area = 0;

            // Vector de asignaturas
            foreach ($asignaturas_area as $una_asignatura) 
            {

                $pertenece_al_curso = CursoTieneAsignatura::get_datos_asignacion( $periodo->periodo_lectivo_id, $request->curso_id, $una_asignatura->id );

                if ( !is_null( $pertenece_al_curso ) ) 
                {
                    $cant_asig_area++;

                    $vec_asignaturas[$as]['id'] = $una_asignatura->id;
                    $vec_asignaturas[$as]['abreviatura'] = $una_asignatura->abreviatura;
                    $as++;
                }
                    
            }
            
            if ( $cant_asig_area > 0) 
            {
                $celdas_encabezado_areas.='<th colspan="'.$cant_asig_area.'" style="border: 1px solid; text-align:center;">'.$un_area->abreviatura.'</th>';
            }
                
        }        


        $celdas_encabezado_asignaturas='';
        for ($i=0; $i < $as; $i++) 
        {
            $celdas_encabezado_asignaturas.='<th style="border: 1px solid; text-align:center;">'.$vec_asignaturas[$i]['abreviatura'].'</th>';
        }

        $observaciones = ObservacionesBoletin::get_observaciones_boletines( $this->colegio->id, $request->periodo_id, $request->curso_id);

        //where(['id_periodo' => $request->periodo_id, 'curso_id' => $request->curso_id])->select('id_estudiante','puesto')->get();

        //dd($calificaciones);

        $vista = View::make( 'calificaciones.incluir.consolidado_periodo_por_curso', compact('estudiantes', 'calificaciones', 'celdas_encabezado_areas', 'celdas_encabezado_asignaturas','vec_asignaturas','observaciones','periodo','curso','tope_escala_valoracion_minima') )->render();        

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }


    /*
        * POST cuadro_honor_estudiantes 
    */
    public function cuadro_honor_estudiantes(Request $request)
    {
        
        $calificaciones = Calificacion::calificaciones_promedio_por_estudiante( $request->periodo_id );
        
        //dd( $calificaciones );

        switch ( $request->tipo_reporte )
        {
            case 'colegio':
                # code...
                break;

            case 'nivel_academico':
                $agrupado = 'Nivel';
                break;
                
            case 'grado':
                $agrupado = 'Grado';
                break;
                
            case 'curso':
                $agrupado = 'Curso';
                break;
            
            default:
                # code...
                break;
        }

        // Se agrupa la consulta de acuerdo al tipo de reporte
        $grupos = $calificaciones->groupBy( $agrupado );

        // Se crea array con valores ordenados por calificacion_prom
        $salida = [];
        foreach ($grupos as $key => $value)
        {
            $salida[$key] = $value->sortByDesc('calificacion_prom')->toArray();
        }

        $cantidad_puestos = $request->cantidad_puestos;
        $mostrar_foto = $request->mostrar_foto;

        $periodo = '';
        if ( $request->periodo_id != '' ) 
        {
            $periodo = 'Periodo '.Periodo::where('id',$request->periodo_id)->value('descripcion');
        }

        $vista = View::make( 'calificaciones.incluir.cuadro_de_honor_tabla', compact('salida', 'cantidad_puestos', 'mostrar_foto', 'agrupado','periodo') )->render();        

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /*
        * POST promedio_acumulado_periodos 
    */
    public function promedio_acumulado_periodos(Request $request)
    {
        $tope_escala_valoracion_minima = EscalaValoracion::orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, null );

        $estado_matricula = null; // Todas las matriculas. ¿Está bien así?
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id, $estado_matricula );

        $periodos = Periodo::get_activos_periodo_lectivo( $request->periodo_lectivo_id );
        // Excluir el periodo final
        foreach ($periodos as $key => $value)
        {
            if ( $value->periodo_de_promedios )
            {
                unset( $periodos[$key] );
            }
        }

        
        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );
        $curso = Curso::find( $request->curso_id );

        $vista = View::make( 'calificaciones.incluir.promedio_acumulado_periodos_tabla', compact('estudiantes','calificaciones','periodos','curso','tope_escala_valoracion_minima','periodo_lectivo') )->render();        

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /**
     * promedio_proyectado_asignaturas
     *
     */
    public function promedio_proyectado_asignaturas(Request $request)
    {
        $tope_escala_valoracion_minima = EscalaValoracion::where('periodo_lectivo_id',$request->periodo_lectivo_id)
                                                        ->orderBy('calificacion_minima','ASC')
                                                        ->first()
                                                        ->calificacion_maxima;

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, null );

        //dd( $calificaciones->where('estudiante_id', 346)->where('asignatura_id', 30)->all() );

        $estado_matricula = null; // No filtra por estado; es decir, todas las matriculas. ¿Está bien así?
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id, $estado_matricula );
        
        $curso = Curso::find( $request->curso_id );     

        $asignaturas = Asignatura::asignadas_al_curso( $request->periodo_lectivo_id, $request->curso_id )->toArray();
        
        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );
        
        $periodos = Periodo::get_activos_periodo_lectivo( $request->periodo_lectivo_id );
        // Excluir el periodo final
        foreach ($periodos as $key => $value)
        {
            if ( $value->periodo_de_promedios )
            {
                unset( $periodos[$key] );
            }
        }
        //dd( $periodos );

        $vista = View::make( 'calificaciones.incluir.promedio_proyectado_asignaturas', compact('estudiantes', 'calificaciones', 'asignaturas','curso','tope_escala_valoracion_minima','periodos','periodo_lectivo') )->render();        

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /**
     * promedio_consolidado_asignaturas
     *
     */
    public function promedio_consolidado_asignaturas(Request $request)
    {
        $tope_escala_valoracion_minima = EscalaValoracion::orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, null );

        $estado_matricula = null; // Todas las matriculas. ¿Está bien así?
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id, $estado_matricula );
        
        $curso = Curso::find($request->curso_id);     

        $asignaturas = Asignatura::asignadas_al_curso( $request->periodo_lectivo_id, $request->curso_id )->toArray();
        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );
        $periodos = Periodo::get_activos_periodo_lectivo( $request->periodo_lectivo_id );
        // Excluir el periodo final
        foreach ($periodos as $key => $value)
        {
            if ( $value->periodo_de_promedios )
            {
                unset( $periodos[$key] );
            }
        }

        $vista = View::make( 'calificaciones.incluir.promedio_consolidado_asignaturas', compact('estudiantes', 'calificaciones', 'asignaturas','curso','tope_escala_valoracion_minima','periodos','periodo_lectivo') )->render();        

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

}