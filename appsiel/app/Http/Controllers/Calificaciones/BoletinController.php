<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Database\Eloquent\Model;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Logro;
use App\Calificaciones\Meta;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Periodo;
use App\Calificaciones\Boletin;
use App\Calificaciones\Calificacion;
use App\Calificaciones\ObservacionesBoletin;
use App\Calificaciones\ObservacionIngresada;
use App\Calificaciones\PreinformeAcademico;
use App\Calificaciones\AsistenciaClase;

use App\AcademicoDocente\CursoTieneDirectorGrupo;
use App\AcademicoDocente\AsignacionProfesor;

use App\Core\PasswordReset;
use App\Core\Colegio;
use App\Sistema\Aplicacion;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class BoletinController extends Controller
{
	
	public function __construct()
    {
		$this->middleware('auth');
    }
	
	/**
     * Se muestra formulario para revisar
     *
     */
    public function revisar1()
    {
        $cursos = $this->get_array_cursos_segun_usuario();
        $periodos = Periodo::opciones_campo_select();

        $app = Aplicacion::find( Input::get('id') );

        $miga_pan = [
                        ['url' => $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Revisar informes']
                    ];

		return view('calificaciones.boletines.revisar1',compact('cursos','periodos','miga_pan'));
    }
	
	/**
     * Se muestra el resultado de la petición del usuario para revisar
     *
     */
    public function revisar2(Request $request)
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $periodo = Periodo::find( $request->id_periodo );
        $anio = (int)explode("-",$periodo->fecha_desde)[0];

        // Listado de estudiantes con matriculas activas en el curso y año indicados
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, null );

        if ( $request->tipo_informe[0] == 'preinforme' )
        {
            $html = '<br>';
            foreach ($estudiantes as $estudiante )
            {
                $html .= $this->consultar_preinforme( $request->id_periodo, $request->curso_id, $estudiante->id_estudiante);

                $html .= '<hr>';
            }

            return $html;
        }
			
		// Seleccionar asignaturas del curso
		$asignaturas = CursoTieneAsignatura::asignaturas_del_curso($request->curso_id, null, $periodo->periodo_lectivo_id );

        $calificaciones = Calificacion::get_calificaciones_boletines( $colegio->id, $request->curso_id, null, $periodo->id );

        $escala_valoracion = EscalaValoracion::all();

        $observaciones = ObservacionesBoletin::get_observaciones_boletines( $colegio->id, $periodo->id, $request->curso_id);

        return View::make('calificaciones.boletines.revisar2',compact('estudiantes','asignaturas','colegio','periodo','anio','calificaciones','escala_valoracion','observaciones'))->render();
		
    }

    public function consultar_preinforme($periodo_id, $curso_id, $estudiante_id)
    {
        $periodo = Periodo::find($periodo_id);
        $anio = PeriodoLectivo::find($periodo->periodo_lectivo_id)->descripcion;

        $estudiante = Estudiante::get_datos_basicos($estudiante_id);

        $curso = Curso::find($curso_id);

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($curso_id, null, null, null);

        return View::make('academico_estudiante.preinforme_academico', compact('estudiante', 'periodo', 'anio', 'curso', 'asignaturas'))->render();
    }
	
	/**
     * Muestra Formulario para imprimir
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	public function imprimir()
    {        
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $opciones1 = Curso::where('id_colegio','=',$colegio->id)->where('estado','=','Activo')->orderBy('descripcion')->get();
        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id]=$opcion->descripcion;
        }
        $cursos = $vec1;


		$periodos_lectivos = PeriodoLectivo::get_array_activos();

        $formatos = [
                        'pdf_boletines_1' => 'Formato # 1 (estándar)',
                        'pdf_boletines_2' => 'Formato # 2 (preescolar)',
                        'pdf_boletines_3' => 'Formato # 3 (moderno)',
                        'pdf_boletines_4' => 'Formato # 4 (resúmen)',
                        'pdf_boletines_6' => 'Formato # 5 (marca de agua)'
                    ];

        if( config( 'calificaciones.manejar_preinformes_academicos' ) == 'Si' )
        {
            $formatos[ 'pdf_preinforme_academico' ] = 'Preinforme Académico';
        }

		$miga_pan = [
                        ['url'=>'calificaciones?id='.Input::get('id'),'etiqueta'=>'Calificaciones'],
                        ['url'=>'NO','etiqueta'=>'Imprimir informes']
                    ];

        return view( 'calificaciones.boletines.form_imprimir', compact('cursos','periodos_lectivos', 'formatos', 'miga_pan' ) );
    }
	
	public function generarPDF( Request $request )
	{
        $colegio = Auth::user()->empresa->colegio;
        $curso = Curso::find( $request->curso_id );
        $periodo = Periodo::find( $request->periodo_id );
        $anio = (int)explode("-",$periodo->fecha_desde)[0];

        $obj_matricula = new Matricula;
        $matriculas = $obj_matricula->get_segun_periodo_lectivo_y_curso( $periodo->periodo_lectivo_id, $request->curso_id );
        if( empty( $matriculas->toArray() ) )
        {
            return redirect( 'calificaciones/boletines/imprimir?id=' . Input::get('id') . '&id_modelo=0' )->with( 'mensaje_error', "No hay regitros de estudiantes matriculados en el curso " . $curso->descripcion );
        }

        if( $request->estudiante_id != null && $request->estudiante_id != '' )
        {
            $matriculas = $matriculas->where( 'id_estudiante', (int)$request->estudiante_id )->all();
        }

        //$asignaturas = CursoTieneAsignatura::asignaturas_del_curso($request->curso_id, null, $periodo->periodo_lectivo_id, null);

        // Parametros enviados        
        $convetir_logros_mayusculas = $request->convetir_logros_mayusculas;
        $mostrar_areas = $request->mostrar_areas;
        $mostrar_calificacion_media_areas = $request->mostrar_calificacion_media_areas;
        $mostrar_fallas = $request->mostrar_fallas;
        $mostrar_nombre_docentes = $request->mostrar_nombre_docentes;
        $mostrar_escala_valoracion = $request->mostrar_escala_valoracion;
        $mostrar_usuarios_estudiantes = $request->mostrar_usuarios_estudiantes;
        $mostrar_etiqueta_final = $request->mostrar_etiqueta_final;
        $mostrar_nota_nivelacion = $request->mostrar_nota_nivelacion;
        $tam_hoja = $request->tam_hoja;
        $tam_letra = $request->tam_letra;
        $cantidad_caracteres_para_proxima_pagina = $request->cantidad_caracteres_para_proxima_pagina;
        
        $margenes = (object)[ 
                                'superior' => $request->margen_superior - 5,
                                'derecho' => $request->margen_derecho - 5,
                                'inferior' => $request->margen_inferior - 5,
                                'izquierdo' => $request->margen_izquierdo - 5 
                            ];

        $firmas = $this->almacenar_imagenes_de_firmas( $request );

        $datos = $this->preparar_datos_boletin( $periodo, $curso, $matriculas );

        $periodos = Periodo::get_activos_periodo_lectivo( $periodo->periodo_lectivo_id );
        // Excluir el periodo final
        foreach ($periodos as $key => $value)
        {
            if ( $value->periodo_de_promedios )
            {
                unset( $periodos[$key] );
            }
        }

        $url_imagen_marca_agua = config('matriculas.url_imagen_marca_agua');

        if ($request->formato == 'pdf_boletines_6') {
            $view =  $this->get_view_for_pdf_boletines_6($request->formato, $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina);
        }else{
            $view =  View::make('calificaciones.boletines.'.$request->formato, compact( 'colegio', 'curso', 'periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'datos','margenes','mostrar_nota_nivelacion', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua') )->render();
        }
        
        //dd($view);

        // Se prepara el PDF
        $orientacion='portrait';
        $pdf = App::make('dompdf.wrapper');			
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);

		//return $view;
		return $pdf->download('boletines_del_curso_'.$curso->descripcion.'.pdf');
	}

    public function get_view_for_pdf_boletines_6($formato, $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina)
    {
        $lbl_numero_periodo = $this->get_label_periodo($periodo);

        $all_boletines = '';

        foreach($datos as $registro)
        {            
            $obj_lineas_cuerpo_boletin = $this->dividir_lineas_cuerpo_boletin($registro->cuerpo_boletin->lineas,$cantidad_caracteres_para_proxima_pagina);

            $lineas_cuerpo_boletin = $obj_lineas_cuerpo_boletin->first_group;

            if (empty($obj_lineas_cuerpo_boletin->second_group)) {
                $front_side =  View::make( 'calificaciones.boletines.pdf_boletines_6_onepage', compact( 'colegio', 'curso', 'periodo','lbl_numero_periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin') )->render();
                $back_side = '';
            }else{
                $front_side =  View::make( 'calificaciones.boletines.pdf_boletines_6_frontside', compact( 'colegio', 'curso', 'periodo','lbl_numero_periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin') )->render();
                
                $lineas_cuerpo_boletin = $obj_lineas_cuerpo_boletin->second_group;

                $back_side =  View::make( 'calificaciones.boletines.pdf_boletines_6_backside', compact( 'colegio', 'curso', 'periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin') )->render();
            }                

            $all_boletines .= $front_side . $back_side;
        }

        return View::make( 'calificaciones.boletines.pdf_boletines_6', compact( 'all_boletines','curso', 'tam_hoja', 'tam_letra','margenes', 'mostrar_areas'))->render();
    }

    public function dividir_lineas_cuerpo_boletin($lineas,$cantidad_caracteres_para_proxima_pagina)
    {
        $cant_caracteres = 0;
        $obj_lineas = (object)[
            'first_group' => [],
            'second_group' => []
        ];

        foreach ($lineas as $key => $linea) {
            if ( $linea->logros != null )    
            {
                foreach( $linea->logros as $un_logro )
                {
                    $cant_caracteres += strlen($un_logro->descripcion);
                }
            }
            if ( $linea->logros_adicionales != null )    
            {
                foreach( $linea->logros_adicionales as $un_logro )
                {
                    $cant_caracteres += strlen($un_logro->descripcion);
                }
            }

            if ( $cant_caracteres < $cantidad_caracteres_para_proxima_pagina) {
                $obj_lineas->first_group[] = $linea;
            }else{
                $obj_lineas->second_group[] = $linea;
            }
        }

        return $obj_lineas;
    }

    public function get_label_periodo($periodo)
    {
        $lbl_numero_periodo = '';
        switch ($periodo->numero) {
            case '1':
                $lbl_numero_periodo = 'PRIMER';
                break;
                
            case '2':
                $lbl_numero_periodo = 'SEGUNDO';
                break;
                
            case '3':
                $lbl_numero_periodo = 'TERCER';
                break;
                
            case '4':
                $lbl_numero_periodo = 'CUARTO';
                break;
            
            case '5':
                $lbl_numero_periodo = 'ÚLTIMO';
                break;
            
            default:
                # code...
                break;
        }

        return $lbl_numero_periodo;
    }

    public function preparar_datos_boletin( $periodo, $curso, $matriculas )
    {
        $asignaturas_asignadas = $curso->asignaturas_asignadas->where('periodo_lectivo_id', $periodo->periodo_lectivo_id);

        $datos = (object)[];
        $l = 0;
        foreach ($matriculas as $matricula)
        {
            if( is_null( $matricula->estudiante ) )
            {
                dd( 'ERROR. La matricula no tiene un estudiante asignado.', $matricula );
            }

            $datos->estudiantes[$l] = (object)[];
            $datos->estudiantes[$l]->estudiante = $matricula->estudiante;
            $datos->estudiantes[$l]->password_estudiante = PasswordReset::where( 'email', $matricula->estudiante->tercero->email )->get()->first();
            $datos->estudiantes[$l]->observacion = ObservacionesBoletin::get_x_estudiante( $periodo->id, $curso->id, $matricula->estudiante->id );

            $a = 0;
            $cuerpo_boletin = (object)[];
            foreach ($asignaturas_asignadas as $asignacion)
            {                
                $cuerpo_boletin->lineas[$a] = (object)[];
                $cuerpo_boletin->lineas[$a]->asignacion_asignatura = $asignacion;

                $calificacion = Calificacion::get_para_boletin( $periodo->id, $curso->id, $matricula->estudiante->id, $asignacion->asignatura_id );
                
                $cuerpo_boletin->lineas[$a]->calificacion = $calificacion;

                if(is_null($asignacion->asignatura)){dd('NO hay registros de asignauras para esa asignacion.', $asignacion);} // La asignatura no existe

                $cuerpo_boletin->lineas[$a]->area_id = $asignacion->asignatura->area_id;
                $cuerpo_boletin->lineas[$a]->peso_asignatura = $asignacion->peso;
                $cuerpo_boletin->lineas[$a]->valor_calificacion = 0;

                $cuerpo_boletin->lineas[$a]->escala_valoracion = null;
                $cuerpo_boletin->lineas[$a]->logros = null;
                $cuerpo_boletin->lineas[$a]->logros_adicionales = null;
                if ( !is_null($calificacion) )
                {
                    $valor_calificacion = $calificacion->calificacion;
                    $calificacion_nivelada = $periodo->get_calificacion_nivelacion( $curso->id, $matricula->estudiante->id, $asignacion->asignatura->id );
                    if( !is_null($calificacion_nivelada) )
                    {
                        $valor_calificacion = $calificacion_nivelada->calificacion;
                    }

                    $escala_valoracion = EscalaValoracion::get_escala_segun_calificacion( $valor_calificacion, $periodo->periodo_lectivo_id );
                    $cuerpo_boletin->lineas[$a]->escala_valoracion = $escala_valoracion;

                    if( is_null( $escala_valoracion ) )
                    {
                        dd( 'Por favor corrija la calificacion. No hay configurada una Escala de valoracion para la calificacion ' . $valor_calificacion . '. Curso: ' . $curso->descripcion . '. Asignatura: ' . $asignacion->asignatura->descripcion . '. Estudiante: ' . $matricula->estudiante->tercero->descripcion );
                    }

                    $cuerpo_boletin->lineas[$a]->logros = Logro::get_para_boletin( $periodo->id, $curso->id, $asignacion->asignatura_id, $escala_valoracion->id );

                    $cuerpo_boletin->lineas[$a]->logros_adicionales = $this->get_logros_adicionales( $calificacion, $asignacion->asignatura_id );

                    $cuerpo_boletin->lineas[$a]->valor_calificacion = $valor_calificacion;
                }

                $cuerpo_boletin->lineas[$a]->propositos = Meta::get_para_boletin( $periodo->id, $curso->id, $asignacion->asignatura_id );
                
                $cuerpo_boletin->lineas[$a]->profesor_asignatura = AsignacionProfesor::get_profesor_de_la_asignatura( $curso->id, $asignacion->asignatura_id, $periodo->periodo_lectivo_id );

                $anotacion = PreinformeAcademico::where([
                                                            ['id_periodo', '=', $periodo->id],
                                                            ['curso_id', '=', $curso->id],
                                                            ['id_asignatura', '=', $asignacion->asignatura_id],
                                                            ['id_estudiante', '=', $matricula->estudiante->id]
                                                        ])
                                                ->get()
                                                ->first();
                if ( !is_null($anotacion) ) 
                {
                    $cuerpo_boletin->lineas[$a]->anotacion = $anotacion->anotacion;
                }else{
                    $cuerpo_boletin->lineas[$a]->anotacion = '';
                }

                // Registro de inasistencias
                $cuerpo_boletin->lineas[$a]->fallas = AsistenciaClase::get_inasistencias( $curso->id, $periodo->fecha_desde, $periodo->fecha_hasta, $matricula->estudiante->id, $asignacion->asignatura_id);
                
                $a++;
            } // Fin - Por cada asignatura

            $datos->estudiantes[$l]->cuerpo_boletin = $cuerpo_boletin;

            $l++;
        }
        
        return $datos->estudiantes;
    }

    public function get_logros_adicionales( $calificacion, $asignatura_id )
    {
        $vec_logros = explode( ",", $calificacion->logros);

        return Logro::whereIn( 'codigo', $vec_logros )
                    ->where( 'asignatura_id', $asignatura_id )
                    ->get();
    }

    public function almacenar_imagenes_de_firmas( $request )
    {
        $firmas = [];
        if ( $request->file('firma_rector') != null ) {
            $firmas[0] = $request->file('firma_rector');
            Storage::put('firma_rector.png',
                file_get_contents( $firmas[0]->getRealPath() ) );
        }else{
            $firmas[0] = 'No cargada';
        }
        if ( $request->file('firma_profesor') != null ) {
            $firmas[1] = $request->file('firma_profesor');
            Storage::put('firma_profesor.png',
                file_get_contents( $firmas[1]->getRealPath() ) );
        }else{
            $firmas[1] = 'No cargada';
        }

        return $firmas;
    }
	
    // Muestra formulario para el cálculo del puesto (g = get)
	public function calcular_puesto_g()
    {
        $cursos = $this->get_array_cursos_segun_usuario();

        $periodos = Periodo::opciones_campo_select();

        $app = Aplicacion::find( Input::get('id') );

		$miga_pan = [
                        ['url' => $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Calcular puesto estudiantes']
                    ];

        return view('calificaciones.boletines.calcular_puesto',compact('cursos','periodos','miga_pan'));
	}

    /*
        Los que no son administradores, devuelve solo los cursos donde son director de grupo
    */
    public function get_array_cursos_segun_usuario()
    {
        $user = Auth::user();
        
        if ( $user->hasRole('SuperAdmin') || $user->hasRole('Admin Colegio') || $user->hasRole('Colegio - Vicerrector')  ) 
        {
            return Curso::opciones_campo_select();           
        }else{
            
            $opciones1 = Curso::get_registros_estado_activo();

            $vec1[''] = '';
        
            foreach ($opciones1 as $opcion)
            {
                $esta = CursoTieneDirectorGrupo::where('curso_id', $opcion->id)
                                                ->where('user_id', $user->id)
                                                ->count();

                if ( $esta > 0 ) 
                {
                    $vec1[$opcion->id] = $opcion->descripcion;               
                }
            }
        }
        
        return $vec1;
    } 

    /*
        PROCESO PARA CALCULAR LOS PUESTOS DE ESTUDIANTES DE UN CURSO

        NOTA: NO ESTA TENIENDO EN CUENTA LA NOTA DE NIVELACION. SERÍA INJUSTO QUE ALGUIENQUE NIVELE OCUPE LOS PRIMERO PUESTOS. AUNQUE EN EL PERIODO FINAL SI ENTRA LA NOTA DE NIVELACION.
	*/
    public function calcular_puesto_p(Request $request)
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $periodo = Periodo::find($request->id_periodo);
        $anio = explode("-",$periodo->fecha_desde)[0];

        /**
		 * El puesto se almacena en la misma tabla donde se almacenas las observaciones del boletín
		 * Calcular el promedio de calificaciones de cada estudiante según 
		 * los datos del boletín (año, periodo, curso)
		 */
		$promedios = Calificacion::where( 'id_colegio', $colegio->id)
                                    ->where( 'id_periodo', $request->id_periodo)
                                    ->where( 'curso_id', $request->curso_id )
                                    ->select(
                                                DB::raw('AVG(calificacion) AS promedioCalificaciones'),
                                                'id_estudiante')
                                    ->groupBy('id_estudiante')
                                    ->orderBy('promedioCalificaciones','DESC')
                                    ->get();

		$nom_curso = Curso::where('id','=',$request->curso_id)->value('descripcion');

		$total_estudiantes = count( Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, 'Activo'  ) );
		
		// Si hay calificaciones para los datos enviados
		if( !empty($promedios) )
        {
		
			/**
			 * Crear un vector con los puestos unicos que existen
			 */
			$puestos = Calificacion::where( 'id_colegio', $colegio->id)
                                    ->where( 'id_periodo', $request->id_periodo)
                                    ->where( 'curso_id', $request->curso_id )
                                    ->select(
                                                DB::raw('AVG(calificacion) AS promedioCalificaciones'),
                                                'id_estudiante')
                                    ->groupBy('id_estudiante')
                                    ->orderBy('promedioCalificaciones','DESC')
                                    ->distinct('promedioCalificaciones')
                                    ->get();

            /*$query_2 = "SELECT DISTINCT promedioCalificaciones FROM (".$query_1.") Puestos";
			$puestos = DB::select($query_2);
            */

			$i=1;
			foreach($puestos as $puesto)
            {
				$vec_puestos [$i] = $puesto->promedioCalificaciones;
				$i++;
			}
			
			// Buscar el puesto al que pertenece cada estudiante según su promedio de calificaciones
			foreach($promedios as $fila){
				$puesto_est = array_search($fila->promedioCalificaciones,$vec_puestos);
				
				// Verificar si ya hay observaciones ingresadas en la tabla 
				$cant_observa = ObservacionesBoletin::where(
                                                                [ 'id_colegio' => $colegio->id,
                                                                    'id_periodo' => $request->id_periodo,
                                                                    'curso_id' => $request->curso_id,
                                                                    'id_estudiante' => $fila->id_estudiante
                                                                ])
                                                        ->count();

				$el_puesto = $puesto_est.' / '.$total_estudiantes;
				
				if($cant_observa>0){
					// Si ya hay observaciones para ese estudiante, 
					// Se deben actualizar los registros en la tabla de observaciones_boletines
					ObservacionesBoletin::where(
                                                                [ 'id_colegio' => $colegio->id,
                                                                    'id_periodo' => $request->id_periodo,
                                                                    'curso_id' => $request->curso_id,
                                                                    'id_estudiante' => $fila->id_estudiante
                                                                ])
						                  ->update(['puesto' => $el_puesto]);
				}else{
					// INSERTAR registros en la tabla de observaciones_boletines
					ObservacionesBoletin::insert(
                                                    [ 'id_colegio' => $colegio->id,
                                                        'id_periodo' => $request->id_periodo,
                                                        'curso_id' => $request->curso_id,
                                                        'id_estudiante' => $fila->id_estudiante,
                                                        'puesto' => $el_puesto
                                                    ]);
					
					// Guardar en tabla auxiliar para indicar que ya se ingresaron observaciones o puestos
					// del curso en de ese año-periodo. 
					// Esta tabla es para saber si se están creando los registros por primera vez 
					// de observaciones o puesto; para determinar si se van a INSERTAR o ACTUALIZAR
					ObservacionIngresada::insert(
                                                    [ 'id_colegio' => $colegio->id,
                                                        'id_periodo' => $request->id_periodo,
                                                        'curso_id' => $request->curso_id
                                                    ]);
				}			
			}
			
			return redirect('/calificaciones/boletines/calcular_puesto?id='.$request->id_app)->with('flash_message','Se calcularon los puestos de cada estudiante para los boletines del curso <b>'.$nom_curso.'</b>');	
		}else{
			// Si no hay calificaciones ingresadas para los datos enviados
			return redirect('/calificaciones/boletines/calcular_puesto?id='.$request->id_app)->with('mensaje_error','Aún NO hay calificaciones ingresadas para los estudiante del curso <b>'.$nom_curso.'</b> en el periodo seleccionado.');	
		}
		
    }
	
	/**
     * Crear un vector con los estudiantes con matricula activa y que NO tienen boletín para el año, periodo y curso dado.
     *
     */
	public function select_estudiantes($anio,$id_periodo,$curso_id)
    {
        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, null, 'Activo' );

		// Los estudiantes que no tiene boletin para el curso, año y periodo dado
		$i=0;$ind=0;
		foreach ($estudiantes as $campo)
        {
			// Se consultan los estudiantes que ya tienen boletines para ese año, periodo y curso
			$est = Boletin::where([
											['id_estudiante',"=",$campo->id],
											['curso_id',"=",$curso_id],
											['id_periodo',"=",$id_periodo],
											['anio',"=",$anio],
										])->value('observaciones');
			
			if( empty($est) )
            {
                // Si el estudiante no tiene boletín para ese año, periodo y curso, se agrega en un array de estudiantes
				$vector[$i]['id_estudiante']=$campo->id;
				$vector[$i]['nombre_completo']=$campo->nombres." ".$campo->apellido1." ".$campo->apellido2;
				$vector[$i]['codigo_matricula']=$campo->codigo;
				$i++;
				$ind=1;
			}
		}
		
		// Si todos los estudiantes YA tienen boletín para ese año, periodo y curso, 
		// se llena el array de estudiantes con datos vacios
		if($ind==0){
			$vector[0]['id_estudiante']="";
			$vector[0]['nombre_completo']="";
			$vector[0]['codigo_matricula']="";
		}
		
		return $vector;
	}
	
	
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		//
    }
	
	
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}