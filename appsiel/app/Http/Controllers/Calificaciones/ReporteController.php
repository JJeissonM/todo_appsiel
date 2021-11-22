<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\Core\ConfiguracionController;

use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Grado;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;

use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CalificacionAuxiliar;
use App\Calificaciones\EncabezadoCalificacion;
use App\Calificaciones\ObservacionesBoletin;
use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Area;

use App\Core\Colegio;
use App\Core\FirmaAutorizada;

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
        $periodo = Periodo::find($request->periodo_id);

        $tope_escala_valoracion_minima = EscalaValoracion::where( 'periodo_lectivo_id', $periodo->periodo_lectivo_id )->orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, $request->periodo_id );

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, null );
        
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
        * POST promedio_acumulado_periodos TODAS LAS ASIGNATURAS
    */
    public function promedio_acumulado_periodos(Request $request)
    {
        $tope_escala_valoracion_minima = EscalaValoracion::where( 'periodo_lectivo_id', $request->periodo_lectivo_id )->orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, null );

        foreach( $calificaciones AS $calificacion )
        {
            if ( !is_null( $calificacion->nota_nivelacion() ) )
            {
                $calificacion->calificacion = $calificacion->nota_nivelacion()->calificacion;
            }
        }

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id, null );

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

        foreach( $calificaciones AS $calificacion )
        {
            $calificacion->lbl_nivelacion = '';
            if ( !is_null( $calificacion->nota_nivelacion() ) )
            {
                $calificacion->calificacion = $calificacion->nota_nivelacion()->calificacion;
                $calificacion->lbl_nivelacion = 'n';
            }
        }

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id, null );
        
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
        $tope_escala_valoracion_minima = EscalaValoracion::where( 'periodo_lectivo_id', $request->periodo_lectivo_id )
                                                        ->orderBy('calificacion_minima','ASC')
                                                        ->first()->calificacion_maxima;

        $calificaciones = Calificacion::get_calificaciones_boletines( $this->colegio->id, $request->curso_id, null, null );

        foreach( $calificaciones AS $calificacion )
        {
            $calificacion->lbl_nivelacion = '';
            if ( !is_null( $calificacion->nota_nivelacion() ) )
            {
                $calificacion->calificacion = $calificacion->nota_nivelacion()->calificacion;
                $calificacion->lbl_nivelacion = 'n';
            }
        }

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id, null );
        
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

    /**
     * consulta_notas_auxiliares
     *
     */
    public function consulta_notas_auxiliares(Request $request)
    {
        $periodo = Periodo::find( $request->periodo_id );

        // Warning!!!! El año se toma del periodo. Analizar si está bien.
        $anio = explode("-",$periodo->fecha_desde)[0];

        $periodo_lectivo = PeriodoLectivo::find( $periodo->periodo_lectivo_id );

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, null );

        // Warning!!! No usar funciones de Eloquent en el controller (acoplamiento al framework) 
        $curso = Curso::find($request->curso_id);
        
        $asignatura = Asignatura::find( $request->asignatura_id );

        // Se crea un array con los valores de las calificaciones de cada estudiante
        $vec_estudiantes = array();
        $i=0;
        foreach($estudiantes as $estudiante)
        {
            $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;
            $vec_estudiantes[$i]['nombre'] = $estudiante->nombre_completo;//." ".$estudiante->apellido2." ".$estudiante->nombres;
            $vec_estudiantes[$i]['codigo_matricula'] = $estudiante->codigo;
            $vec_estudiantes[$i]['id_calificacion'] = "no";
            $vec_estudiantes[$i]['calificacion'] = 0;
            $vec_estudiantes[$i]['logros'] = '';
            $vec_estudiantes[$i]['id_calificacion_aux'] = "no";
            for ($c=1; $c < 16; $c++) { 
                $key = "C".$c;
                $vec_estudiantes[$i][$key] = 0;
            }

            // Se verifica si cada estudiante tiene calificación creada
            $calificacion_est = Calificacion::where(['anio'=>$anio,'id_periodo'=>$request->periodo_id,
                                'curso_id'=>$request->curso_id,'id_asignatura'=>$request->asignatura_id,
                                'id_estudiante'=>$estudiante->id_estudiante])
                                ->get()
                                ->first();
            
            // Si el estudiante tiene calificacion se envian los datos de esta para editar
            if( !is_null($calificacion_est) )
            {
                $creado_por = $calificacion_est->creado_por;
                $modificado_por = Auth::user()->email;
                // Obtener la calificación auxiliar del estudiante
                $calificacion_aux = CalificacionAuxiliar::where(['anio'=>$anio,'id_periodo'=>$request->periodo_id,
                                'curso_id'=>$request->curso_id,'id_asignatura'=>$request->asignatura_id,
                                'id_estudiante'=>$estudiante->id_estudiante])
                                ->get()
                                ->first();
                
                $vec_estudiantes[$i]['id_calificacion'] = $calificacion_est->id;
                $vec_estudiantes[$i]['calificacion'] = $calificacion_est->calificacion;
                $vec_estudiantes[$i]['logros'] = $calificacion_est->logros;
                $vec_estudiantes[$i]['id_calificacion_aux'] = $calificacion_aux->id;

                for ($c=1; $c < 16; $c++) { 
                    $key = "C".$c;
                    $vec_estudiantes[$i][$key] = $calificacion_aux->$key;
                }

            }
            $i++;
        }

        $cantidad_estudiantes = count($estudiantes);

        $encabezados_calificaciones = EncabezadoCalificacion::where('periodo_id', $request->periodo_id)
                                                            ->where('curso_id', $request->curso_id)
                                                            ->where('asignatura_id', $request->asignatura_id)
                                                            ->get();

        $vista = View::make( 'calificaciones.incluir.consulta_notas_auxiliares', compact('vec_estudiantes', 'cantidad_estudiantes', 'anio','curso','periodo','periodo_lectivo', 'asignatura', 'encabezados_calificaciones') )->render();   

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }


    public function certificado_notas( Request $request )
    {
        $estudiantes = Matricula::todos_estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id );
        
        if( $request->estudiante_id != '' )
        {
            $estudiantes = $estudiantes->where('id_estudiante', (int)$request->estudiante_id)->all();
        }

        // Seleccionar asignaturas del grado
        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $request->curso_id, null, $request->periodo_lectivo_id);

        $curso = Curso::find( $request->curso_id );

        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );

        $maxima_escala_valoracion = EscalaValoracion::where( 'periodo_lectivo_id', $request->periodo_lectivo_id )->orderBy('calificacion_minima','DESC')->first()->calificacion_maxima;

        $periodo_id = $request->periodo_id;
        $observacion_adicional = $request->observacion_adicional;
        $tam_hoja = $request->tam_hoja;

        $array_fecha = [ date('d'), ConfiguracionController::nombre_mes( date('m') ), date('Y') ];

        if ( $request->fecha_expedicion != '' )
        {
            $fecha = explode('-', $request->fecha_expedicion );
            $array_fecha = [ $fecha[2], ConfiguracionController::nombre_mes( $fecha[1] ), $fecha[0] ];            
        }

        $periodo = Periodo::find( $periodo_id );

        $firma_autorizada_1 = FirmaAutorizada::find( $request->firma_autorizada_1 );
        $firma_autorizada_2 = FirmaAutorizada::find( $request->firma_autorizada_2 );

        $vista = View::make( 'core.dis_formatos.plantillas.'.$request->estilo_formato, compact( 'estudiantes', 'asignaturas', 'curso', 'periodo_lectivo', 'periodo_id', 'array_fecha', 'firma_autorizada_1', 'firma_autorizada_2', 'observacion_adicional', 'tam_hoja', 'maxima_escala_valoracion', 'periodo' )  )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }


    public function rendimiento_areas_asignaturas( Request $request )
    {

        dd('Lo sentimos. Reporte no disponible. Intente en otro momento.');

        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );
        $periodo = Periodo::find( $request->periodo_id );
        $grado = Grado::find( $request->sga_grado_id );

        $array_cursos_id = Curso::where('sga_grado_id', $request->sga_grado_id)->get()->pluck('id')->toArray();

        // Seleccionar asignaturas del grado
        $asignaturas = CursoTieneAsignatura::asignaturas_del_grado( $array_cursos_id, $request->periodo_lectivo_id);

        $curso = Curso::find( $request->curso_id );

        $periodo_id = $request->periodo_id;
        $observacion_adicional = $request->observacion_adicional;
        $tam_hoja = $request->tam_hoja;

        $array_fecha = [ date('d'), ConfiguracionController::nombre_mes( date('m') ), date('Y') ];

        if ( $request->fecha_expedicion != '' )
        {
            $fecha = explode('-', $request->fecha_expedicion );
            $array_fecha = [ $fecha[2], ConfiguracionController::nombre_mes( $fecha[1] ), $fecha[0] ];            
        }

        $firma_autorizada_1 = FirmaAutorizada::find( $request->firma_autorizada_1 );
        $firma_autorizada_2 = FirmaAutorizada::find( $request->firma_autorizada_2 );

        $vista = View::make( 'core.dis_formatos.plantillas.'.$request->estilo_formato, compact( 'estudiantes', 'asignaturas', 'curso', 'periodo_lectivo', 'periodo_id', 'array_fecha', 'firma_autorizada_1', 'firma_autorizada_2', 'observacion_adicional', 'tam_hoja' )  )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }
}