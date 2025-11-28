<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Matriculas\ReportesController;

use App\User;

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
use App\Core\TipoDocumentoId;
use App\Matriculas\Responsableestudiante;
use App\Matriculas\Services\FacturaEstudiantesService;
use App\Matriculas\Tiporesponsable;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class MatriculaController extends ModeloController
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $colegio = null;

        if (Auth::check()) {
            $colegio = Colegio::get_colegio_user();
        }

        if ($colegio == null) {
            return redirect('inicio')->with('mensaje_error', 'La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.');
        }

        $periodo_lectivo = PeriodoLectivo::get_actual();

        if ($periodo_lectivo == null) {
            return redirect('web/create?id=1&id_modelo=179&id_transaccion=')->with('mensaje_error', 'Debe crear un Año Lectivo para empezar.');
        }        

        /**   ALGUNAS ESTADISTICAS            **/
        $alumnos_por_curso = ReportesController::grafica_estudiantes_x_curso($periodo_lectivo->id);
        $generos = ReportesController::grafica_estudiantes_x_genero($periodo_lectivo->id);

        $nuevos_matriculados = ReportesController::nuevos_matriculados( $periodo_lectivo->id );

        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Matrículas']
        ];

        return view('matriculas.index', compact('generos', 'alumnos_por_curso', 'miga_pan', 'periodo_lectivo','nuevos_matriculados'));
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
            ['url' => 'matriculas?id=' . Input::get('id'), 'etiqueta' => 'Matrículas'],
            ['url' => 'NO', 'etiqueta' => 'Nueva']
        ];

        return view('matriculas.create', compact('miga_pan', 'candidatos'));
    }

    /**
     * FORMULARIO PARA CREAR MATRICULA DE UN ESTUDIANTE, Con base en el id de la INSCRIPCION
     */
    public function crear_nuevo(Request $request)
    {
        $id_modelo = (int)Input::get('id_modelo');

        if ( $id_modelo == 66) { // Inscripciones
            // LLAMAR AL FORMULARIO PARA CREAR UNA NUEVA MATRICULA, SEGÚN EL DOC. ID DEL ESTUDIANTE
            $inscripcion = Inscripcion::find( $request->id_inscripcion );

            $tercero = Tercero::find( $inscripcion->core_tercero_id );

            $id_modelo = 19; // Matriculas
        }else{
            // id_inscripcion en realidad es el ID de la matricula (sino que se llama desde otra vista)
            $matricula_activa = Matricula::find( $request->id_inscripcion );

            $tercero = $matricula_activa->estudiante->tercero;
        }        

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find( $id_modelo );

        $lista_campos = ModeloController::get_campos_modelo($modelo, '', 'create');
        //eliminamos los campos de acudiente por defecto en el modelo
        if (count($lista_campos) > 0) {
            foreach ($lista_campos as $key => $lc) {
                if ($lc['name'] == 'acudiente' || $lc['name'] == 'cedula_acudiente' || $lc['name'] == 'telefono_acudiente' || $lc['name'] == 'email_acudiente') {
                    unset($lista_campos[$key]);
                }
            }
        }

        //Algunas personalizaciones
        $cantidad_campos = count($lista_campos);
        for ($i = 0; $i < $cantidad_campos; $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'codigo':
                    $lista_campos[$i]['value'] = SecuenciaCodigo::get_codigo('matriculas', (object)['grado_id' => 1]);
                    break;
                case 'fecha_matricula':
                    $lista_campos[$i]['value'] = date('Y-m-d');
                    break;
                case 'sga_grado_id':
                    $grados = Grado::all();
                    $opciones[''] = '';
                    foreach ($grados as $fila) {
                        $opciones[$fila->id . "-" . $fila->codigo] = $fila->descripcion;
                    }
                    $lista_campos[$i]['opciones'] = $opciones;
                    break;
                case 'anio':

                    $secuencia = SecuenciaCodigo::where([['modulo', 'matriculas'], ['estado', 'Activo']])->get()->first();

                    $lista_campos[$i]['value'] = date('Y');

                    if (!is_null($secuencia)) {
                        $lista_campos[$i]['value'] = '20' . $secuencia->anio;
                    }

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
        $estudiante = Estudiante::get_estudiante_x_tercero_id($tercero->id);

        $matriculas = [];
        $estudiante_existe = 0;
        if ( $estudiante != null ) {
            $matriculas = Matricula::get_matriculas_un_estudiante($estudiante->id);
            $estudiante_existe = 1;
        }

        $miga_pan = [
            ['url' => 'matriculas?id=' . Input::get('id'), 'etiqueta' => 'Matrículas'],
            ['url' => 'web?id=1&id_modelo=' . Input::get('id_modelo'), 'etiqueta' => $modelo->descripcion],
            ['url' => 'NO', 'etiqueta' => 'Crear nueva']
        ];

        $colegio = Colegio::get_colegio_user();
        $id_colegio = $colegio->id;

        //obtenemos el listado de tipos de responsables (PAPA, MAMA, RESPONSABLE-FINANCIERO, ACUDIENTE, ETC)
        $tipos = Tiporesponsable::all();
        $tiposdoc = TipoDocumentoId::all();
        return view('matriculas.crear_nuevo', compact('matriculas', 'miga_pan', 'form_create', 'estudiante', 'id_colegio', 'estudiante_existe', 'tipos', 'tiposdoc'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'fecha_matricula' => 'required',
                'curso_id' => 'required'
            ],
            ['required' => 'Los campos de fecha de matrícula y curso son obligatorios.']
        );

        // YA EL TERCERO FUE CREADO EN LA INSCRIPCION

        // Si el estudiante no existe, Se crea usuario y Estudiante
        if ( $request->estudiante_existe == 0 )
        {
            $estudiante = Estudiante::create( $request->all() );
            
            $datos_responsables = json_decode( $request->lineas_registros );
            $cantidad_registros = count($datos_responsables) - 1;// no se tiene en cuenta el ultimo elemento del array
            for ($i=0; $i < $cantidad_registros; $i++) 
            {
                $datos = [];
                $datos['tercero_id'] = (int)$datos_responsables[$i]->tercero_id;
                $datos['tiporesponsable_id'] = (int)$datos_responsables[$i]->tiporesponsable_id;
                $datos['estudiante_id'] = $estudiante->id;
                $this->setResponsable( $datos );
            }

        } else {
            // Si ya existe, obtengo el registro según el tercero asociado
            $estudiante = Estudiante::get_estudiante_x_tercero_id( $request->core_tercero_id );

            $estudiante->grupo_sanguineo = $request->grupo_sanguineo;
            $estudiante->alergias = $request->alergias;
            $estudiante->medicamentos = $request->medicamentos;
            $estudiante->eps = $request->eps;
            $estudiante->save();
        }

        $this->crear_y_asignar_usuario($estudiante);

        $requisitos = $request->requisito1 . "-" . $request->requisito2 . "-" . $request->requisito3 . "-" . $request->requisito4
            . "-" . $request->requisito5 . "-" . $request->requisito6;

        // Generar el código de la matrícula
        $vec_grado = explode("-", $request->sga_grado_id);
        $codigo = SecuenciaCodigo::get_codigo('matriculas', (object)['grado_id' => $vec_grado[0]]);

        $datos2 = array_merge(
                                $request->all(),
                                [
                                    'requisitos' => $requisitos,
                                    'id_estudiante' => $estudiante->id,
                                    'codigo' => $codigo,
                                    'estado' => 'Activo'
                                ]
                            );


        // Obtener el id de la ultima matricula activa de ese estudiante
        $matricula_activa = Matricula::get_matricula_activa_un_estudiante($estudiante->id);

        if (!is_null($matricula_activa)) {
            // Inactivar la matricula anterior antes de crear la nueva
            Matricula::inactivar($matricula_activa->id);
        }

        $matricula = Matricula::create($datos2);

        $this->actualizar_estado_ultima_inscripcion( $estudiante->core_tercero_id );

        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo('matriculas');

        if ( $request->fecha_inicio != null && $request->numero_periodos != null && $request->valor_matricula != null && $request->valor_pension_mensual != null )    
        {
            $data = $request->all();
            $data['matricula_id'] = $matricula->id;
            $data['valor_pension_anual'] = $request->valor_pension_mensual * $request->numero_periodos;
            $data['id_estudiante'] = $matricula->id_estudiante;
            $data['estado'] = 'Activo';
            $data['creado_por'] = Auth::user()->email;
            (new FacturaEstudiantesService)->store_plan_pagos($data);
        }

        return redirect('matriculas/show/' . $matricula->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Matrícula creada correctamente. Código: ' . $matricula->codigo);
    }

    public function crear_y_asignar_usuario($estudiante)
    {
        $user = User::where('email',$estudiante->tercero->email)->get()->first();
        if ($user != null) {
            return false;
        }

        $name = $estudiante->tercero->nombre1 . " " . $estudiante->tercero->otros_nombres . " " . $estudiante->tercero->apellido1 . " " . $estudiante->tercero->apellido2;

        if ( $name == '' )
        {
            $name = $estudiante->tercero->descripcion;
        }
        
        $password = str_random(8);
        $user = User::crear_y_asignar_role($name, $estudiante->tercero->email, 4, $password); // 4 = Role Estudiante

            $user_id = 0;
        if ( $user != null )
        {
            $user_id = $user->id;
        }

        $estudiante->user_id = $user_id;

        return true;
    }

    public function actualizar_estado_ultima_inscripcion( $core_tercero_id )
    {
        $inscripcion = Inscripcion::where( 'core_tercero_id', $core_tercero_id )
                                    ->get()
                                    ->last();

        if ( $inscripcion == null) {
            return false;
        }

        if ( $inscripcion->estado == 'Activo' )
        {
            $inscripcion->estado = 'Pendiente';
        }else{
            $inscripcion->estado = 'Activo';
        }
        
        $inscripcion->save();
    } 

    //crea un responsable para los papás
    public function setResponsable($data)
    {
        $r = new Responsableestudiante();
        $r->fill( $data );
        $r->save();
        return $r;
    }

    // Generar vista para SHOW  o IMPRIMIR
    public static function vista_preliminar($id)
    {
        $matricula = Matricula::get_registro_impresion($id);

        $estudiante = Estudiante::get_datos_basicos($matricula->id_estudiante);
        
        $formato = 'formatos.matriculas.estandar';
        if ( !in_array(config('matriculas.formato_default_fichas_incripcion_y_matricula'), [null,'']) ) {
            $formato = 'formatos.matriculas.' . config('matriculas.formato_default_fichas_incripcion_y_matricula');
        }

        // Crear vista
        $view =  View::make('matriculas.' . $formato, compact('matricula', 'estudiante'))->render();

        return $view;
    }

    public function show($id)
    {
        $reg_anterior = Matricula::where('id', '<', $id)->max('id');
        $reg_siguiente = Matricula::where('id', '>', $id)->min('id');

        $view_pdf = MatriculaController::vista_preliminar($id);
        $app = Input::get('id');
        $modelo = Input::get('id_modelo');
        $matricula = Matricula::get_registro_impresion($id);
        $miga_pan = [
            ['url' => 'matriculas?id=' . $app, 'etiqueta' => 'Matrículas'],
            ['url' => 'web?id=' . $app . '&id_modelo=' . $modelo, 'etiqueta' => 'Matrículas'],
            ['url' => 'NO', 'etiqueta' => 'Consulta']
        ];
        
        $matriculas = Matricula::get_matriculas_un_estudiante($matricula->estudiante->id);

        $libreta_id = null;
        $libreta = TesoLibretasPago::where([
                                        ['matricula_id','=', $id]
                                    ])
                                  ->get()
                                  ->first();

        if ($libreta != null) {
            $libreta_id = $libreta->id;
        }

        return view('matriculas.show_matricula', compact('modelo', 'app', 'matricula', 'matriculas', 'reg_anterior', 'reg_siguiente', 'miga_pan', 'view_pdf', 'id', 'libreta_id'));
    }

    public function imprimir($id)
    {
        $view = MatriculaController::vista_preliminar($id);
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';

        // Crear PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
        return $pdf->stream('matricula.pdf'); //stream();


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
        $id_modelo = (int)Input::get('id_modelo');
        if ( $id_modelo == 66) { // Inscripciones
            $id_modelo = 19; // Matriculas
        }

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find( $id_modelo );

        // Se obtiene el registro a modificar del modelo
        $matricula = app($modelo->name_space)->find($id);

        if ($matricula->periodo_lectivo_id != 0) {
            if (PeriodoLectivo::find($matricula->periodo_lectivo_id)->cerrado) {
                return redirect('web?id=' . Input::get('id') . '&id_modelo=' . $id_modelo)->with('mensaje_error', 'Matrícula no puede ser MODIFICADA. El Periodo Lectivo está cerrado.');
            }
        }


        // Se vefifica si la matrícula tiene calificaciones, entonces no se podrá modificar el grado ni el curso. 
        $cant_calificaciones = 0;

        // Verificar si el estudiante ya tiene calificaciones con esta matrícula, entonces no se podrá cambiar el Grado
        $cant_calificaciones = Calificacion::get_cantidad_x_matricula($matricula->id_colegio, $matricula->codigo);

        // Si no tiene calificaciones, tambien se validan las observaciones
        if ($cant_calificaciones == 0) {
            $cant_calificaciones = ObservacionesBoletin::get_cantidad_x_matricula($matricula->id_colegio, $matricula->codigo);
        }

        $lista_campos = ModeloController::get_campos_modelo($modelo, $matricula, 'edit');

        //eliminamos los campos de acudiente por defecto en el modelo
        if (count($lista_campos) > 0) {
            foreach ($lista_campos as $key => $lc) {
                if ($lc['name'] == 'acudiente' || $lc['name'] == 'cedula_acudiente' || $lc['name'] == 'telefono_acudiente' || $lc['name'] == 'email_acudiente') {
                    unset($lista_campos[$key]);
                }
            }
        }

        //Algunas personalizaciones
        $cantidad_campos = count($lista_campos);

        $curso = Curso::find($matricula->curso_id);
        $grado = Grado::find($curso->sga_grado_id);

        for ($i = 0; $i < $cantidad_campos; $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'sga_grado_id':
                    $grados = Grado::all();
                    $opciones[''] = '';
                    foreach ($grados as $fila) {
                        $opciones[$fila->id . "-" . $fila->codigo] = $fila->descripcion;
                    }
                    $lista_campos[$i]['opciones'] = $opciones;


                    $lista_campos[$i]['value'] = $grado->id . "-" . $grado->codigo;

                    if ($cant_calificaciones != 0) {
                        $lista_campos[$i]['atributos'] = ['disabled' => 'disabled'];
                    }
                    break;

                case 'curso_id':

                    $cursos = Curso::get_cursos_x_grado($grado->id);
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

        $miga_pan = $this->get_miga_pan($modelo, $matricula->codigo);

        $estudiante = Estudiante::find($matricula->id_estudiante);

        return view('matriculas.edit', compact('matricula', 'cant_calificaciones', 'miga_pan', 'estudiante', 'form_create'));
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
        $this->validate(
            $request,
            [
                'fecha_matricula' => 'required',
                'curso_id' => 'required'
            ],
            ['required' => 'Los campos de fecha de matrícula y curso son obligatorios.']
        );

        $requisitos = $request->requisito1 . "-" . $request->requisito2 . "-" . $request->requisito3 . "-" . $request->requisito4
            . "-" . $request->requisito5 . "-" . $request->requisito6;

        $datos = array_merge($request->all(), ['requisitos' => $requisitos]);

        $registro = Matricula::find($id);

        $registro->fill($datos)->save();

        return redirect('matriculas/show/' . $registro->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Matrícula MODIFICADA correctamente. Código: ' . $registro->codigo);
    }

    /**
     * Eliminar matricula.
     *
     * 
     */
    public function eliminar($id)
    {
        $registro = Matricula::find($id);

        $todas_las_matriculas = Matricula::where('id_estudiante', $registro->id_estudiante)->get();

        $tabla_existe = DB::select(DB::raw("SHOW TABLES LIKE 'teso_libretas_pagos'"));
        if ( !empty($tabla_existe) )
        {
            // Verificación 1: Libreta de pagos
            $cantidad = TesoLibretasPago::where('matricula_id', $id)->count();
            if ($cantidad != 0)
            {
                return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Matrícula NO puede ser eliminada. Tiene libreta de pago asociada.');
            }
        }

        // Verificadion 2: Calificaciones y observaciones
        $cant_calificaciones = 0;
        $cant_calificaciones = Calificacion::where([
                                                    'id_colegio' => $registro->id_colegio,
                                                    'codigo_matricula' => $registro->codigo
                                                ])
                                            ->count();
        if ($cant_calificaciones != 0)
        {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Matrícula NO puede ser eliminada. El estudiante tiene CALIFICACIONES resgistradas.');
        }

        $cant_calificaciones = DB::table('sga_observaciones_boletines')
                                    ->where(
                                        [
                                            'id_colegio' => $registro->id_colegio,
                                            'codigo_matricula' => $registro->codigo
                                        ]
                                    )
                                    ->count();

        if ($cant_calificaciones != 0)
        {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Matrícula NO puede ser eliminada. El estudiante tiene OBSERVACIONES de boletín resgistradas.');
        }

        $this->actualizar_estado_ultima_inscripcion( $registro->estudiante->core_tercero_id );

        // Si hay SOLO una (1) matrícula, se elimina al usuario
        if (count($todas_las_matriculas->toArray()) == 1)
        {

            $estudiante = Estudiante::find( $registro->id_estudiante );

            if ( !is_null($estudiante) )
            {
                $user = User::find($estudiante->user_id);

                //Borrar User
                if (!is_null($user))
                {
                    $user->roles()->sync([]); // borrar todos los roles y asignar los del array (en este caso vacío)
                    $user->delete();
                }
            }
        }       
        
        //Borrar Matrícula
        $registro->delete();

        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Matrícula ELIMINADA correctamente. Código: ' . $registro->codigo);
    }
}