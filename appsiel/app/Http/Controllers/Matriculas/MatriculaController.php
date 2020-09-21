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
        // LLAMAR AL FORMULARIO PARA CREAR UNA NUEVA MATRICULA, SEGÚN EL DOC. ID DEL ESTUDIANTE
        $inscripcion = Inscripcion::find($request->id_inscripcion);

        $tercero = Tercero::find($inscripcion->core_tercero_id);

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

        if (!is_null($estudiante)) {
            $matriculas = Matricula::get_matriculas_un_estudiante($estudiante->id);
            $estudiante_existe = true;
        } else {
            $matriculas = array();
            $estudiante_existe = false;
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
        return view('matriculas.crear_nuevo', compact('matriculas', 'miga_pan', 'form_create', 'tercero', 'id_colegio', 'inscripcion', 'estudiante_existe', 'tipos', 'tiposdoc'));
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
        if ($request->estudiante_existe == false) {

            $total = 0;

            if (isset($request->id_tipo_documento_idp)) {
                if (count($request->id_tipo_documento_idp) > 0) {
                    foreach ($request->id_tipo_documento_idp as $key => $td) {
                        if ($request->tiporesponsable_idp[$key] == '3' || $request->tiporesponsable_idp[$key] == '4') {
                            $total = $total + 1;
                        }
                    }
                }
            }

            if ($total < 2) {
                return redirect('matriculas/create?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('mensaje_error', 'Debe indicar como mínimo el acudiente y el responsable financiero para crear la matrícula del estudiante');
            }

            $name = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
            $email = $request->email;
            $user = User::crear_y_asignar_role($name, $email, 4); // 4 = Role Estudiante

            $datos = array_merge(
                $request->all(),
                ['user_id' => $user->id]
            );

            $estudiante = Estudiante::create($datos);
            //guardamos papa y mama, guardamos responsable financiero, acudiente, etc
            if (isset($request->id_tipo_documento_idp)) {
                if (count($request->id_tipo_documento_idp) > 0) {
                    foreach ($request->id_tipo_documento_idp as $key => $td) {
                        $t = $this->setTercero($estudiante, $td, $request->numero_docp[$key], $request->nombre1p[$key], $request->otros_nombresp[$key], $request->apellido1p[$key], $request->apellido2p[$key], $request->telefono1p[$key], $request->emailp[$key]);
                        $datosVacios = false;
                        if ($request->datosp[$key] == "") {
                            $datosVacios = true;
                        }
                        $data = explode(";", $request->datosp[$key]);
                        $r = $this->setResponsable($data, $request->ocupacionp[$key], $request->tiporesponsable_idp[$key], $estudiante->id, $t->id, $datosVacios);
                    }
                }
            }
        } else {
            //echo "true";
            // Si ya existe, obtengo el registro según el tercero asociado
            $estudiante = Estudiante::get_estudiante_x_tercero_id($request->core_tercero_id);
            //print_r($estudiante);
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
                'codigo' => $codigo
            ]
        );


        // Obtener el id de la ultima matricula activa de ese estudiante
        $matricula_activa = Matricula::get_matricula_activa_un_estudiante($estudiante->id);

        if (!is_null($matricula_activa)) {
            // Inactivar la matricula anterior antes de crear la nueva
            Matricula::inactivar($matricula_activa->id);
        }

        $matricula = Matricula::create($datos2);

        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo('matriculas');

        return redirect('matriculas/show/' . $matricula->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Matrícula creada correctamente. Código: ' . $matricula->codigo);
    }

    //crea un tercero para los papás
    public function setTercero($estudiante, $td, $numero_docp, $nombre1p, $otros_nombresp, $apellido1p, $apellido2p, $telefono1p, $emailp)
    {
        $t = null;
        //si el tercero ya existe solamente es asignado como responsable y si no existe se crea
        $t = Tercero::where('numero_identificacion', $numero_docp)->first();
        if ($t == null) {
            $t = new Tercero();
            $t->core_empresa_id = $estudiante->getTercero($estudiante->id)->core_empresa_id;
            $t->imagen = " ";
            $t->tipo = "Persona natural";
            $t->razon_social = " ";
            $t->nombre1 = $nombre1p;
            $t->otros_nombres = $otros_nombresp;
            $t->apellido1 = $apellido1p;
            $t->apellido2 = $apellido2p;
            $t->descripcion = $nombre1p . " " . $otros_nombresp . " " . $apellido1p . " " . $apellido2p;
            $t->id_tipo_documento_id = $td;
            $t->numero_identificacion = $numero_docp;
            $t->digito_verificacion = 0;
            $t->direccion1 = " ";
            $t->direccion2 = " ";
            $t->barrio = " ";
            $t->codigo_ciudad = 0;
            $t->codigo_postal = 0;
            $t->telefono1 = $telefono1p;
            $t->telefono2 = 0;
            $t->email = $emailp;
            $t->pagina_web = " ";
            $t->estado = "Activo";
            $t->user_id = 0;
            $t->contab_anticipo_cta_id = 0;
            $t->contab_cartera_cta_id = 0;
            $t->contab_cxp_cta_id = 0;
            $t->creado_por = " ";
            $t->modificado_por = " ";
            $t->save();
        }
        return $t;
    }

    //crea un responsable para los papás
    public function setResponsable($data, $ocupacionp, $tiporesponsable_idp, $estudiante, $tercero, $datosVacios)
    {
        $r = new Responsableestudiante();
        if ($datosVacios) {
            $r->direccion_trabajo = " ";
            $r->telefono_trabajo = " ";
            $r->puesto_trabajo = null;
            $r->empresa_labora = null;
            $r->jefe_inmediato = null;
            $r->telefono_jefe = null;
            $r->descripcion_trabajador_independiente = null;
        } else {
            $r->direccion_trabajo = $data[0];
            $r->telefono_trabajo = $data[1];
            $r->puesto_trabajo = $data[2];
            $r->empresa_labora = $data[3];
            $r->jefe_inmediato = $data[4];
            $r->telefono_jefe = $data[5];
            $r->descripcion_trabajador_independiente = $data[6];
        }
        $r->ocupacion = $ocupacionp;
        $r->tiporesponsable_id = $tiporesponsable_idp;
        $r->estudiante_id = $estudiante;
        $r->tercero_id = $tercero;
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
        $registro = app($modelo->name_space)->find($id);

        if ($registro->periodo_lectivo_id != 0) {
            if (PeriodoLectivo::find($registro->periodo_lectivo_id)->cerrado) {
                return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Matrícula no puede ser MODIFICADA. El Periodo Lectivo está cerrado.');
            }
        }


        // Se vefifica si la matrícula tiene calificaciones, entonces no se podrá modificar el grado ni el curso. 
        $cant_calificaciones = 0;

        // Verificar si el estudiante ya tiene calificaciones con esta matrícula, entonces no se podrá cambiar el Grado
        $cant_calificaciones = Calificacion::get_cantidad_x_matricula($registro->id_colegio, $registro->codigo);

        // Si no tiene calificaciones, tambien se validan las observaciones
        if ($cant_calificaciones == 0) {
            $cant_calificaciones = ObservacionesBoletin::get_cantidad_x_matricula($registro->id_colegio, $registro->codigo);
        }

        $lista_campos = ModeloController::get_campos_modelo($modelo, $registro, 'edit');

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

        $curso = Curso::find($registro->curso_id);
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

        $miga_pan = $this->get_miga_pan($modelo, $registro->codigo);



        $estudiante = Estudiante::find($registro->id_estudiante);

        //print_r($estudiante);
        $tercero = Tercero::find($estudiante->core_tercero_id);

        return view('matriculas.edit', compact('registro', 'cant_calificaciones', 'miga_pan', 'tercero', 'form_create'));
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
        if (count($todas_las_matriculas->toArray()) == 1) {

            $estudiante = Estudiante::find($registro->id_estudiante);

            if (!is_null($estudiante)) {
                $user = User::find($estudiante->user_id);

                //Borrar User
                if (!is_null($user)) {
                    $user->roles()->sync([]); // borrar todos los roles y asignar los del array (en este caso vacío)
                    $user->delete();
                }

                //Borrar Estudiante
                $estudiante->delete();
            }
        }


        //Borrar Matrícula
        $registro->delete();


        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Matrícula ELIMINADA correctamente. Código: ' . $registro->codigo);
    }
}
