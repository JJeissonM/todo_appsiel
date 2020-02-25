<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Matriculas\ReportesController;
use App\Http\Requests;

use DB;
use PDF;
use View;
use Input;
use Hash;

use App\User;

use Auth;

// Modelos
use App\Sistema\Modelo;
use App\Sistema\SecuenciaCodigo;
use App\Core\Colegio;
use App\Core\Tercero;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Matricula;
use App\Matriculas\Estudiante;
use App\Matriculas\Inscripcion;
use App\Matriculas\Grado;
use App\Matriculas\Curso;

use App\Calificaciones\Calificacion;
use App\Calificaciones\ObservacionesBoletin;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoMovimiento;
use App\Contabilidad\ContabMovimiento;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MatriculaController extends ModeloController
{
	
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if( Auth::check() ) 
        {
            $colegio = Colegio::get_colegio_user();
        }

        if( is_null($colegio) ) 
        {
            return "La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.";
        }

        $periodo_lectivo = PeriodoLectivo::get_actual();

        /**   ALGUNAS ESTADISTICAS            **/  
        $alumnos_por_curso = ReportesController::grafica_estudiantes_x_curso( $periodo_lectivo->id );
        $generos = ReportesController::grafica_estudiantes_x_genero( $periodo_lectivo->id );

        $miga_pan = [
                    ['url'=>'NO','etiqueta'=>'Matrículas']
                ];
        
        return view('matriculas.index',compact('generos','alumnos_por_curso','miga_pan','periodo_lectivo'));
    }
	

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $candidatos = Inscripcion::get_opciones_select_inscritos();

        $miga_pan = [
                        ['url'=>'matriculas?id='.Input::get('id'),'etiqueta'=>'Matrículas'],
                        ['url'=>'NO','etiqueta'=>'Nueva']
                    ];

        return view( 'matriculas.create', compact( 'miga_pan','candidatos' ) );
    }

    /**
        FORMULARIO PARA CREAR MATRICULA DE UN ESTUDIANTE, Con base en el id de la INSCRIPCION
     */
    public function crear_nuevo(Request $request)
    {
        // LLAMAR AL FORMULARIO PARA CREAR UNA NUEVA MATRICULA, SEGÚN EL DOC. ID DEL ESTUDIANTE
        $inscripcion = Inscripcion::find($request->id_inscripcion);

        $tercero = Tercero::find($inscripcion->core_tercero_id);

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

        //Algunas personalizaciones
        $cantidad_campos = count($lista_campos);
        for ($i=0; $i < $cantidad_campos; $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'codigo':
                    $lista_campos[$i]['value'] = SecuenciaCodigo::get_codigo( 'matriculas', (object)['grado_id'=>1] );
                    break;
                case 'fecha_matricula':
                    $lista_campos[$i]['value'] = date('Y-m-d');
                    break;
                case 'sga_grado_id':
                    $grados = Grado::all();
                    $opciones[''] = '';
                    foreach ($grados as $fila) {
                        $opciones[$fila->id."-".$fila->codigo] = $fila->descripcion; 
                    }
                    $lista_campos[$i]['opciones'] = $opciones;
                    break;
                case 'anio':

                    $secuencia = SecuenciaCodigo::where( [ ['modulo','matriculas'], ['estado','Activo'] ] )->get()->first();

                    $lista_campos[$i]['value'] = date('Y');

                    if ( !is_null($secuencia) ) {
                        $lista_campos[$i]['value'] = '20'.$secuencia->anio;
                    }

                    
                    break;
                case 'acudiente':

                    $lista_campos[$i]['value'] = $inscripcion->acudiente;
                    
                    break;
                
                default:
                    # code...
                    break;
            }
        }

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];


        // Consultar matriculas del estudiante
        $estudiante = Estudiante::get_estudiante_x_tercero_id( $tercero->id );

        if ( !is_null($estudiante) ) {
            $matriculas = Matricula::get_matriculas_un_estudiante( $estudiante->id );
            $estudiante_existe = true;
        }else{
            $matriculas = array();
            $estudiante_existe = false;
        }

        $miga_pan = [
                ['url'=>'matriculas?id='.Input::get('id'),'etiqueta'=>'Matrículas'],
                ['url'=>'matriculas/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Nueva'],
                ['url'=>'NO','etiqueta'=>'Crear nueva']
            ]; 

        $colegio = Colegio::get_colegio_user();
        $id_colegio = $colegio->id;

        return view('matriculas.crear_nuevo', compact('matriculas','miga_pan','form_create','tercero','id_colegio','inscripcion','estudiante_existe') );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [ 
            'fecha_matricula' => 'required',
            'curso_id' => 'required',
            'acudiente' => 'required|max:100', 
            'cedula_acudiente' => 'required'],
                            ['required'=>'Los campos de fecha de matrícula, curso, cédula y nombre de acudiente son obligatorios.']);

        // YA EL TERCERO FUE CREADO EN LA INSCRIPCION

        // Si el estudiante no existe, Se crea usuario y Estudiante
        if ( $request->estudiante_existe == false ) 
        {

            $user = User::crear_y_asignar_role( $request, 4); // 4 = Role Estudiante

            $datos = array_merge($request->all(), 
                            ['user_id'=> $user->id ] );

            $estudiante = Estudiante::create( $datos );

        }else{
            //echo "true";
            // Si ya existe, obtengo el registro según el tercero asociado
            $estudiante = Estudiante::get_estudiante_x_tercero_id( $request->core_tercero_id );
            //print_r($estudiante);
        }

		$requisitos = $request->requisito1."-".$request->requisito2."-".$request->requisito3."-".$request->requisito4
            ."-".$request->requisito5."-".$request->requisito6;
        
        // Generar el código de la matrícula
        $vec_grado = explode("-", $request->sga_grado_id);
        $codigo = SecuenciaCodigo::get_codigo( 'matriculas', (object)['grado_id'=>$vec_grado[0]] );

        
        $datos2 = array_merge($request->all(),
                            ['requisitos' => $requisitos,
                            'id_estudiante' => $estudiante->id,
                            'codigo' => $codigo ] );
		
		
		// Obtener el id de la ultima matricula activa de ese estudiante
		$matricula_activa = Matricula::get_matricula_activa_un_estudiante( $estudiante->id );
		
        if( !is_null($matricula_activa) )
        {	
			// Inactivar la matricula anterior antes de crear la nueva
			Matricula::inactivar( $matricula_activa->id );
		}		
		
        $matricula = Matricula::create($datos2);

        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo( 'matriculas' );

        return redirect('matriculas/show/'.$matricula->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Matrícula creada correctamente. Código: '.$matricula->codigo);
    }

    // Generar vista para SHOW  o IMPRIMIR
    public static function vista_preliminar( $id )
    {
        $matricula = Matricula::get_registro_impresion( $id );

        $estudiante = Estudiante::get_datos_basicos( $matricula->id_estudiante );

        // Crear vista
        $view =  View::make( 'matriculas.pdf_matricula', compact('matricula','estudiante') )->render();

        return $view;
    }

    public function show($id)
    {
        $reg_anterior = Matricula::where('id', '<', $id)->max('id');
        $reg_siguiente = Matricula::where('id', '>', $id)->min('id');

        $view_pdf = MatriculaController::vista_preliminar( $id );

        $miga_pan = [
                ['url'=>'matriculas?id='.Input::get('id'),'etiqueta'=>'Matrículas'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => 'Matrículas' ],
                ['url'=>'NO','etiqueta' => 'Consulta' ]
            ];

        return view( 'matriculas.show_matricula',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') );
    }

    public function imprimir($id)
    {
        $view = MatriculaController::vista_preliminar( $id );
        $orientacion='portrait';
        $tam_hoja='Letter';

        // Crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
        return $pdf->stream('matricula.pdf');//stream();
        

        /*echo $view;*/
    }


    /**
     * Show the form for editing the specified resource, using the id of matricula
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        if ( $registro->periodo_lectivo_id != 0 )
        {
            if( PeriodoLectivo::find($registro->periodo_lectivo_id)->cerrado )
            {
                return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Matrícula no puede ser MODIFICADA. El Periodo Lectivo está cerrado.');
            }
        }
            

        // Se vefifica si la matrícula tiene calificaciones, entonces no se podrá modificar el grado ni el curso. 
        $cant_calificaciones = 0;

        // Verificar si el estudiante ya tiene calificaciones con esta matrícula, entonces no se podrá cambiar el Grado
        $cant_calificaciones = Calificacion::get_cantidad_x_matricula( $registro->id_colegio, $registro->codigo);
        
        // Si no tiene calificaciones, tambien se validan las observaciones
        if( $cant_calificaciones == 0)
        {
            $cant_calificaciones = ObservacionesBoletin::get_cantidad_x_matricula( $registro->id_colegio, $registro->codigo);
        }

        $lista_campos = ModeloController::get_campos_modelo( $modelo, $registro, 'edit');
        
        //Algunas personalizaciones
        $cantidad_campos = count($lista_campos);
        
        $curso = Curso::find( $registro->curso_id );
        $grado = Grado::find( $curso->sga_grado_id );
        
        for ($i=0; $i < $cantidad_campos; $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'sga_grado_id':
                    $grados = Grado::all();
                    $opciones[''] = '';
                    foreach ($grados as $fila) {
                        $opciones[$fila->id."-".$fila->codigo] = $fila->descripcion; 
                    }
                    $lista_campos[$i]['opciones'] = $opciones;

                    
                    $lista_campos[$i]['value'] = $grado->id."-".$grado->codigo;

                    if ( $cant_calificaciones != 0) {
                        $lista_campos[$i]['atributos'] = ['disabled' => 'disabled']; 
                    }
                    break;

                case 'curso_id':

                    $cursos = Curso::get_cursos_x_grado( $grado->id );
                    unset($opciones);
                    $opciones[''] = '';
                    foreach ($cursos as $fila) {
                        $opciones[$fila->id] = $fila->descripcion; 
                    }
                    $lista_campos[$i]['opciones'] = $opciones;

                    $lista_campos[$i]['value'] = $curso->id;

                    /*if ( $cant_calificaciones != 0) {
                        $lista_campos[$i]['atributos'] = ['disabled' => 'disabled']; 
                    }*/

                    break;
                
                default:
                    # code...
                    break;
            }
        }

        // form_create para generar un formulario html 
        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo,$registro->codigo);
		
        

        $estudiante = Estudiante::find($registro->id_estudiante);
        
        //print_r($estudiante);
        $tercero = Tercero::find($estudiante->core_tercero_id);

		return view('matriculas.edit',compact('registro','cant_calificaciones','miga_pan','tercero', 'form_create'));
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
        $this->validate($request, [ 
                            'fecha_matricula' => 'required',
                            'curso_id' => 'required',
                            'acudiente' => 'required|max:100', 
                            'cedula_acudiente' => 'required'],
                               ['required'=>'Los campos de fecha de matrícula, curso, cédula y nombre de acudiente son obligatorios.']);

		$requisitos = $request->requisito1."-".$request->requisito2."-".$request->requisito3."-".$request->requisito4
			."-".$request->requisito5."-".$request->requisito6;

		$datos = array_merge($request->all(), ['requisitos'=>$requisitos ]);
		
		$registro = Matricula::find($id);

		$registro->fill( $datos )->save();
		
        return redirect('matriculas/show/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Matrícula MODIFICADA correctamente. Código: '.$registro->codigo);

        //return redirect('/matriculas');
		//return redirect($request->return)->with('flash_message','Matrícula MODIFICADA correctamente.');
				
    }

    /**
     * Eliminar matricula.
     *
     * 
     */
    public function eliminar($id)
    {
        $registro = Matricula::find( $id );

        $estudiante = Estudiante::find( $registro->id_estudiante );
        $user = User::find( $estudiante->user_id );

        $todas_las_matriculas = Matricula::where( 'id_estudiante', $registro->id_estudiante )->get();

        // Verificación 1: Libreta de pagos
        $cantidad = TesoLibretasPago::where('matricula_id', $id)->count();
        if($cantidad != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Matrícula NO puede ser eliminada. Tiene libreta de pago asociada.');
        }

        // Verificadion 2: Calificaciones y observaciones
        $cant_calificaciones = 0;
        $cant_calificaciones = Calificacion::where([
                                                    'id_colegio'=>$registro->id_colegio,
                                                    'codigo_matricula'=>$registro->codigo])
                                            ->count();
        if($cant_calificaciones != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Matrícula NO puede ser eliminada. El estudiante tiene CALIFICACIONES resgistradas.');
        }

        $cant_calificaciones = DB::table('sga_observaciones_boletines')
                                ->where( 
                                            [ 
                                                'id_colegio' => $registro->id_colegio,
                                                'codigo_matricula' => $registro->codigo
                                            ]
                                        )
                                ->count();
        if($cant_calificaciones != 0){
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Matrícula NO puede ser eliminada. El estudiante tiene OBSERVACIONES de boletín resgistradas.');
        }
        

        // Si hay SOLO una (1) matrícula, se elimina al usuario y al estudiante
        if ( count( $todas_las_matriculas->toArray() ) == 1 )
        {
            //Borrar User
            if( !is_null($user) )
            {
                $user->roles()->sync( [ ] ); // borrar todos los roles y asignar los del array (en este caso vacío)
                $user->delete();
            }
                
            //Borrar Estudiante
            $estudiante->delete();
        }
            

        //Borrar Matrícula
        $registro->delete();


        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Matrícula ELIMINADA correctamente. Código: '.$registro->codigo);
    }

}
