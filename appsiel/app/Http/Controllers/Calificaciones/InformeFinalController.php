<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Database\Eloquent\Model;

use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Logro;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Periodo;
use App\Calificaciones\Boletin;
use App\Calificaciones\Calificacion;
use App\Calificaciones\ObservacionesBoletin;


use App\Core\Colegio;
use App\Sistema\Aplicacion;

use Input;
use DB;
use PDF;
use View;
use Storage;
use Auth;

//use Request;
//use Boletin;

class InformeFinalController extends BoletinController
{
	protected $colegio;
    public $estado_matricula = null;


	public function __construct()
    {
		$this->middleware('auth');

        if ( Auth::check() )
        {
            $this->colegio = Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();
        }
    }
	
    /**
     * FORMULARIO PARA GENEERAR PDF.
     *
     */
    public function index()
    {

        $opciones1 = Curso::where('id_colegio','=',$this->colegio->id)->where('estado','=','Activo')->OrderBy('nivel_grado')->get();
        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id]=$opcion->descripcion;
        }
        $cursos = $vec1;


        $opciones = Periodo::where('id_colegio','=',$this->colegio->id)->get();

        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->descripcion;
        }

        $periodos = $vec;

        $miga_pan = [
                        ['url'=>'calificaciones?id='.Input::get('id'),'etiqueta'=>'Calificaciones'],
                        ['url'=>'NO','etiqueta'=>'Imprimir informe final']
                    ];


        return view('calificaciones.boletines.form_imprimir_informe_final',compact('cursos','periodos','miga_pan'));				
    }	


    /**
     * Se va a usar este método para GENERAR EL PDF del informe final
     *
     */
    public function store(Request $request)
    {
        $periodos_promediar = $request->periodos_promediar;
        $periodo = Periodo::find( $request->id_periodo[0] );
        $anio = explode("-",$periodo->fecha_desde)[0];

        $convetir_logros_mayusculas = $request->convetir_logros_mayusculas;

        $mostrar_areas = $request->mostrar_areas;
        $mostrar_nombre_docentes = $request->mostrar_nombre_docentes;
        $mostrar_escala_valoracion = $request->mostrar_escala_valoracion;

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

        
        // Listado de estudiantes con matriculas activas en el curso y año indicados
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, $this->estado_matricula );
        
        $curso = Curso::find($request->curso_id);

        if( count($estudiantes) > 0 ){
            
            /*
                ** Para imprimir se llaman solo a las asignaturas que han sido calificadas
                ** No se pueden llamar las asignaturas del curso porque estas pudieron haber cambiado
                ** Es decir a un curso se le pudieron agregar asignaturas nuevas y/o eliminar viejas 
            */
            
            // Seleccionar asignaturas del curso
            $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($request->curso_id, null, $periodo->periodo_lectivo_id );
        
            // Se prepara el PDF
            $orientacion='portrait';
            $tam_letra=$request->tam_letra;

            $banner = View::make('banner_colegio')->render();

            $view =  View::make('calificaciones.boletines.'.$request->formato, compact('estudiantes','asignaturas',['colegio'=>$this->colegio],'curso','periodo','anio','tam_letra','banner','convetir_logros_mayusculas','mostrar_areas','mostrar_nombre_docentes','mostrar_escala_valoracion','firmas','periodos_promediar'))->render();
            
            $pdf = \App::make('dompdf.wrapper');
            $pdf->loadHTML(($view))->setPaper($request->tam_hoja,$orientacion);

            return $pdf->download('boletines_del_curso_'.$curso->descripcion.'.pdf');//stream();
            

            //echo $view;/**/
        }else{

            // PENDIENTE!!!!! usar mejor un redirect con mensaje
            
            echo "No hay regitros de estudiantes matriculados en el curso ".$curso->descripcion;
        }
    }

    // Muestra formulario para el cálculo del puesto (g = get)
	public function calcular_puesto_g()
    {
		$user = Auth::user();
        $colegio = Colegio::where('empresa_id',$user->empresa_id)->get()[0];

		// SELECT DE CURSOS
        $opciones1 = DB::table('sga_cursos')
                        ->where(['id_colegio'=>$colegio->id,'estado'=>'Activo'])
                        ->OrderBy('nivel_grado')->get();

        $vec1['']='';
        
        if ( $user->hasRole('SuperAdmin') || $user->hasRole('Admin Colegio') || $user->hasRole('Colegio - Vicerrector')  ) 
        {
            foreach ($opciones1 as $opcion){
                $vec1[$opcion->id]=$opcion->descripcion;
            }            
        }else{
            foreach ($opciones1 as $opcion)
            {
                $esta = DB::table('sga_curso_tiene_director_grupo')->where('curso_id',$opcion->id)->where('user_id',$user->id)->get();
                if ( !empty($esta) ) 
                {
                    $vec1[$opcion->id]=$opcion->descripcion;               
                }
            }
        }
        
        $cursos = $vec1;


		$opciones = Periodo::where('id_colegio','=',$colegio->id)->get();
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->descripcion;
        }

        $periodos = $vec;

        $app = Aplicacion::find( Input::get('id') );

		$miga_pan = [
                        ['url' => $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Calcular puesto estudiantes']
                    ];


        return view('calificaciones.boletines.calcular_puesto_informe_final',compact('cursos','periodos','miga_pan'));
	}
	
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
        $cadena = '';
        $es_el_primero = true;
        foreach ($request->periodos_promediar as $key => $value)
        {
            if ($es_el_primero)
            {
                $cadena .= $value;
                $es_el_primero = false;
            }else{
                $cadena .= ','.$value;
            }
            
        }
		$query_1 = "SELECT AVG(calificacion) as promedioCalificaciones,id_estudiante FROM calificaciones 
				WHERE id_colegio=".$colegio->id." AND anio=".$anio." AND id_periodo IN (".$cadena.") AND curso_id=".$request->curso_id." 
				GROUP BY id_estudiante 
				ORDER BY promedioCalificaciones DESC";                
		
		$promedios = DB::select($query_1);

		$nom_curso = Curso::where('id','=',$request->curso_id)->value('descripcion');

		$total_estudiantes = count( Matricula::estudiantes_matriculados( $request->curso_id, $periodo->periodo_lectivo_id, $this->estado_matricula  ) );
		
		// Si hay calificaciones para los datos enviados
		if( !empty($promedios) )
        {
		
			/**
			 * Crear un vector con los puestos unicos que existen
			 */
			$query_2 = "SELECT DISTINCT promedioCalificaciones FROM (".$query_1.") Puestos";
			$puestos = DB::select($query_2);
			$i=1;
			foreach($puestos as $puesto){
				$vec_puestos [$i] = $puesto->promedioCalificaciones;
				$i++;
			}
			
			// Buscar el puesto al que pertenece cada estudiante según su promedio de calificaciones
			foreach($promedios as $fila){
				$puesto_est = array_search($fila->promedioCalificaciones,$vec_puestos);
				
				// Verificar si ya hay observaciones ingresadas en la tabla 
				$cant_observa = DB::table('observaciones_boletines')
								->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
										'id_periodo'=>$request->id_periodo,'curso_id'=>$request->curso_id,
										'id_estudiante'=>$fila->id_estudiante])
								->count();

				$el_puesto = $puesto_est.' / '.$total_estudiantes;
				
				if($cant_observa>0){
					// Si ya hay observaciones para ese estudiante, 
					// Se deben actualizar los registros en la tabla de observaciones_boletines
					DB::table('observaciones_boletines')
						->where(['id_colegio'=>$colegio->id,'anio'=>$anio,
								'id_periodo'=>$request->id_periodo,'curso_id'=>$request->curso_id,
								'id_estudiante'=>$fila->id_estudiante])
						->update(['puesto' => $el_puesto]);
				}else{
					// INSERTAR registros en la tabla de observaciones_boletines
					DB::table('observaciones_boletines')
						->insert(['id_colegio'=>$colegio->id,'anio'=>$anio,
								'id_periodo'=>$request->id_periodo,'curso_id'=>$request->curso_id,
								'id_estudiante'=>$fila->id_estudiante,
								'puesto' => $el_puesto]);
					
					// Guardar en tabla auxiliar para indicar que ya se ingresaron observaciones o puestos
					// del curso en de ese año-periodo. 
					// Esta tabla es para saber si se están creando los registros por primera vez 
					// de observaciones o puesto; para determinar si se van a INSERTAR o ACTUALIZAR
					DB::insert('insert into observaciones_ingresadas (id_colegio,anio,id_periodo,curso_id) values(?,?,?,?)',
								[$colegio->id,$anio,$request->id_periodo,$request->curso_id]);
				}			
			}
			
			return redirect('/calificaciones/boletines/calcular_puesto_informe_final?id='.$request->id_app)->with('flash_message','Se calcularon los puestos de cada estudiante para los boletines del curso <b>'.$nom_curso.'</b>');	
		}else{
			// Si no hay calificaciones ingresadas para los datos enviados
			return redirect('/calificaciones/boletines/calcular_puesto_informe_final?id='.$request->id_app)->with('mensaje_error','Aún NO hay calificaciones ingresadas para los estudiante del curso <b>'.$nom_curso.'</b> en el periodo seleccionado.');	
		}
		
    }
	
	/**
     * Crear un vector con los estudiantes con matricula activa y que no tienen boletín para el año, periodo y curso dado.
     *
     */
	public function select_estudiantes($anio,$id_periodo,$curso_id)
    {	
		
		// Listado de estudiantes con matriculas activas en el curso indicado
		$estudiantes = DB::table('matriculas')
			->join('sga_estudiantes', 'matriculas.id_estudiante', '=', 'sga_estudiantes.id')
			->select('matriculas.codigo','matriculas.id_estudiante', 'sga_estudiantes.id', 'sga_estudiantes.nombres', 
					'sga_estudiantes.apellido1', 'sga_estudiantes.apellido2')
			->where([['matriculas.curso_id', $curso_id],['matriculas.estado','Activo']])
			->get();

		// Los estudiantes que no tiene boletin para el curso, año y periodo dado
		$i=0;$ind=0;
		foreach ($estudiantes as $campo) {
			// Se consultan los estudiantes que ya tienen boletines para ese año, periodo y curso
			$est = DB::table('boletines')->where([
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