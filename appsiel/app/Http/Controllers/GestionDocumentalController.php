<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;
use View;
use Input;
use DB;
use Form;

// Modelos
use App\Core\Colegio;
use App\Core\DifoFormato;
use App\Core\Configuracion;
use App\Matriculas\Matricula;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;
use App\Core\FirmaAutorizada;
use App\Calificaciones\Periodo;

class GestionDocumentalController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect('gestion_documental/imprimir_formato?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'));
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
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
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
                $pdf = \App::make('dompdf.wrapper');
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
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
                return $pdf->download('certificado_quinto.pdf');
                
                break;
            case '4':
                $view =  View::make('gestion_documental.pdf.hoja_membreteada', compact('colegio','tam_letra','tam_hoja'))->render();
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
                return $pdf->download('hoja_membreteada.pdf');

                //echo "hola mundo";

                //return view('gestion_documental.pdf.hoja_membreteada', compact('colegio','tam_letra','tam_hoja'));
                break;

            default:
                $view =  View::make('gestion_documental.pdf.mi_formato', compact('colegio','request','tam_hoja'))->render();
                $pdf = \App::make('dompdf.wrapper');
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
                ['url'=>'gestion_documental?id='.Input::get('id'),'etiqueta'=>'Gestión documental'],
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
        
        /*echo $view;*/
        
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);
        return $pdf->download($formato->descripcion.'.pdf');
        
    }

    public static function get_select_estudiantes_del_curso( $curso_id )
    {
        $registros = Matricula::estudiantes_matriculados( $curso_id, null, null );

        $opciones = '<option value=""></option>';
        foreach ($registros as $campo) {
            $opciones .= '<option value="'.$campo->id_estudiante.'-'.$campo->codigo.'">'.$campo->nombre_completo.'</option>';
        }

        return $opciones;
    }
}
