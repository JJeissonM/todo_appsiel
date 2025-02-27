<?php

namespace App\Http\Controllers;

use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\EscalaValoracion;
use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

// Modelos
use App\Core\Colegio;
use App\Core\DifoFormato;

use App\Matriculas\Matricula;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;
use App\Core\FirmaAutorizada;
use App\Calificaciones\Periodo;
use App\Http\Controllers\Core\ConfiguracionController;
use App\Matriculas\PeriodoLectivo;
use App\Tesoreria\TesoLibretasPago;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class GestionDocumentalController extends ModeloController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $miga_pan = [[ 'url' => 'NO', 'etiqueta' => 'Gestión Documental']];

        return view('gestion_documental.index', compact('miga_pan') );
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
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $tam_hoja = $request->tam_hoja;
        $orientacion=$request->orientacion;
        $tam_letra=$request->tam_letra;

        switch ($id) {

            case '1':
                $id_formato=$request->id_formato;
                $opcion_estudiante = explode("-",$request->id_estudiante);
                $estudiante = Estudiante::find($opcion_estudiante[0]);
                $curso=Curso::find($opcion_estudiante[1]);
                $id_firma_autorizada=$request->id_firma_autorizada;

                $view =  View::make('gestion_documental.pdf.constancias', compact('colegio','id_formato','estudiante','curso','id_firma_autorizada','tam_letra','tam_hoja'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
                return $pdf->download('constancia_observador.pdf');

                //return view('gestion_documental.pdf.constancias', compact('colegio','id_formato','id_estudiante','id_firma_autorizada','tam_letra','tam_hoja'));

                //echo "hola mundo";
                break;

            case '2':
                $anio=$request->anio;
                $curso_id=$request->curso_id;
                $id_periodo=$request->id_periodo;
                //$opcion_impresion=$request->opcion_impresion;
                $id_formato=$request->id_formato;
                //$id_estudiante=$request->id_estudiante;

                $view =  View::make('gestion_documental.pdf.certificado_quinto', compact('colegio','anio','id_formato','curso_id','id_periodo','tam_letra','tam_hoja'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
                return $pdf->download('certificado_quinto.pdf');
                
                break;
            case '4':
                $view =  View::make('gestion_documental.pdf.hoja_membreteada', compact('colegio','tam_letra','tam_hoja'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
                return $pdf->download('hoja_membreteada.pdf');

                //echo "hola mundo";

                //return view('gestion_documental.pdf.hoja_membreteada', compact('colegio','tam_letra','tam_hoja'));
                break;

            default:
                $view =  View::make('gestion_documental.pdf.mi_formato', compact('colegio','request','tam_hoja'))->render();
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
                return $pdf->download('formato.pdf');
                //return view('gestion_documental.pdf.mi_formato',compact('colegio','request'));
                break;
        }
        
    }


    // Formulario para la generacion de formatos
    public function imprimir_formato()
    {
        $opciones= DifoFormato::where('estado','Activo')->get();
        
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->descripcion;
        }
        
        $formatos = $vec;

        $miga_pan = [
                ['url'=>'gestion_documental?id=' . Input::get('id'),'etiqueta'=>'Gestión documental'],
                ['url'=>'NO','etiqueta'=>'Imprimir formatos']
            ];

        return view('gestion_documental.imprimir_formato',compact('formatos','miga_pan'));
    }

    public function cargar_controles($formato_id)
    {

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $formato = DifoFormato::find($formato_id);
        
        if($formato->nota_mensaje!=''){
            $nota_mensaje = $formato->nota_mensaje;
        }else{
            $nota_mensaje = '';
        }

        if($formato->maneja_anio=='Si')
        {
            $maneja_anio = Form::bsText('anio',date('Y'),"Año",['required' => 'required']);
        }else{
            $maneja_anio = '';
        }

        if($formato->maneja_periodo=='Si')
        {
            $opciones = Periodo::all();
            unset($vec);
            foreach ($opciones as $opcion){
                $vec[$opcion->id]=$opcion->descripcion;
            }
            $periodos = $vec;

            $maneja_periodo = Form::bsSelect('id_periodo', $formato->periodo_predeterminado, 'Seleccionar periodo', $periodos ,['required' => 'required']).Form::bsCheckBox('periodos_promediar','','Periodos a promediar',$periodos,['required' => 'required']);
        }else{
            $maneja_periodo = '';
        }

        if($formato->maneja_curso=='Si')
        {
            $opciones = Curso::all();
            unset($vec);
            $vec[''] = '';
            foreach ($opciones as $opcion){
                $vec[$opcion->id]=$opcion->descripcion;
            }
            $cursos = $vec;

            $maneja_curso = Form::bsSelect('curso_id', $formato->curso_predeterminado, 'Seleccionar curso', $cursos ,['required' => 'required']);
        }else{
            $maneja_curso = '';
        }

        if($formato->maneja_estudiantes == 'Si')
        {
            $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres," (",cursos.descripcion,")") AS nombre_estudiante';

            $estudiantes = DB::table('matriculas')
                            ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'matriculas.id_estudiante')
                            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'matriculas.curso_id')
                            ->select(DB::raw($select_raw),'matriculas.id_estudiante','matriculas.codigo')
                            ->where('sga_estudiantes.id_colegio', $colegio->id)
                            ->orderBy('sga_estudiantes.apellido1', 'ASC')
                            ->get();

            unset($vec);
            $vec[''] = '';
            foreach ($estudiantes as $opcion)
            {
                $vec[$opcion->id_estudiante.'-'.$opcion->codigo] = $opcion->nombre_estudiante;
            }

            if ( $formato->maneja_curso == 'Si' ) {
                unset($vec);
                $vec[''] = '';
            }

            $maneja_estudiantes = Form::bsSelect('id_estudiante',null, 'Estudiante', $vec ,['class'=>'combobox','required' => 'required']);

        }else{
            $maneja_estudiantes = '';
        }

        if($formato->maneja_firma_autorizada=='Si')
        {

            $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2," (",core_firmas_autorizadas.titulo_tercero,")") AS nombre_tercero';

            $firmas = FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
                    ->select(DB::raw($select_raw),'core_firmas_autorizadas.id')
                    ->orderBy('core_terceros.nombre1', 'ASC')
                    ->get();

                unset($vec);
                $vec[''] = '';
                foreach ($firmas as $opcion){                            
                    $vec[$opcion->id]= $opcion->nombre_tercero;
                }
            
            $maneja_firma_autorizada = Form::bsSelect('id_firma_autorizada',null, 'Firma autorizada', $vec ,['required' => 'required']);
        }else{
            $maneja_firma_autorizada = '';
        }

        $view =  View::make('gestion_documental.incluir.controles_imprimir_formato', compact('nota_mensaje','maneja_anio','maneja_periodo','maneja_curso','maneja_estudiantes','maneja_firma_autorizada','formato_id'))->render();

        return $view;

    }

    public function generar_formato(Request $request)
    {
        $tam_hoja = $request->tam_hoja;
        $orientacion=$request->orientacion;
        //$tam_letra=$request->tam_letra; // El tamaño de letra lo debe tener la seccion del formato

        $formato = DifoFormato::find($request->formato_id);

        $view = View::make('core.dis_formatos.plantillas.'.$formato->plantilla, compact('formato','request'))->render();
        
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);
        return $pdf->download($formato->descripcion.'.pdf');
        
    }

    public static function get_select_estudiantes_del_curso( $curso_id )
    {
        $registros = Matricula::estudiantes_matriculados( $curso_id, null, null, null );

        $opciones = '<option value=""></option>';
        foreach ($registros as $campo) {
            $opciones .= '<option value="'.$campo->id_estudiante.'-'.$campo->codigo.'">'.$campo->nombre_completo.'</option>';
        }

        return $opciones;
    }

    /**
     * 
     */
    public function constancia_estudios( Request $request )
    {
        $estudiante = Estudiante::get_datos_basicos( (int)$request->estudiante_id );

        $matriculado = true;

        $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );

        $matricula = Matricula::get_matricula_periodo_lectivo_un_estudiante( (int)$request->estudiante_id, $request->periodo_lectivo_id );

        $libreta_pago = null;
        if ( !is_null($matricula) )
        {
            $libreta_pago = TesoLibretasPago::where( 'matricula_id', $matricula->id )->get()->first();
        }else{
            $matriculado = false;
        }
        
        $curso = Curso::find( $request->curso_id );

        $tam_hoja = $request->tam_hoja;
        $detalla_valores_matricula_pension = $request->detalla_valores_matricula_pension;

        $array_fecha = [ date('d'), ConfiguracionController::nombre_mes( date('m') ), date('Y') ];

        if ( $request->fecha_expedicion != '' )
        {
            $fecha = explode('-', $request->fecha_expedicion );
            $array_fecha = [ $fecha[2], ConfiguracionController::nombre_mes( $fecha[1] ), $fecha[0] ];            
        }
        
        $firma_autorizada_datos_1 = FirmaAutorizada::get_datos( $request->firma_autorizada_1 );
        $firma_autorizada_1 = FirmaAutorizada::find( $request->firma_autorizada_1 );

        $vista = View::make( 'core.dis_formatos.plantillas.constancia_estudios_estudiante', compact( 'estudiante', 'curso', 'periodo_lectivo', 'array_fecha', 'firma_autorizada_1','firma_autorizada_datos_1', 'tam_hoja', 'libreta_pago', 'detalla_valores_matricula_pension', 'matriculado' )  )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /**
     * 
     */
     public function certificado_notas( Request $request )
     {
         $estudiantes = Matricula::todos_estudiantes_matriculados( $request->curso_id, $request->periodo_lectivo_id );
         
         if( $request->estudiante_id != '' )
         {
             $estudiantes = $estudiantes->where('id_estudiante', (int)$request->estudiante_id)->all();
         }
 
         // Seleccionar asignaturas del grado
         $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $request->curso_id, null, $request->periodo_lectivo_id, null);
 
         $curso = Curso::find( $request->curso_id );
 
         $periodo_lectivo = PeriodoLectivo::find( $request->periodo_lectivo_id );
 
         $maxima_escala_valoracion = 1;
         $calificacion_maxima = EscalaValoracion::where( 'periodo_lectivo_id', $request->periodo_lectivo_id )->orderBy('calificacion_minima','DESC')->first();
         if ($calificacion_maxima != null) {
             $maxima_escala_valoracion = $calificacion_maxima->calificacion_maxima;
         }        
 
         $resultado_academico = $request->resultado_academico;
 
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
 
         $mostrar_promedio_calificaciones = (int)$request->mostrar_promedio_calificaciones;

         $firma_autorizada_1 = FirmaAutorizada::find( $request->firma_autorizada_1 );
         $firma_autorizada_2 = FirmaAutorizada::find( $request->firma_autorizada_2 );
         
         $parametros = config('gestion_documental');
 
         $mostrar_intensidad_horaria = $parametros['mostrar_intensidad_horaria'];
         $mostrar_numero_identificacion_estudiante = $parametros['mostrar_numero_identificacion_estudiante'];
         $mostrar_imagen_firma_autorizada_1 = $parametros['mostrar_imagen_firma_autorizada_1'];
         $mostrar_imagen_firma_autorizada_2 = $parametros['mostrar_imagen_firma_autorizada_2'];
 
         $vista = View::make( 'core.dis_formatos.plantillas.cetificados_notas.'.$request->estilo_formato, compact( 'estudiantes', 'asignaturas', 'curso', 'periodo_lectivo', 'periodo_id', 'array_fecha', 'firma_autorizada_1', 'firma_autorizada_2', 'observacion_adicional', 'tam_hoja', 'maxima_escala_valoracion', 'periodo', 'resultado_academico', 'mostrar_intensidad_horaria', 'mostrar_numero_identificacion_estudiante', 'mostrar_imagen_firma_autorizada_1', 'mostrar_imagen_firma_autorizada_2', 'mostrar_promedio_calificaciones' )  )->render();
 
         Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
 
         return $vista;
     }
}
