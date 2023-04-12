<?php

namespace App\Http\Controllers\Calificaciones;



use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\Sistema\ModeloController;

use App\Calificaciones\Logro;
use App\Calificaciones\Asignatura;
use App\Calificaciones\ConsecutivoLogro;
use App\Calificaciones\EscalaValoracion;

use App\Calificaciones\Periodo;
use App\Matriculas\PeriodoLectivo;

use App\Core\Colegio;

use App\Sistema\Modelo;
use App\Sistema\SecuenciaCodigo;

use PDF;
use Auth;
use Input;
use DB;
use View;

class LogroController extends Controller
{
	
	public function __construct()
    {
		$this->middleware('auth');
    }
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**/

        $general = new ModeloController;
        $registro_creado = $general->crear_nuevo_registro( $request );

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];
        $id_colegio=$colegio->id;        

        // Se obtiene el consecutivo para actualizar el logro creado
        $registro = SecuenciaCodigo::where(['id_colegio'=>$id_colegio,'modulo'=>'logros'])->value('consecutivo');
        $consecutivo=$registro+1;

        // Actualizar el consecutivo
        SecuenciaCodigo::where(['id_colegio'=>$id_colegio,'modulo'=>'logros'])->increment('consecutivo');
        
        $registro_creado->codigo = $consecutivo;
        $registro_creado->save();
        
        $ruta = 'web';
        if($request->guardar_y_nuevo=="on")
        {
            $ruta = 'web/create';
        }

        // Para la Aplicación Académico Docente
        if ( strpos( $request->session()->previousUrl(), 'academico_docente' ) > 0 )
        {
            $ruta = 'academico_docente';
            if($request->guardar_y_nuevo=="on")
            {
                $ruta = 'academico_docente/ingresar_logros/'.$request->curso_id.'/'.$request->asignatura_id;
            }
        }
        
        return redirect($ruta.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Logro creado correctamente.');
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
		
		switch($id){
			case 'buscar':
                $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];
                $id_colegio=$colegio->id;
				
                $opciones="";
				$id_nivel = $request->id_nivel;
				$asignaturas = Asignatura::where(
                        [
                            ['nivel_grado', $id_nivel],
                            ['id_colegio',$id_colegio],
                            ['estado',"Activo"]
                        ])->get();

				$opciones .= '<option value="">Seleccionar...</option>';
				foreach ($asignaturas as $campo) {
					$opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
				}
				return $opciones;
				break;
			case 'editar':
				
				break;
				//return $id." ".$request->descripcion;
			case 'listado':
				//Preparar Vista

				$registros = DB::table('logros')
					->where('id_asignatura', $request->id_asignatura)
                    ->where('estado','Activo')
					->get();
				
				$nom_asignatura=Asignatura::where('id','=',$request->id_asignatura)->value('descripcion');
				
                $orientacion = $request->orientacion;
				$tam_hoja=$request->tam_hoja;
				$cantidad_lineas=$request->cantidad_lineas;
				$view =  View::make('calificaciones.logros.pdf_logros', compact('registros', 'nom_asignatura','tam_hoja','cantidad_lineas'))->render();
					
				//$oficio=array(216,326);
				//Renderizar PDF
				$pdf = \App::make('dompdf.wrapper');
				$pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
				return $pdf->download('logros_'.$nom_asignatura.'.pdf');//stream();
				break;
			default:

                $logro = Logro::find($id);

                $general = new ModeloController;
                $general->update( $request, $id );

                $ruta = 'web';

                // Para la Aplicación Académico Docente
                if ( strpos( $request->session()->previousUrl(), 'academico_docente' ) > 0 )
                {
                    $ruta = 'academico_docente/revisar_logros/'.$logro->curso_id.'/'.$logro->asignatura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo;
                }

                return redirect( $ruta.'/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');

				break;				
		}
    }


    public function proceso1()
    {
        //llenar_codigo_logros
        $cantidad=Logro::count();
        for($i=1;$i<=$cantidad;$i++){
            DB::table('logros')
                ->where('id', $i)
                ->update(['codigo' => $i]);
            //echo "<br/>".$i; Esta salida no funciona, el controlador solo manda un nual final.
        }
        //echo "cant. logros: ".$cantidad;
    }
	
    public function consultar($asignatura,$curso_id)
    {
        $logros = Logro::where('asignatura_id', $asignatura)
                        ->where('curso_id',$curso_id)
                        ->where('estado','Activo')
                        ->where('escala_valoracion_id',0)
                        ->get();

		return view('calificaciones.logros.consultar',['logros'=>$logros,'id_asignatura'=>$asignatura,'curso_id'=>$curso_id]);
    }
	

    /**
     * Muestra formulario para listar logros
     */
    public function listar()
    {
         $opciones1 = DB::table('niveles')->get();

        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id]=$opcion->descripcion;
        }

        $niveles = $vec1;

        $miga_pan = [
                        ['url'=>'calificaciones_logros?id='.Input::get('id'),'etiqueta'=>'Logros'],
                        ['url'=>'NO','etiqueta'=>'Listados']
                    ];

        return view('calificaciones.logros.listar',compact('niveles','miga_pan'));
    }
    
    // Elimina un logro
    public function eliminar_logros( $logro_id )
    {
        $logro = Logro::find($logro_id);

        // Validación #1
        $periodo = Periodo::find( $logro->periodo_id );
        if ( $periodo->cerrado )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Logro no puede ser eliminado, El periodo está cerrado. Código Logro: '.$logro->codigo );
        }

        // Validación #2
        $periodo_lectivo = PeriodoLectivo::find( $periodo->periodo_lectivo_id );
        if ( $periodo_lectivo->cerrado )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Logro no puede ser eliminado, El PERIODO LECTIVO está cerrado. Código Logro: '.$logro->codigo );
        }

        $logro->delete();

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Logro Eliminado correctamente.');
    }

    
    // Eliminar una escala de valoración
    public function eliminar_escala_valoracion( $id )
    {
        $logro = Logro::where('escala_valoracion_id',$id)->get()->first();

        if ( !is_null($logro) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Escala no puede ser eliminada, tiene un logro asociado. Código Logro: '.$logro->codigo );
        }

        $escala = EscalaValoracion::find($id);   
        $escala->delete();

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Escala de valoración eliminada correctamente.');
    }
	
}
