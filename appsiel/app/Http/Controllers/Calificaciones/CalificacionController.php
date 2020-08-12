<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
//use Illuminate\Database\Eloquent\Model;

use App\Matriculas\PeriodoLectivo;
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

use App\Core\Colegio;
use App\Sistema\Aplicacion;

use Input;
use DB;
use PDF;
use View;
use Auth;
use Lava;

class CalificacionController extends Controller
{
    protected $escala_valoracion;
    protected $colegio, $aplicacion;

    public function __construct()
    {
		$this->middleware('auth');

        $this->aplicacion = Aplicacion::find( Input::get('id') );

        if( Auth::check() ) 
        {
            $this->colegio = Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();
        }
    }
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if( !is_null($this->colegio) ) 
        {
            $periodo_id = '';
            $periodo_lbl = 'Todos';
            $periodos = Periodo::where( 'periodo_lectivo_id', PeriodoLectivo::get_actual()->id )->where('id_colegio',$this->colegio->id)->where('estado','Activo')->get();
            if ( Input::get('periodo_id')!==null ) 
            {
                $periodo_id = Input::get('periodo_id');
                if ( Input::get('periodo_id') != '') {
                    $periodo_lbl = Periodo::find(Input::get('periodo_id'))->descripcion;
                }
                
            }
            $vec1['']='Todos';
            foreach ($periodos as $opcion){
                $vec1[$opcion->id]=$opcion->descripcion;
            }
            $periodos = $vec1;

            $curso_id = '';
            $curso_lbl = 'Todos';
            $cursos = Curso::where('id_colegio',$this->colegio->id)->where('estado','Activo')->get();
            if ( Input::get('curso_id')!==null ) 
            {
                $curso_id = Input::get('curso_id');
                if ( Input::get('curso_id') != '') {
                    $curso_lbl = Curso::find(Input::get('curso_id'))->descripcion;
                }                
            }
            $vec2['']='Todos';
            foreach ($cursos as $opcion){
                $vec2[$opcion->id]=$opcion->descripcion;
            }
            $cursos = $vec2;


            $escalas = EscalaValoracion::where( 'periodo_lectivo_id', PeriodoLectivo::get_actual()->id )->orderBy('calificacion_minima','ASC')->get();


            // Gráfica de rendimiento académico
            $stocksTable1 = Lava::DataTable();
          
            $stocksTable1->addStringColumn('Escala')
                        ->addNumberColumn('Valor');
                        
            $tabla = [];
            $i = 0;
            $valor_total = 0;
            foreach ($escalas as $escala) 
            {
                $valor_calificacion = Calificacion::where('calificacion','>=', $escala->calificacion_minima)->where('calificacion','<=', $escala->calificacion_maxima)->where('id_periodo','LIKE', '%'.$periodo_id.'%')->where('curso_id','LIKE', '%'.$curso_id.'%')->avg('calificacion');

                $stocksTable1->addRow([$escala->nombre_escala, (float)$valor_calificacion]);

                $tabla[$i]['escala'] = $escala->nombre_escala;
                $tabla[$i]['valor'] = (float)$valor_calificacion;
                $valor_total += (float)$valor_calificacion;
                $i++;
            }

            $chart1 = Lava::PieChart('rendimiento_academico', $stocksTable1,[
                  'is3D'                  => True,
                  'pieSliceText'          => 'value'
              ]);

            $miga_pan = [
                    ['url' => $this->aplicacion->app.'?id='.Input::get('id'),'etiqueta'=> $this->aplicacion->descripcion],
                    ['url'=>'NO','etiqueta'=>'Informes y listados']
                ];

			return view('calificaciones.informes_listados', compact('miga_pan','periodos','periodo_id','periodo_lbl','cursos','curso_id','curso_lbl','tabla','valor_total'));
		}else{
			echo "La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.";
		}
    }


    public function index2()
    {
        if( !is_null($this->colegio) ) 
        {    
            $registros = Calificacion::get_calificaciones( $this->colegio->id, null, null );

            $miga_pan = [
                            ['url'=>'NO','etiqueta'=>'Calificaciones']
                        ];
            
            $titulo_tabla='';

            $encabezado_tabla=['Año','Periodo','Curso','Estudiante','Asignatura','Calificación',''];

            $ruta_modelo='calificaciones';

            $url_crear = 'calificaciones/create?id='.Input::get('id');
            $url_edit = '';
            $url_print = '';
            $url_ver = '';
            $url_estado = '';
            $url_eliminar = '';

            return view('layouts.index', compact('registros','miga_pan','url_crear','titulo_tabla','encabezado_tabla','url_crear','url_edit','url_print','url_ver','url_estado','url_eliminar'));
        }else{
            echo "La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.";
        }
    }   
    
					
    /**
     * Muestra el formulario de filtros, para enviar a ingresar las calificaiones.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $cursos = Curso::opciones_campo_select();
        $periodos = Periodo::opciones_campo_select();

        $miga_pan = [
                        [ 'url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta' => $this->aplicacion->descripcion ],
                        [ 'url' => 'NO', 'etiqueta' => 'Ingresar']
                    ];

		return view('calificaciones.create',compact('cursos','periodos','miga_pan'));
		// Lo datos del formulario create se envía vía post al método calificar2
    }

    /**
     * Llamar al formulario de Ingreso/Edición de calificaciones.
     *
     */
    public function calificar2(Request $request)
    {        
        // Validación del ingreso de calificaciones
        $parametros = config('calificaciones');

        if ($parametros['permitir_calificaciones_sin_logros'] == 'No') 
        {
            $logros = Logro::get_logros($this->colegio->id, $request->curso_id, $request->id_asignatura,$request->id_periodo);
            if (empty($logros)) {
                return redirect( url()->previous() )->with( 'mensaje_error','No se permite ingresar calificaciones para las asignaturas que aún no tienen logros en el periodo seleccionado.' );
            }
        }
        
        $periodo = Periodo::find( $request->id_periodo );

        // Warning!!!! El año se toma del periodo. Analizar si está bien.
        $anio = explode("-",$periodo->fecha_desde)[0];

        $periodo_lectivo = PeriodoLectivo::find( $periodo->periodo_lectivo_id );

        // Se obtienen los estudiantes con matriculas activas en el curso y el periodo lectivo
        /*
            WARNING!!!
            Si se envía estado 'Activo' NO muestra los estudiante cuando se van a calificar periodos lectivos anteriores.
            Si se envía estado null, muestra repetido a los estudiantes con matriculas en el mismo curso, por ejemplo cuando se equivocan en la matrícula y la hacen nuevamente en el mismo curso y periodo lectivo.
        */
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, null  );

        // Warning!!! No usar funciones de Eloquent en el controller (acoplamiento al framework) 
        $curso = Curso::find($request->curso_id);
        
        $datos_asignatura = CursoTieneAsignatura::get_datos_asignacion( $periodo->periodo_lectivo_id, $request->curso_id, $request->id_asignatura );

        if ( is_null( $datos_asignatura ) ) 
        {
            return redirect()->back()->with('mensaje_error', 'Hay problemas en la asignación de la asignatura al curso. Consulte con el administrador.');
        }

        $creado_por = Auth::user()->email;
        $modificado_por = ''; 

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
            $calificacion_est = Calificacion::where(['anio'=>$anio,'id_periodo'=>$request->id_periodo,
                                'curso_id'=>$request->curso_id,'id_asignatura'=>$request->id_asignatura,
                                'id_estudiante'=>$estudiante->id_estudiante])
                                ->get()
                                ->first();
            
            // Si el estudiante tiene calificacion se envian los datos de esta para editar
            if( !is_null($calificacion_est) )
            {
                $creado_por = $calificacion_est->creado_por;
                $modificado_por = Auth::user()->email;
                // Obtener la calificación auxiliar del estudiante
                $calificacion_aux = CalificacionAuxiliar::where(['anio'=>$anio,'id_periodo'=>$request->id_periodo,
                                'curso_id'=>$request->curso_id,'id_asignatura'=>$request->id_asignatura,
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

        $escala_min_max = EscalaValoracion::get_min_max( $periodo->periodo_lectivo_id );
        
        $id_app = $request->id_app;

        $miga_pan = [
                        [ 'url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta' => $this->aplicacion->descripcion ],
                        ['url'=>'NO','etiqueta'=>'Ingresar periodo '.$periodo->descripcion]
                    ];

        return view('calificaciones.calificar2',[ 'vec_estudiantes'=>$vec_estudiantes,
                'cantidad_estudiantes'=>count($estudiantes),
                'anio'=>$anio,
                'curso'=>$curso,
                'periodo'=>$periodo,
                'periodo_lectivo'=>$periodo_lectivo,
                'datos_asignatura'=>$datos_asignatura,
                'ruta'=>$request->ruta,
                'miga_pan'=>$miga_pan,
                'escala_min_max'=>$escala_min_max,
                'creado_por'=>$creado_por,
                'modificado_por'=>$modificado_por,
                'id_colegio'=>$this->colegio->id]);
        
    }
    


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $calificacion = Calificacion::findOrFail($id);
		return view('calificaciones.editar2',compact('calificacion'));
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
			default: 
				$Calificacion = Calificacion::findOrFail($id);
				$Calificacion->calificacion = $request->calificacion;
				$Calificacion->modificado_por = Auth::user()->email;
				$Calificacion->save();
				return redirect('/calificaciones');
				break;
		}
    } 


    public static function almacenar_calificacion(Request $request)
    {
        $id_calificacion = $request->id_calificacion;
        $calificacion_texto = $request->calificacion;
        $id_calificacion_aux = $request->id_calificacion_aux;

        // Recibe las calificaciones de una fila (un estudiante)

        // Se verifica si la calificación y las calificaciones auxiliares ya existen
        $calificacion = Calificacion::find($request->id_calificacion);

        if ( is_null($calificacion) ) 
        {
            // Crear nuevos registros
            if ( $request->calificacion != 0) 
            {
                $calificacion_creada = Calificacion::create( $request->all() );
                $calificacion_aux_creada = CalificacionAuxiliar::create( $request->all() );

                $id_calificacion = $calificacion_creada->id;
                $calificacion_texto = $calificacion_creada->calificacion;
                $id_calificacion_aux = $calificacion_aux_creada->id;
            }

        }else{
            // Actualizar registros existentes
            $calificacion_aux = CalificacionAuxiliar::find($request->id_calificacion_aux);

            // Si la calificación ENVIADA es cero se borran de la BD los registros almacenados
            if ( $request->calificacion == 0) 
            {
                $calificacion->delete();
                $calificacion_aux->delete();

                $id_calificacion = 'no';
                $calificacion_texto = 0;
                $id_calificacion_aux = 'no';
            }else{

                // Si no, se actualizan la calificación y las auxiliares
                $calificacion->fill( $request->all() );
                $calificacion->save();

                $calificacion_aux->fill( $request->all() );
                $calificacion_aux->save();
            }
        }

        return [ $id_calificacion, $calificacion_texto, $id_calificacion_aux ];

    }
	

    // LLenar select dependiente
    public function get_select_periodos( $periodo_lectivo_id )
    {
        $registros = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $opcion)
        {
            $opciones .= '<option value="'.$opcion->id.'">'.$opcion->descripcion.'</option>';
        }

        return $opciones;
    }

    // LLenar select dependiente
    public function get_select_asignaturas( $curso_id, $periodo_id = null)
    {
        if ( is_null($periodo_id) )
        {
            $periodo_lectivo = PeriodoLectivo::get_actual();
        }else{
            $periodo_lectivo = PeriodoLectivo::get_segun_periodo( $periodo_id );
        }

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo->id );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($asignaturas as $campo) {
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

    // LLenar select dependiente
    public function get_select_escala_valoracion( $periodo_id, $curso_id, $asignatura_id )
    {
        $periodo_lectivo = PeriodoLectivo::get_segun_periodo( $periodo_id );
        
        $escalas = EscalaValoracion::get_escalas_periodo_lectivo_abierto( $periodo_lectivo->id );

        $logros = Logro::get_logros_periodo_curso_asignatura( $periodo_id, $curso_id, $asignatura_id)->pluck('escala_valoracion_id')->toArray();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($escalas as $escala)
        {
            if( !in_array($escala->id, $logros) )
            {
                $opciones .= '<option value="'.$escala->id.'">'.$escala->nombre_escala.' ('.$escala->calificacion_minima.'-'.$escala->calificacion_maxima.')'.'</option>';
            }
        }

        return $opciones;
    }
}