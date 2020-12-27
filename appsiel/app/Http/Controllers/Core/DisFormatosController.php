<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
    use App\Http\Controllers\Core\ConfiguracionController;

use App\Http\Requests;

use DB;
use Auth;
use Input;
use View;

use App\Core\DifoFormato;
use App\Core\DifoSeccion;
use App\Core\Aplicacion;
use App\Core\Empresa;
use App\Core\Colegio;
use App\Core\FirmaAutorizada;

use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;

use App\Calificaciones\Periodo;
use App\Calificaciones\Calificacion;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\EscalaValoracion;

use App\Tesoreria\TesoLibretasPago;


class DisFormatosController extends Controller
{

    protected $colegio;

    public function secciones_formato($id_formato)
    {
        $formato = DifoFormato::find($id_formato);
        $secciones_formato = DB::table('difo_secciones_formatos')->where('id_formato',$formato->id)->orderBy('orden','ASC')->get();

        $secciones_no_formato = DB::select(DB::raw('SELECT difo_secciones.id,difo_secciones.descripcion FROM difo_secciones LEFT JOIN difo_secciones_formatos ON (difo_secciones.id = difo_secciones_formatos.id_seccion) WHERE difo_secciones_formatos.id_formato IS NULL OR difo_secciones_formatos.id_formato <> '.$id_formato));

        //print_r($secciones_no_formato);
        return view('core.dis_formatos.secciones_formato',compact('formato','secciones_no_formato','secciones_formato'));
    }

    // Al
    public function guardar_asignacion(Request $request) {
        DB::insert('INSERT INTO difo_secciones_formatos (id_formato,id_seccion,orden) VALUES (?,?,?)',
                    [$request->id_formato,$request->id_seccion,$request->orden]);

        return redirect('/dis_formatos/secciones_formato/'.$request->id_formato)->with('flash_message','Sección agregada correctamente');
    }

    // Al
    public function eliminar_asignacion(Request $request) {
        DB::table('difo_secciones_formatos')->where('id', '=', $request->id)->delete();

        return redirect('/dis_formatos/secciones_formato/'.$request->id_formato)->with('mensaje_error','Sección eliminada correctamente');
    }


    public static function formatear_contenido( Request $request, $seccion, $estudiante) 
    {
        $empresa = Empresa::find(Auth::user()->empresa_id);

        $colegio = Colegio::where('empresa_id','=', $empresa->id )
                    ->get()[0];

        $anio = date('Y');
        $periodo_lectivo_id = null;

        if ( isset( $request->id_periodo ) ) {
            $periodo = Periodo::find($request->id_periodo);
            $periodo_lectivo_id = $periodo->periodo_lectivo_id;
            $anio = explode("-",$periodo->fecha_desde)[0];
        }

        $contenido = '';

        if ( isset($request->id_estudiante) !== false) 
        {
            $vec_estudiante = explode("-",$request->id_estudiante);

            $nombre_estudiante = Estudiante::get_nombre_completo($vec_estudiante[0],1);

            $contenido = str_replace("nombre_de_estudiante", '<b>'.trim($nombre_estudiante).'</b>',$seccion->contenido);

            // Para mostar el nombre del curso
            $matricula = Matricula::where('codigo',$vec_estudiante[1].'-'.$vec_estudiante[2])->get()[0];
            $curso = Curso::find($matricula->curso_id);
            $contenido = str_replace("nombre_curso", '<b>'.$curso->descripcion.'</b>', $contenido);

            // Para mostrar valores de matrícula y pensión
            $libreta_pago = TesoLibretasPago::where('id_estudiante',$vec_estudiante[0])->get();
                
                $valor_matricula = 0;
                $valor_pension_mensual = 0;

            if ( !is_null($libreta_pago) ) 
            {
                $valor_matricula = $libreta_pago[0]->valor_matricula;
                $valor_pension_mensual = $libreta_pago[0]->valor_pension_mensual;
            }

            $contenido = str_replace( "valor_matricula", '<b>$'.number_format( $valor_matricula, 0, ',', '.').'</b>', $contenido);
            $contenido = str_replace( "valor_pension_mensual", '<b>$'.number_format( $valor_pension_mensual, 0, ',', '.').'</b>', $contenido);
        }
        

        if ( isset($request->id_curso) !== false) 
        {
            $curso = Curso::find($matricula->curso_id);

            $contenido = str_replace("nombre_curso", '<b>'.$curso->descripcion.'</b>', $contenido);
        }           

        $contenido = str_replace("numero_dia_actual", date('d'), $contenido);

        $contenido = str_replace("numero_mes_actual", ConfiguracionController::nombre_mes(date('m')), $contenido);

        $contenido = str_replace("año_actual", date('Y'), $contenido);

        if ( isset($request->id_firma_autorizada) !== false) 
        {
            $firma_autorizada = DB::table('core_firmas_autorizadas')->where('id',$request->id_firma_autorizada)->get();

            $tercero = DB::table('core_terceros')->where('id',$firma_autorizada[0]->core_tercero_id)->get();

            $tipo_doc_id = DB::table('core_tipos_docs_id')->where('id',$tercero[0]->id_tipo_documento_id)->value('abreviatura');

            $nombre_tercero = $tercero[0]->nombre1." ".$tercero[0]->otros_nombres." ".$tercero[0]->apellido1." ".$tercero[0]->apellido2;
            $contenido = str_replace("nombre_tercero", $nombre_tercero, $contenido);

            $contenido = str_replace("tipo_documento_tercero", $tipo_doc_id, $contenido);

            $contenido = str_replace("numero_documento_tercero", number_format($tercero[0]->numero_identificacion, 0, ',', '.'), $contenido);

            $contenido = str_replace("ciudad_expedicion_documento", $tercero[0]->ciudad_expedicion, $contenido);

            $contenido = str_replace("titulo_tercero", $firma_autorizada[0]->titulo_tercero, $contenido);
        }

        $contenido = str_replace("ciudad_colegio", $colegio->ciudad, $contenido);

        $contenido = str_replace("nombre_del_colegio", $colegio->descripcion, $contenido);

        $contenido = str_replace("nueva_linea","<br/>", $contenido);

        if ($seccion->presentacion == 'tabla') 
        {
            $vec_estudiante = explode("-",$request->id_estudiante);
            $estudiante = Estudiante::find($vec_estudiante[0]);

            // Seleccionar asignaturas del grado
            $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($request->curso_id, null, $periodo_lectivo_id );

            $curso_id = $request->curso_id;
            $contenido.=View::make('core.dis_formatos.plantillas.tabla_asignaturas_calificacion',compact('asignaturas','colegio','anio','estudiante','curso_id','periodo'))->render();

            $contenido = str_replace("_tabla_", "", $contenido);

        }

        return $contenido;
    }

}
