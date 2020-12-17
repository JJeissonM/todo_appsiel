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
use App\Core\TipoDocumentoId;
use App\Matriculas\Responsableestudiante;
use App\Matriculas\Tiporesponsable;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Core\PasswordReset;

class MatriculaController extends ModeloController
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::check()) {
            $colegio = Colegio::get_colegio_user();
        }

        if (is_null($colegio)) {
            return "La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.";
        }

        $periodo_lectivo = PeriodoLectivo::get_actual();

        /**   ALGUNAS ESTADISTICAS            **/
        $alumnos_por_curso = ReportesController::grafica_estudiantes_x_curso($periodo_lectivo->id);
        $generos = ReportesController::grafica_estudiantes_x_genero($periodo_lectivo->id);

        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Matrículas']
        ];

        return view('matriculas.index', compact('generos', 'alumnos_por_curso', 'miga_pan', 'periodo_lectivo'));
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
        FORMULARIO PARA CREAR MATRICULA DE UN ESTUDIANTE, Con base en el id de la INSCRIPCION
     */
    public function crear_nuevo(Request $request)
    {
        //dd( $request->id_inscripcion );

        // LLAMAR AL FORMULARIO PARA CREAR UNA NUEVA MATRICULA, SEGÚN EL DOC. ID DEL ESTUDIANTE
        $inscripcion = Inscripcion::find( $request->id_inscripcion );

        $tercero = Tercero::find( $inscripcion->core_tercero_id );

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

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
        $estudiante = Estudiante::get_estudiante_x_tercero_id($tercero->id);

        if ( !is_null( $estudiante ) ) {
            $matriculas = Matricula::get_matriculas_un_estudiante($estudiante->id);
            $estudiante_existe = 1;
        } else {
            $matriculas = array();
            $estudiante_existe = 0;
        }

        $miga_pan = [
            ['url' => 'matriculas?id=' . Input::get('id'), 'etiqueta' => 'Matrículas'],
            ['url' => 'matriculas/create?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'), 'etiqueta' => 'Nueva'],
            ['url' => 'NO', 'etiqueta' => 'Crear nueva']
        ];

        $colegio = Colegio::get_colegio_user();
        $id_colegio = $colegio->id;

        //obtenemos el listado de tipos de responsables (PAPA, MAMA, RESPONSABLE-FINANCIERO, ACUDIENTE, ETC)
        $tipos = Tiporesponsable::all();
        $tiposdoc = TipoDocumentoId::all();
        return view('matriculas.crear_nuevo', compact('matriculas', 'miga_pan', 'form_create', 'estudiante', 'id_colegio', 'inscripcion', 'estudiante_existe', 'tipos', 'tiposdoc'));
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
            /**/
            $name = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
            $email = $request->email;
            $password = str_random(7);
            $user = User::crear_y_asignar_role($name, $email, 4, $password); // 4 = Role Estudiante

            // Se almacena la contraseña temporalmente; cuando el usuario la cambie, se eliminará
            PasswordReset::insert([
                                    'email' => $email,
                                    'token' => $password ]);

            $datos = array_merge(
                                    $request->all(),
                                    ['user_id' => $user->id]
                                );

            $estudiante = Estudiante::create( $datos );
            
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

        }

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

        return redirect('matriculas/show/' . $matricula->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Matrícula creada correctamente. Código: ' . $matricula->codigo);
    }

    public function actualizar_estado_ultima_inscripcion( $core_tercero_id )
    {
        $inscripcion = Inscripcion::where( 'core_tercero_id', $core_tercero_id )
                                    ->get()
                                    ->last();

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

        // Crear vista
        $view =  View::make('matriculas.pdf_matricula', compact('matricula', 'estudiante'))->render();

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

        return view('matriculas.show_matricula', compact('modelo', 'app', 'matricula', 'reg_anterior', 'reg_siguiente', 'miga_pan', 'view_pdf', 'id'));
    }

    public function imprimir($id)
    {
        $view = MatriculaController::vista_preliminar($id);
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';

        // Crear PDF
        $pdf = \App::make('dompdf.wrapper');
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

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $matricula = app($modelo->name_space)->find($id);

        if ($matricula->periodo_lectivo_id != 0) {
            if (PeriodoLectivo::find($matricula->periodo_lectivo_id)->cerrado) {
                return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Matrícula no puede ser MODIFICADA. El Periodo Lectivo está cerrado.');
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
        if (!empty($tabla_existe)) {
            // Verificación 1: Libreta de pagos
            $cantidad = TesoLibretasPago::where('matricula_id', $id)->count();
            if ($cantidad != 0) {
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
        if ($cant_calificaciones != 0) {
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

        if ($cant_calificaciones != 0) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Matrícula NO puede ser eliminada. El estudiante tiene OBSERVACIONES de boletín resgistradas.');
        }


        // Si hay SOLO una (1) matrícula, se elimina al usuario y al estudiante
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

                //Borrar Estudiante
                $estudiante->delete();
            }
        }

        $this->actualizar_estado_ultima_inscripcion( $registro->estudiante->core_tercero_id );
        
        //Borrar Matrícula
        $registro->delete();


        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Matrícula ELIMINADA correctamente. Código: ' . $registro->codigo);
    }
}


/*
    array:17 [▼
  "_token" => "r9JxNq41ZMNyUzgcDsZJVxKMlITeOqXjXKSpd3fs"
  "id_colegio" => "1"
  "codigo" => "19018-02"
  "fecha_matricula" => "2020-10-22"
  "sga_grado_id" => "6-02"
  "curso_id" => "5"
  "periodo_lectivo_id" => "2"
  "numero_docp" => array:1 [▶]
  "tiporesponsable_idp" => ""
  "grupo_sanguineo" => ""
  "medicamentos" => ""
  "alergias" => ""
  "eps" => ""
  "lineas_registros" => "[{"tercero_id":"198","tipo_responsable_id":"3","Tercero":"1.065.567.198 - Adalberto  Pérez Oliveros","Dirección":"CL 7 22 39","Teléfono":"314 656 1062","Correo":"ing.adalbertoperez@gmail.com","Tipo de responsable":"RESPONSABLE-FINANCIERO"},{"tercero_id":"","tipo_responsable_id":"","Tercero":":\n\t\n\t\tPAPAMAMA","Dirección":""}]"
  "responsable_agregado" => "1"
  "url_id" => "1"
  "url_id_modelo" => "19"
]

*/