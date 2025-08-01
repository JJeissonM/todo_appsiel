<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
use App\Calificaciones\CalificacionAuxiliar;
use App\Calificaciones\CalificacionDesempenio;
use App\Calificaciones\NotaNivelacion;
use App\Calificaciones\Services\CalificacionesService;
use App\Core\PasswordReset;
use App\Core\Colegio;
use App\Sistema\Aplicacion;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Webklex\PDFMerger\Facades\PDFMergerFacade;
use ZipArchive;

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

        $logros = Logro::where([
            'periodo_id' => $request->id_periodo,
            'curso_id' => $request->curso_id,
            'escala_valoracion_id' => 0
        ])->get();

        $todas_las_calificaciones = CalificacionDesempenio::where([
            'periodo_id' => $request->id_periodo,
            'curso_id' => $request->curso_id
        ])->get();

        $metas_del_curso_en_el_periodo = collect([]);
        if( config( 'calificaciones.colegio_maneja_metas' ) == 'Si' )
        {
            $metas_del_curso_en_el_periodo = Meta::where( [
                                                ['periodo_id', '=', $request->id_periodo],
                                                ['curso_id', '=', $request->curso_id]
                                            ])->select('id', 'codigo', 'periodo_id', 'curso_id', 'asignatura_id', 'descripcion')
                                            ->get();
        }

        return View::make('calificaciones.boletines.revisar2',compact('estudiantes','asignaturas','colegio','periodo','anio','calificaciones','escala_valoracion','observaciones', 'logros', 'todas_las_calificaciones', 'metas_del_curso_en_el_periodo'))->render();
		
    }
    
    /**
     * 
     */
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
                        'pdf_boletines_6' => 'Formato # 5 (marca de agua)',
                        'pdf_boletines_7' => 'Formato # 6 (Calificaciones Aux.)',
                        'pdf_boletines_8_moderno_foto' => 'Formato # 7 (moderno con foto)',
                        'pdf_boletines_9_desempenios' => 'Formato # 8 (Por Desempeños)'
                    ];

        if( config( 'calificaciones.manejar_preinformes_academicos' ) == 'Si' )
        {
            $formatos[ 'pdf_preinforme_academico' ] = 'Preinforme Académico';
        }

		$miga_pan = [
                        ['url'=>'calificaciones?id='.Input::get('id'),'etiqueta'=>'Calificaciones'],
                        ['url'=>'NO','etiqueta'=>'Imprimir informes']
                    ];
        
        $parametros = config('calificaciones');
        return view( 'calificaciones.boletines.form_imprimir', compact('cursos','periodos_lectivos', 'formatos', 'miga_pan', 'parametros' ) );
    }
    
    /**
     * 
     */
	public function generarPDF( Request $request )
	{
        $firmas = $this->almacenar_imagenes_de_firmas( $request );

        $view = $this->get_view_for_pdf($request->all(), $firmas);
        $tam_hoja = $request->tam_hoja;
        
        $curso = Curso::find( $request->curso_id );

        // Se prepara el PDF
        $orientacion='portrait';
        $pdf = App::make('dompdf.wrapper');			
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);

        //return $view; 
		return $pdf->download('boletines_del_curso_'.$curso->descripcion.'.pdf');
	}
    
    /**
     * 
     */
	public function delete_pdfs_of_folder_of_curso_id( $curso_id )
	{
        $folderName = '/app/pdf_boletines_curso_id_' . $curso_id;
        
        $files = File::files(storage_path() . $folderName );
        
        File::delete($files);

        return 1;
	}
    
    /**
     * 
     */
	public function generar_pdf_un_boletin( Request $request )
	{
        $firmas = $this->almacenar_imagenes_de_firmas( $request );

        $view = $this->get_view_for_pdf($request->all(), $firmas, false);
            
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = $request->tam_hoja;
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);

        $estudiante = Estudiante::find((int)$request->estudiante_id);

        $nombrearchivo = uniqid() . '.pdf';
        if ($estudiante != null) {
            $nombrearchivo = str_slug( $estudiante->tercero->descripcion ) . '-' . uniqid() . '.pdf';
        }

        Storage::put( 'pdf_boletines_curso_id_' . $request->curso_id . '/' . $nombrearchivo, $pdf->output());

        return 'true';
	}
    
    /**
     * 
     */
    public function create_zip_of_folder_of_curso_id($curso_id)
    {
        $zip = new ZipArchive;
   
        $folderName = '/app/pdf_boletines_curso_id_' . $curso_id;
        $fileName = 'pdf_boletines_curso_id_' . $curso_id . '.zip';

        $path_complete = storage_path() . '/app/' . $fileName;
        
        if (file_exists($path_complete)) {
            unlink( $path_complete );
        }

        if ($zip->open($path_complete, ZipArchive::CREATE) === TRUE)
        {
        	// Folder files to zip and download
        	// files folder must be existing to your public folder
            $files = File::files(storage_path() . $folderName );
            
   			// loop the files result
            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }
             
            $zip->close();
        }
    	
    	// Download the generated zip
        return response()->download($path_complete);
    }
    
    /**
     * 
     */
    public function download_zip_of_curso_id($curso_id)
    {
        $fileName = 'pdf_boletines_curso_id_' . $curso_id . '.zip';

        $path_complete = storage_path() . '/app/' . $fileName;

        return response()->download($path_complete);
    }
    
    /**
     * 
     */
    public function merge_pdfs_and_download_by_curso($curso_id)
    {
        // MERGE PDFs
        $folderName = '/app/pdf_boletines_curso_id_' . $curso_id;
        
        $oMerger = PDFMergerFacade::init();
        
        // files folder must be existing to your public folder
        $files = File::files(storage_path() . $folderName );
        
        // loop the files result
        foreach ($files as $key => $value) {
            $oMerger->addPDF($value, 'all');
        }        

        $oMerger->setFileName('pdf_boletines_curso_id_' . $curso_id . '.pdf');

        $oMerger->merge();

    	// Download the generated PDF
        return $oMerger->download();
    }
    
    /**
     * 
     */
    public function get_view_for_pdf($data_request, $firmas, $with_page_breaks = true)
    {
        $colegio = Auth::user()->empresa->colegio;
        $curso = Curso::find( $data_request['curso_id'] );
        $periodo = Periodo::find( $data_request['periodo_id'] );
        $anio = (int)explode("-",$periodo->fecha_desde)[0];

        $obj_matricula = new Matricula;
        $matriculas = $obj_matricula->get_segun_periodo_lectivo_y_curso( $periodo->periodo_lectivo_id, $data_request['curso_id'] );
        if( empty( $matriculas->toArray() ) )
        {
            return redirect( 'calificaciones/boletines/imprimir?id=' . Input::get('id') . '&id_modelo=0' )->with( 'mensaje_error', "No hay regitros de estudiantes matriculados en el curso " . $curso->descripcion );
        }

        if( $data_request['estudiante_id'] != null && $data_request['estudiante_id'] != '' )
        {
            $matriculas = $matriculas->where( 'id_estudiante', (int)$data_request['estudiante_id'] )->all();
        }

        // Parametros enviados        
        $convetir_logros_mayusculas = $data_request['convetir_logros_mayusculas'];
        $mostrar_areas = $data_request['mostrar_areas'];
        $mostrar_calificacion_media_areas = $data_request['mostrar_calificacion_media_areas'];
        $mostrar_fallas = $data_request['mostrar_fallas'];
        $mostrar_nombre_docentes = $data_request['mostrar_nombre_docentes'];
        $mostrar_escala_valoracion = $data_request['mostrar_escala_valoracion'];
        $mostrar_usuarios_estudiantes = $data_request['mostrar_usuarios_estudiantes'];
        $mostrar_etiqueta_final = $data_request['mostrar_etiqueta_final'];
        $mostrar_nota_nivelacion = $data_request['mostrar_nota_nivelacion'];
        $mostrar_intensidad_horaria = (int)$data_request['mostrar_intensidad_horaria'];
        $tam_hoja = $data_request['tam_hoja'];
        $tam_letra = $data_request['tam_letra'];
        $cantidad_caracteres_para_proxima_pagina = $data_request['cantidad_caracteres_para_proxima_pagina'];
        $ancho_columna_asignatura = $data_request['ancho_columna_asignatura'];
        $mostrar_logros = $data_request['mostrar_logros'];
        
        $margenes = (object)[ 
                                'superior' => $data_request['margen_superior'] - 5,
                                'derecho' => $data_request['margen_derecho'] - 5,
                                'inferior' => $data_request['margen_inferior'] - 5,
                                'izquierdo' => $data_request['margen_izquierdo'] - 5 
                            ];
        
        $mostrar_notas_auxiliares = false;
        if ( $data_request['formato'] == 'pdf_boletines_7') {
            $mostrar_notas_auxiliares = true;
        }

        $mostrar_notas_periodos_anteriores = false;
        if ( in_array( $data_request['formato'], ['pdf_boletines_4', 'pdf_boletines_7'] ) ) {
            $mostrar_notas_periodos_anteriores = true;
        }
        
        $datos = $this->preparar_datos_boletin( $periodo, $curso, $matriculas, $mostrar_fallas, $mostrar_nombre_docentes, $mostrar_usuarios_estudiantes, $mostrar_notas_auxiliares, $mostrar_notas_periodos_anteriores );

        $url_imagen_marca_agua = config('matriculas.url_imagen_marca_agua');

        switch ($data_request['formato']) {
            case 'pdf_boletines_6':
                
                $periodos = $this->get_periodos_para_columnas($periodo,'menor_igual');
                
                $view =  $this->get_view_for_pdf_boletines_6($data_request['formato'], $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion,$mostrar_intensidad_horaria, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina,$ancho_columna_asignatura,$mostrar_logros,$with_page_breaks);

                break;
            
            case 'pdf_boletines_7':
                    $periodos = $this->get_periodos_para_columnas($periodo,'menor');

                    $view =  $this->get_view_for_pdf_boletines_7($data_request['formato'], $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion,$mostrar_intensidad_horaria, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina,$ancho_columna_asignatura,$mostrar_logros,$with_page_breaks);
                    break;

            case 'pdf_boletines_9_desempenios':
                
                $periodos = $this->get_periodos_para_columnas($periodo,'menor_igual');
                
                $view =  $this->get_view_for_pdf_boletines_9_desempenios($data_request['formato'], $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion,$mostrar_intensidad_horaria, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina,$ancho_columna_asignatura,$mostrar_logros,$with_page_breaks);

                break;
            
            default:
                $periodos = $this->get_periodos_para_columnas($periodo,'menor_igual');
                    
                $view =  View::make('calificaciones.boletines.'.$data_request['formato'], compact( 'colegio', 'curso', 'periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'datos','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','ancho_columna_asignatura','mostrar_logros','with_page_breaks') )->render();
                break;
        }

        return $view;
    }
    
    /**
     * 
     */
    public function get_periodos_para_columnas($periodo, $operador)
    {
        $periodos = Periodo::get_activos_periodo_lectivo( $periodo->periodo_lectivo_id );
        // Excluir el periodo final
        foreach ($periodos as $key => $value)
        {
            if ( $value->periodo_de_promedios )
            {
                unset( $periodos[$key] );
            }
        }

        $new_periodos = collect([]);
        foreach ($periodos as $periodo_lista) {
            if ($operador = 'menor_igual') {
                if ($periodo_lista->numero <= $periodo->numero) {
                    $new_periodos->push($periodo_lista);
                }
            }else{
                if ($periodo_lista->numero < $periodo->numero) {
                    $new_periodos->push($periodo_lista);
                }
            }
            
        }

        return $new_periodos;
    }
    
    /**
     * 
     */
    public function get_view_for_pdf_boletines_6($formato, $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion,$mostrar_intensidad_horaria, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina,$ancho_columna_asignatura, $mostrar_logros, $with_page_breaks)
    {
        $lbl_numero_periodo = $this->get_label_periodo($periodo);

        $all_boletines = '';

        foreach($datos as $registro)
        {
            $obj_lineas_cuerpo_boletin = $this->dividir_lineas_cuerpo_boletin($registro->cuerpo_boletin->lineas,$cantidad_caracteres_para_proxima_pagina);

            $lineas_cuerpo_boletin = $obj_lineas_cuerpo_boletin->first_group;

            if (empty($obj_lineas_cuerpo_boletin->second_group)) {
                $front_side =  View::make( 'calificaciones.boletines.pdf_boletines_6_onepage', compact( 'colegio', 'curso', 'periodo','lbl_numero_periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin','ancho_columna_asignatura','mostrar_logros','with_page_breaks') )->render();
                $back_side = '';
            }else{
                $front_side =  View::make( 'calificaciones.boletines.pdf_boletines_6_frontside', compact( 'colegio', 'curso', 'periodo','lbl_numero_periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin','ancho_columna_asignatura','mostrar_logros','with_page_breaks') )->render();
                
                $lineas_cuerpo_boletin = $obj_lineas_cuerpo_boletin->second_group;

                $back_side =  View::make( 'calificaciones.boletines.pdf_boletines_6_backside', compact( 'colegio', 'curso', 'periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin','ancho_columna_asignatura','mostrar_logros','with_page_breaks') )->render();
            }                

            $all_boletines .= $front_side . $back_side;
        }

        return View::make( 'calificaciones.boletines.pdf_boletines_6', compact( 'all_boletines','curso', 'tam_hoja', 'tam_letra','margenes', 'mostrar_areas'))->render();
    }
    
    /**
     * 
     */
    public function get_view_for_pdf_boletines_7($formato, $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion,$mostrar_intensidad_horaria, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina,$ancho_columna_asignatura, $mostrar_logros, $with_page_breaks)
    {
        $lbl_calificaciones_aux = (new CalificacionesService())->get_object_calificaciones_auxiliares($periodo->id, $curso->id);

        return  View::make('calificaciones.boletines.'.$formato, compact( 'colegio', 'curso', 'periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'datos','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','ancho_columna_asignatura','mostrar_logros','lbl_calificaciones_aux', 'with_page_breaks') )->render();
    }
    
    /**
     * 
     */
    public function get_view_for_pdf_boletines_9_desempenios($formato, $colegio, $curso, $periodo, $convetir_logros_mayusculas, $mostrar_areas, $mostrar_calificacion_media_areas, $mostrar_fallas, $mostrar_nombre_docentes,$mostrar_escala_valoracion,$mostrar_usuarios_estudiantes, $mostrar_etiqueta_final, $tam_hoja, $tam_letra, $firmas, $datos,$margenes,$mostrar_nota_nivelacion,$mostrar_intensidad_horaria, $matriculas, $anio, $periodos, $url_imagen_marca_agua,$cantidad_caracteres_para_proxima_pagina,$ancho_columna_asignatura, $mostrar_logros, $with_page_breaks)
    {
        $lbl_numero_periodo = $this->get_label_periodo($periodo);

        $all_boletines = '';

        $logros = Logro::where([
            'periodo_id' => $periodo->id,
            'curso_id' => $curso->id,
            'escala_valoracion_id' => 0
        ])->get();

        $todas_las_calificaciones = CalificacionDesempenio::where([
            'periodo_id' => $periodo->id,
            'curso_id' => $curso->id
        ])->get();

        foreach($datos as $registro)
        {
            $obj_lineas_cuerpo_boletin = $this->dividir_lineas_cuerpo_boletin_desempenios($registro->cuerpo_boletin->lineas, $cantidad_caracteres_para_proxima_pagina, $logros);

            $lineas_cuerpo_boletin = $obj_lineas_cuerpo_boletin->first_group;

            if (empty($obj_lineas_cuerpo_boletin->second_group)) {
                $front_side =  View::make( 'calificaciones.boletines.pdf_boletines_9_desempenios_onepage', compact( 'colegio', 'curso', 'periodo','lbl_numero_periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin','ancho_columna_asignatura','mostrar_logros','with_page_breaks', 'logros', 'todas_las_calificaciones') )->render();
                $back_side = '';
            }else{
                $front_side =  View::make( 'calificaciones.boletines.pdf_boletines_9_desempenios_frontside', compact( 'colegio', 'curso', 'periodo','lbl_numero_periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin','ancho_columna_asignatura','mostrar_logros','with_page_breaks', 'logros', 'todas_las_calificaciones') )->render();
                
                $lineas_cuerpo_boletin = $obj_lineas_cuerpo_boletin->second_group;

                $back_side =  View::make( 'calificaciones.boletines.pdf_boletines_9_desempenios_backside', compact( 'colegio', 'curso', 'periodo', 'convetir_logros_mayusculas', 'mostrar_areas', 'mostrar_calificacion_media_areas', 'mostrar_fallas', 'mostrar_nombre_docentes','mostrar_escala_valoracion','mostrar_usuarios_estudiantes', 'mostrar_etiqueta_final', 'tam_hoja', 'tam_letra', 'firmas', 'registro','margenes','mostrar_nota_nivelacion', 'mostrar_intensidad_horaria', 'matriculas', 'anio', 'periodos', 'url_imagen_marca_agua','lineas_cuerpo_boletin','ancho_columna_asignatura','mostrar_logros','with_page_breaks', 'logros', 'todas_las_calificaciones') )->render();
            }                

            $all_boletines .= $front_side . $back_side;
        }

        return View::make( 'calificaciones.boletines.pdf_boletines_9_desempenios', compact( 'all_boletines','curso', 'tam_hoja', 'tam_letra','margenes', 'mostrar_areas'))->render();
    }
    
    /**
     * 
     */
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
    
    /**
     * 
     */
    public function dividir_lineas_cuerpo_boletin_desempenios($lineas, $cantidad_caracteres_para_proxima_pagina, $logros)
    {
        $cant_caracteres = 0;
        $obj_lineas = (object)[
            'first_group' => [],
            'second_group' => []
        ];

        foreach ($lineas as $key => $linea) {

            $asignatura = $linea->asignacion_asignatura->asignatura;

            $logros_asignatura = $logros->where( 'asignatura_id', $asignatura->id )->all();

            foreach( $logros_asignatura as $un_logro )
            {
                $cant_caracteres += strlen($un_logro->descripcion);
            }

            if ( $cant_caracteres < $cantidad_caracteres_para_proxima_pagina) {
                $obj_lineas->first_group[] = $linea;
            }else{
                $obj_lineas->second_group[] = $linea;
            }
        }

        return $obj_lineas;
    }
    
    /**
     * 
     */
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

    /**
     * 
     */
    public function preparar_datos_boletin( $periodo, $curso, $matriculas, $mostrar_fallas, $mostrar_nombre_docentes, $mostrar_usuarios_estudiantes, $mostrar_notas_auxiliares, $mostrar_notas_periodos_anteriores )
    {
        $all_passwords = collect([]);
        if ($mostrar_usuarios_estudiantes) {
            $all_passwords = PasswordReset::all();
        }

        $observaciones_del_curso_en_el_periodo = ObservacionesBoletin::where( [
                                                ['id_periodo', '=', $periodo->id],
                                                ['curso_id', '=', $curso->id]
                                            ])->select('id', 'id_periodo','curso_id','id_estudiante','observacion','puesto')
                                            ->get();
        
        if ($mostrar_notas_periodos_anteriores) {
            $calificaciones_del_curso_en_el_periodo = Calificacion::where( [
                                        ['curso_id', '=', $curso->id]
                                    ])->select('id', 'id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'calificacion', 'logros')
                                    ->get();

            $notas_nivelacion_del_curso_en_el_periodo = NotaNivelacion::where([
                                            ['curso_id', '=', $curso->id]
                                        ]
                                    )
                                    ->get();
        }else{
            $calificaciones_del_curso_en_el_periodo = Calificacion::where( [
                                            ['id_periodo', '=', $periodo->id],
                                            ['curso_id', '=', $curso->id]
                                        ])->select('id', 'id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'calificacion', 'logros')
                                        ->get();

            $notas_nivelacion_del_curso_en_el_periodo = NotaNivelacion::where([
                                            ['periodo_id', '=', $periodo->id],
                                            ['curso_id', '=', $curso->id]
                                        ]
                                    )
                                    ->get();
        }       
        
        $calificaciones_auxiliares_del_curso_en_el_periodo = collect([]);
        if ($mostrar_notas_auxiliares) {
            $calificaciones_auxiliares_del_curso_en_el_periodo = CalificacionAuxiliar::where([
                                        ['id_periodo', '=', $periodo->id],
                                        ['curso_id', '=', $curso->id]
                                    ])->get();
        }

        $escalas_valoracion_periodo_lectivo = EscalaValoracion::where([
                                                ['periodo_lectivo_id', '=', $periodo->periodo_lectivo_id]
                                            ])->select('id', 'periodo_lectivo_id','calificacion_minima','calificacion_maxima','nombre_escala','imagen')
                                            ->get();

        $logros_del_curso_en_el_periodo = Logro::where( [
                                            ['periodo_id', '=', $periodo->id],
                                            ['curso_id', '=', $curso->id]
                                        ])->select('id', 'codigo', 'asignatura_id', 'descripcion', 'escala_valoracion_id', 'curso_id', 'periodo_id')
                                        ->get();
                                        
        $metas_del_curso_en_el_periodo = collect([]);
        if( config( 'calificaciones.colegio_maneja_metas' ) == 'Si' )
        {
            $metas_del_curso_en_el_periodo = Meta::where( [
                                                ['periodo_id', '=', $periodo->id],
                                                ['curso_id', '=', $curso->id]
                                            ])->select('id', 'codigo', 'periodo_id', 'curso_id', 'asignatura_id', 'descripcion')
                                            ->get();
        }
        
        $profesores_del_curso_en_el_periodo_lectivo = collect([]);
        if ($mostrar_nombre_docentes) {
            $profesores_del_curso_en_el_periodo_lectivo = AsignacionProfesor::where( [
                                    ['periodo_lectivo_id', '=', $periodo->periodo_lectivo_id],
                                    ['curso_id', '=', $curso->id]
                                ])
                                ->get();
        }        
        
        $anotaciones_del_curso_en_el_periodo = collect([]);
        if( config( 'calificaciones.manejar_preinformes_academicos' ) == 'Si' )
        {
            $anotaciones_del_curso_en_el_periodo = PreinformeAcademico::where([
                                        ['id_periodo', '=', $periodo->id],
                                        ['curso_id', '=', $curso->id]
                                    ])->select('id', 'id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'anotacion')
                                    ->get();
        }
        
        $asistencias_del_curso_en_el_periodo = collect([]);
        if ($mostrar_fallas) {
            $asistencias_del_curso_en_el_periodo = AsistenciaClase::whereBetween('fecha',               [$periodo->fecha_desde, $periodo->fecha_hasta])
                                        ->where( [
                                            ['curso_id', '=', $curso->id] 
                                        ])->select('id', 'id_estudiante', 'curso_id', 'asignatura_id', 'fecha', 'asistio')
                                        ->get();
        }

        $asignaturas_asignadas = CursoTieneAsignatura::with('asignatura')->where([
                                            ['curso_id', '=', $curso->id],
                                            ['periodo_lectivo_id', '=', $periodo->periodo_lectivo_id]
                                        ])
                                        ->orderBy('orden_boletin')
                                        ->get();

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
            $datos->estudiantes[$l]->matricula = $matricula;
            $datos->estudiantes[$l]->password_estudiante = $all_passwords->where( 'email', $matricula->estudiante->tercero->email )->first();
            $datos->estudiantes[$l]->observacion = $observaciones_del_curso_en_el_periodo->where('id_estudiante', $matricula->estudiante->id )->first();

            $a = 0;
            $cuerpo_boletin = (object)[];
            
            foreach ($asignaturas_asignadas as $asignacion)
            {
                $cuerpo_boletin->lineas[$a] = (object)[];
                $cuerpo_boletin->lineas[$a]->asignacion_asignatura = $asignacion;
                $cuerpo_boletin->lineas[$a]->asignatura_descripcion = $asignacion->asignatura->descripcion;
                $cuerpo_boletin->lineas[$a]->asignatura_id = $asignacion->asignatura->id;
                $cuerpo_boletin->lineas[$a]->maneja_calificacion = $asignacion->maneja_calificacion;
                $cuerpo_boletin->lineas[$a]->intensidad_horaria = $asignacion->intensidad_horaria;
                $cuerpo_boletin->lineas[$a]->area = $asignacion->asignatura->area;
                $cuerpo_boletin->lineas[$a]->area_descripcion = $asignacion->asignatura->area->descripcion;
                $cuerpo_boletin->lineas[$a]->area_id = $asignacion->asignatura->area->id;

                

                $cuerpo_boletin->lineas[$a]->calificaciones_todos_los_periodos_asignatura_estudiante = [];
                if ($mostrar_notas_periodos_anteriores) {
                    $calificacion = $calificaciones_del_curso_en_el_periodo->where('id_periodo', $periodo->id)->where('id_asignatura',$asignacion->asignatura_id)->where('id_estudiante', $matricula->estudiante->id )->first();

                    $cuerpo_boletin->lineas[$a]->calificaciones_todos_los_periodos_asignatura_estudiante = $calificaciones_del_curso_en_el_periodo->where('id_asignatura',$asignacion->asignatura_id)->where('id_estudiante', $matricula->estudiante->id );

                    $cuerpo_boletin->lineas[$a]->calificaciones_niveladas_todos_los_periodos_asignatura_estudiante = $notas_nivelacion_del_curso_en_el_periodo->where('asignatura_id',$asignacion->asignatura_id)->where('estudiante_id', $matricula->estudiante->id );

                }else{
                    $calificacion = $calificaciones_del_curso_en_el_periodo->where('id_asignatura',$asignacion->asignatura_id)->where('id_estudiante', $matricula->estudiante->id )->first();
                }

                $cuerpo_boletin->lineas[$a]->calificacion = $calificacion;

                if( $asignacion->asignatura == null)
                {
                    // La asignatura no existe
                    dd('NO hay registros de asignauras para esa asignacion.', $asignacion);
                }

                $cuerpo_boletin->lineas[$a]->area_id = $asignacion->asignatura->area_id;
                $cuerpo_boletin->lineas[$a]->peso_asignatura = $asignacion->peso;
                $cuerpo_boletin->lineas[$a]->valor_calificacion = 0;

                $cuerpo_boletin->lineas[$a]->escala_valoracion = null;
                $cuerpo_boletin->lineas[$a]->logros = null;
                $cuerpo_boletin->lineas[$a]->logros_adicionales = null;
                $cuerpo_boletin->lineas[$a]->calificacion_nivelacion = null;

                $cuerpo_boletin->lineas[$a]->calificaciones_auxiliares = $calificaciones_auxiliares_del_curso_en_el_periodo->where('id_asignatura',$asignacion->asignatura_id)->where('id_estudiante', $matricula->estudiante->id )->first();

                if ( $calificacion != null )
                {
                    $valor_calificacion = $calificacion->calificacion;

                    $calificacion_nivelada = $notas_nivelacion_del_curso_en_el_periodo->where('asignatura_id',$asignacion->asignatura_id)->where('estudiante_id', $matricula->estudiante->id )->first();

                    if( $calificacion_nivelada != null )
                    {
                        $valor_calificacion = $calificacion_nivelada->calificacion;
                        
                        $cuerpo_boletin->lineas[$a]->calificacion_nivelacion = $calificacion_nivelada->calificacion;
                    }

                    $escala_valoracion = $this->get_escala_valoracion($escalas_valoracion_periodo_lectivo, $valor_calificacion);
                    
                    if( $escala_valoracion == null )
                    {
                        dd( 'Por favor corrija la calificacion. No hay configurada una Escala de valoracion para la calificacion ' . $valor_calificacion . '. Curso: ' . $curso->descripcion . '. Asignatura: ' . $asignacion->asignatura->descripcion . '. Estudiante: ' . $matricula->estudiante->tercero->descripcion );
                    }

                    $cuerpo_boletin->lineas[$a]->escala_valoracion = $escala_valoracion;

                    $cuerpo_boletin->lineas[$a]->logros = $logros_del_curso_en_el_periodo->where('asignatura_id',$asignacion->asignatura_id)->where('escala_valoracion_id', $escala_valoracion->id )->all();

                    $cuerpo_boletin->lineas[$a]->logros_adicionales = $this->get_logros_adicionales( $logros_del_curso_en_el_periodo, $calificacion, $asignacion->asignatura_id );

                    $cuerpo_boletin->lineas[$a]->valor_calificacion = $valor_calificacion;
                }

                $cuerpo_boletin->lineas[$a]->propositos = $metas_del_curso_en_el_periodo->where('asignatura_id', $asignacion->asignatura_id )->all();
                
                $cuerpo_boletin->lineas[$a]->profesor_asignatura = $this->get_profesor_de_la_asignatura( $profesores_del_curso_en_el_periodo_lectivo, $asignacion->asignatura_id);

                $anotacion = $anotaciones_del_curso_en_el_periodo->where('id_asignatura',  $asignacion->asignatura_id)->where('id_estudiante', $matricula->estudiante->id)->first();
                
                $cuerpo_boletin->lineas[$a]->anotacion = '';
                if ( $anotacion != null ) 
                {
                    $cuerpo_boletin->lineas[$a]->anotacion = $anotacion->anotacion;
                }

                // Registro de inasistencias
                $cuerpo_boletin->lineas[$a]->fallas = $this->get_inasistencias_estudiante_asignatura( $asistencias_del_curso_en_el_periodo, $matricula->estudiante->id, $asignacion->asignatura_id);
                
                $a++;
            } // Fin - Por cada asignatura

            $datos->estudiantes[$l]->cuerpo_boletin = $cuerpo_boletin;

            $l++;
        }
        
        return $datos->estudiantes;
    }
    
    /**
     * 
     */
    public function get_inasistencias_estudiante_asignatura( $asistencias_del_curso_en_el_periodo, $estudiante_id, $asignatura_id)
    {
        $cant_fallas = 0;

        foreach ($asistencias_del_curso_en_el_periodo as $asistencia) {
            if ( $asistencia->asistio == 'No' && $asistencia->id_estudiante == $estudiante_id && $asistencia->asignatura_id == $asignatura_id ) {
                $cant_fallas++;
            }
        }
        
        return $cant_fallas;
    }
    
    /**
     * 
     */
    public function get_profesor_de_la_asignatura( $profesores_del_curso_en_el_periodo_lectivo, $asignatura_id )
    {
        return $profesores_del_curso_en_el_periodo_lectivo->where('id_asignatura',$asignatura_id)
                                    ->first();
    }
    
    /**
     * 
     */
    public function get_escala_valoracion($escalas_valoracion_periodo_lectivo, $valor_calificacion)
    {
        foreach ($escalas_valoracion_periodo_lectivo as $escala) {
            if( $escala->calificacion_minima <= $valor_calificacion && $escala->calificacion_maxima >= $valor_calificacion)
            {
                return $escala;
            }
        }

        return null;
    }
    
    /**
     * 
     */
    public function get_logros_adicionales( $logros_del_curso_en_el_periodo, $calificacion, $asignatura_id )
    {
        $vec_logros = explode( ",", $calificacion->logros);

        $all_logros = collect([]);
        foreach ($logros_del_curso_en_el_periodo as $logro) {
            if( $logro->asignatura_id == $asignatura_id && in_array($logro->codigo, $vec_logros) )
            {
                $all_logros->push($logro);
            }
        }
        
        if ( $all_logros->isEmpty() ) {
            return null;
        }

        return $all_logros;
    }
    
    /**
     * 
     */
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
    
    /**
     * Muestra formulario para el cálculo del puesto (g = get)
     */
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

        NOTA: NO ESTA TENIENDO EN CUENTA LA NOTA DE NIVELACION. SERÍA INJUSTO QUE ALGUIEN QUE NIVELE OCUPE LOS PRIMERO PUESTOS. AUNQUE EN EL PERIODO FINAL SI ENTRA LA NOTA DE NIVELACION.
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

			$i=1;
			$promedio_anterior = 0;
			foreach($promedios as $promedio)
            {
                if( $promedio->promedioCalificaciones != $promedio_anterior)
                {
				    $vec_puestos [$i] = $promedio->promedioCalificaciones;
				    $i++;
				    $promedio_anterior = $promedio->promedioCalificaciones;
                }
				
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
					$reg_observacion = ObservacionesBoletin::where(
                                                                [ 'id_colegio' => $colegio->id,
                                                                    'id_periodo' => $request->id_periodo,
                                                                    'curso_id' => $request->curso_id,
                                                                    'id_estudiante' => $fila->id_estudiante
                                                                ])
                                                                ->get()
                                                                ->first();
					if ($reg_observacion != null) {
                        $reg_observacion->puesto = $el_puesto;
                        $reg_observacion->save();
                    }
                                                                
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
}
