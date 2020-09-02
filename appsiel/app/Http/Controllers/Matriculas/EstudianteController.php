<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\Sistema\ModeloController;

use DB;
use PDF;
use View;
use Auth;
use Storage;
use Input;

use App\Sistema\Modelo;
use App\Sistema\Html\Boton;

use App\User;

// Modelos
use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;
use App\Matriculas\Grado;
use App\Matriculas\Curso;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Boletin;
use App\Core\Colegio;
use App\Core\Tercero;
use App\Core\TipoDocumentoId;
use App\Matriculas\Responsableestudiante;
use App\Matriculas\Tiporesponsable;

use App\Ventas\Cliente;

class EstudianteController extends ModeloController
{
    protected $modelo;

    public function edit($id)
    {
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $tercero = Tercero::find($registro->core_tercero_id);

        $lista_campos = ModeloController::get_campos_modelo($modelo, $registro, 'edit');

        //eliminamos los campos de acudiente por defecto en el modelo
        if (count($lista_campos) > 0) {
            foreach ($lista_campos as $key => $lc) {
                if ($lc['name'] == 'acudiente' || $lc['name'] == 'cedula_acudiente' || $lc['name'] == 'telefono_acudiente' || $lc['name'] == 'email_acudiente' || $lc['name'] == 'mama' || $lc['name'] == 'cedula_mama' || $lc['name'] == 'ocupacion_mama' || $lc['name'] == 'email_mama' || $lc['name'] == 'telefono_mama' || $lc['name'] == 'papa' || $lc['name'] == 'cedula_papa' || $lc['name'] == 'ocupacion_papa' || $lc['name'] == 'email_papa' || $lc['name'] == 'telefono_papa') {
                    unset($lista_campos[$key]);
                }
            }
        }
        //generamos los indices de nuevo del array para que el switch no presente error
        $listaTemp = null;
        foreach ($lista_campos as $lc) {
            $listaTemp[] = $lc;
        }

        $lista_campos = $listaTemp;

        //Personalización de la lista de campos
        for ($i = 0; $i < count($lista_campos); $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'nombre1':
                    $lista_campos[$i]['value'] = $tercero->nombre1;
                    break;
                case 'otros_nombres':
                    $lista_campos[$i]['value'] = $tercero->otros_nombres;
                    break;
                case 'apellido1':
                    $lista_campos[$i]['value'] = $tercero->apellido1;
                    break;
                case 'apellido2':
                    $lista_campos[$i]['value'] = $tercero->apellido2;
                    break;
                case 'id_tipo_documento_id':
                    $lista_campos[$i]['value'] = $tercero->id_tipo_documento_id;
                    break;
                case 'numero_identificacion':
                    $lista_campos[$i]['value'] = $tercero->numero_identificacion;
                    break;
                case 'direccion1':
                    $lista_campos[$i]['value'] = $tercero->direccion1;
                    break;
                case 'barrio':
                    $lista_campos[$i]['value'] = $tercero->barrio;
                    break;
                case 'telefono1':
                    $lista_campos[$i]['value'] = $tercero->telefono1;
                    break;
                case 'email':
                    $lista_campos[$i]['value'] = $tercero->email;
                    break;
                case 'codigo_ciudad':
                    $lista_campos[$i]['value'] = $tercero->codigo_ciudad;
                    break;

                default:
                    # code...
                    break;
            }
        }

        // Agregar NUEVO campo con el core_tercero_id
        $lista_campos[$i]['tipo'] = 'hidden';
        $lista_campos[$i]['name'] = 'core_tercero_id';
        $lista_campos[$i]['descripcion'] = '';
        $lista_campos[$i]['opciones'] = [];
        $lista_campos[$i]['value'] = $tercero->id;
        $lista_campos[$i]['atributos'] = [];
        $lista_campos[$i]['requerido'] = false;

        // form_create para generar un formulario html 
        $form_create = [
            'url' => $modelo->url_form_create,
            'campos' => $lista_campos
        ];

        $url_action = $modelo->url_form_create . '/' . $id;

        $miga_pan = $this->get_miga_pan($modelo, $registro->descripcion);

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($modelo->name_space)->archivo_js;

        //obtengo los datos de papá, mamá, acudiente, responsable financiero, etc...
        $responsables = $registro->responsableestudiantes;

        return view('layouts.edit', compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action', 'responsables'));
    }

    /*
        Visualizar los datos de un estudiante
    */
    public function show($estudiante_id)
    {
        $estudiante = Estudiante::get_datos_basicos($estudiante_id); // Se obtiene el registro del modelo indicado y el anterior y siguiente registro

        $registro = app($this->modelo->name_space)->find($estudiante_id);
        $reg_anterior = app($this->modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($this->modelo->name_space)->where('id', '>', $registro->id)->min('id');

        $miga_pan = $this->get_miga_pan($this->modelo, $registro->descripcion);

        $url_crear = '';
        $url_edit = '';

        $id_transaccion = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        if ($this->modelo->url_crear != '') {
            $url_crear = $this->modelo->url_crear . $variables_url;
        }
        if ($this->modelo->url_edit != '') {
            $url_edit = $this->modelo->url_edit . $variables_url;
        }

        // ENLACES
        $botones = [];
        if ($this->modelo->enlaces != '') {
            $enlaces = json_decode($this->modelo->enlaces);
            $i = 0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
            }
        }

        return view('matriculas.estudiantes.show', compact('miga_pan', 'registro', 'url_crear', 'url_edit', 'reg_anterior', 'reg_siguiente', 'botones', 'estudiante'));
    }

    /**
     * Muestra formulario para generar listados de estudiantes
     *
     */
    public function listar()
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $periodos_lectivos = PeriodoLectivo::get_array_activos();


        $registros = Grado::where(['id_colegio' => $colegio->id, 'estado' => 'Activo'])
            ->get();
        $grados['Todos'] = 'Todos';
        foreach ($registros as $fila) {
            $grados[$fila->id] = $fila->descripcion;
        }

        $miga_pan = [
            ['url' => 'matriculas?id=' . Input::get('id'), 'etiqueta' => 'Matrículas'],
            ['url' => 'web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'), 'etiqueta' => 'Estudiantes'],
            ['url' => 'NO', 'etiqueta' => 'Listados']
        ];

        return view('matriculas.estudiantes.listar', compact('periodos_lectivos', 'grados', 'miga_pan'));
    }

    /**
     * Muestra formulario para importar estudiantes
     *
     */
    public function importar_excel()
    {
        return view('matriculas.estudiantes.importar_excel.index');
    }

    /**
     * Genera en PDF listados de estudiantes. Además, actualiza los datos al modificar un estudiante.
     *
     */
    public function update(Request $request, $id)
    {
        $estudiantes = [];

        switch ($id) {
            case 'listado':

                if ($request->sga_grado_id == "Todos") {
                    $grados = Grado::where('estado', 'Activo')->get();
                } else {
                    // Un grado específico
                    $grados = Grado::where('id', $request->sga_grado_id)->get();
                }

                $i = 0;
                foreach ($grados as $fila_grado) {

                    $grado = Grado::find($fila_grado->id);

                    if ($request->curso_id == "Todos") {
                        $cursos = Curso::where('sga_grado_id', $grado->id)->where('estado', 'Activo')->get();
                    } else {
                        $cursos = Curso::where('id', $request->curso_id)->get();
                    }

                    foreach ($cursos as $fila_curso) {
                        $estudiantes[$i]['grado'] = $grado->descripcion;

                        $curso = Curso::find($fila_curso->id);
                        $estudiantes[$i]['curso'] = $curso->descripcion;

                        $estudiantes[$i]['listado'] = Matricula::estudiantes_matriculados($curso->id, $request->periodo_lectivo_id, null);
                        $i++;
                    }
                }

                $orientacion = $request->orientacion;

                /*
					Formato 1 = Listado por asignaturas
					Formato 2 = Ficha Datos básicos
					Formato 3 = Lista Datos básicos
					Formato 4 = Lista de usuarios
				*/
                $formato = 'pdf_estudiantes' . $request->tipo_listado;

                $tam_letra = $request->tam_letra;

                $view =  View::make('matriculas/estudiantes/' . $formato, compact('estudiantes', 'tam_letra'))->render();

                //crear PDF
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML(($view))->setPaper($request->tam_hoja, $orientacion);

                return $pdf->download('listado_estudiantes.pdf');

                break;

            default:

                // Para cualquier $id (cualquier estudiante), se actualizan los datos en las tablas respectivas: terceros, users, estudiantes
                $estudiante = Estudiante::find($id);


                $registro2 = '';
                // Si se envían datos tipo file
                //if ( count($request->file()) > 0)
                if (!empty($request->file())) {
                    // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
                    $registro2 = $estudiante;
                }

                $tercero = Tercero::find($estudiante->core_tercero_id);

                $descripcion = $request->nombre1 . ' ' . $request->otros_nombres . ' ' . $request->apellido1 . ' ' . $request->apellido2;
                $datos = array_merge($request->all(), [
                    'descripcion' => $descripcion
                ]);

                $tercero->fill($datos);
                $tercero->save();

                $usuario = User::find($estudiante->user_id);
                if (is_null($usuario)) {
                    $name = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
                    $email = $request->email;
                    $usuario = User::crear_y_asignar_role($name, $email, 4); // 4 = Role Estudiante
                    $mensaje = '<br> Se creó un nuevo usuario para el estudiante. <br> Puede acceder al sistema con los siguientes datos: <br> email: ' . $request->email . ' <br> Contraseña: colombia1';
                } else {
                    $usuario->name = $descripcion;
                    $usuario->email = $request->email;
                    $usuario->save();
                    $mensaje = '';
                }

                $estudiante->fill($datos);
                $estudiante->user_id = $usuario->id;
                $estudiante->save();

                if (isset($request->imagen)) {
                    $modelo = Modelo::find($request->url_id_modelo);
                    $general = new ModeloController;
                    $tercero->imagen = $general->almacenar_imagenes($request, $modelo->ruta_storage_imagen, $registro2, 'edit');
                    $tercero->save();
                }


                return redirect('matriculas/estudiantes/show/' . $id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Registro MODIFICADO correctamente.' . $mensaje);

                break;
        }
    }

    public static function nombre_curso($id_estudiante)
    {
        $curso_id = Matricula::where('id_estudiante', "=", $id_estudiante)->where('estado', "=", 'Activo')->value('curso_id');


        if (!is_null($curso_id)) {
            $curso = Curso::find($curso_id);
            $nombre_curso = $curso->descripcion;
        } else {
            $nombre_curso = "";
        }

        return $nombre_curso;
    }

    public static function nombre_acudiente($id_estudiante)
    {
        $nombre_acudiente = Matricula::where('id_estudiante', "=", $id_estudiante)->where('estado', "=", 'Activo')->value('acudiente');
        return $nombre_acudiente;
    }

    public static function get_estudiantes_matriculados($periodo_lectivo_id, $curso_id)
    {
        $registros = Matricula::estudiantes_matriculados($curso_id, $periodo_lectivo_id, null);

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $opcion) {
            $opciones .= '<option value="' . $opcion->id . '">' . $opcion->nombre_completo . '</option>';
        }

        return $opciones;
    }

    //presenta la vista index para gestionar la información de los responsables de un estudiante
    public function gestionresponsables()
    {

        $mod = Modelo::find(Input::get('id_modelo'));
        // Se obtiene el registro a modificar del modelo

        $estudiante = Estudiante::find(Input::get('estudiante_id')); // Se obtiene el registro del modelo indicado
        $tercero = $estudiante->getTercero($estudiante->id);
        $miga_pan = $this->get_miga_pan($mod, $mod->descripcion);
        $id_transaccion = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion . '&estudiante_id=' . $estudiante->id;

        $lista = $estudiante->responsableestudiantes;
        $lista2 = null;
        if (count($lista) > 0) {
            foreach ($lista as $ll) {
                $o = null;
                $o['tdid'] = $ll->tercero->id_tipo_documento_id;
                $o['trid'] = $ll->tiporesponsable_id;
                $o['id'] = $ll->id;
                $o['td'] = TipoDocumentoId::find($ll->tercero->id_tipo_documento_id)->descripcion;
                $o['doc'] = $ll->tercero->numero_identificacion;
                $o['nom'] = $ll->tercero->descripcion;
                $o['tr'] = $ll->tiporesponsable->descripcion;
                $o['tel'] = $ll->tercero->telefono1;
                $o['email'] = $ll->tercero->email;
                $o['estado'] = $ll->tercero->estado;
                $o['ocu'] = $ll->ocupacion;
                $o['dt'] = $ll->direccion_trabajo;
                $o['tt'] = $ll->telefono_trabajo;
                $o['pt'] = $ll->puesto_trabajo;
                $o['indt'] = $ll->descripcion_trabajador_independiente;
                $o['et'] = $ll->empresa_labora;
                $o['jt'] = $ll->jefe_inmediato;
                $o['tjt'] = $ll->telefono_jefe;
                $o['pne'] = $ll->tercero->nombre1;
                $o['sne'] = $ll->tercero->otros_nombres;
                $o['pae'] = $ll->tercero->apellido1;
                $o['sae'] = $ll->tercero->apellido2;
                $lista2[] = $o;
            }
        }
        $tipos = Tiporesponsable::all();
        $tiposdoc = TipoDocumentoId::all();
        return view('matriculas.estudiantes.gestionresponsables', compact('miga_pan', 'lista2', 'tipos', 'tiposdoc', 'tercero', 'estudiante', 'variables_url', 'lista'));
    }

    //guarda un responsable
    public function gestionresponsables_store(Request $request)
    {
        $t = new Tercero( $request->all() );
        $t->imagen = " ";
        $t->tipo = "Persona natural";
        $t->razon_social = " ";
        $t->digito_verificacion = 0;
        $t->direccion1 = " ";
        $t->direccion2 = " ";
        $t->barrio = " ";
        $t->descripcion = $t->nombre1 . " " . $t->otros_nombres . " " . $t->apellido1 . " " . $t->apellido2;
        $t->codigo_ciudad = 0;
        $t->codigo_postal = 0;
        $t->telefono2 = 0;
        $t->pagina_web = " ";
        $t->estado = "Activo";
        $t->user_id = 0;
        $t->contab_anticipo_cta_id = 0;
        $t->contab_cartera_cta_id = 0;
        $t->contab_cxp_cta_id = 0;
        $t->creado_por = " ";
        $t->modificado_por = " ";
        if ($t->save()) {
            $r = new Responsableestudiante($request->all());
            if ($request->tiporesponsable_id != 3) {
                $r->direccion_trabajo = " ";
                $r->telefono_trabajo = " ";
                $r->puesto_trabajo = null;
                $r->empresa_labora = null;
                $r->jefe_inmediato = null;
                $r->telefono_jefe = null;
                $r->descripcion_trabajador_independiente = null;
            }
            $r->tercero_id = $t->id;
            if ($r->save()) {

                // Crear Tercero como cliente, cuando es un Responsable financiero
                if ( is_null( Cliente::where( 'core_tercero_id', $t->id)->get()->first() ) &&  $request->tiporesponsable_id == 3)
                {
                    // Datos del Cliente
                    $cliente = new Cliente;
                    $cliente->fill( 
                                    ['core_tercero_id' => $t->id, 'encabezado_dcto_pp_id' => 1, 'clase_cliente_id' => 1, 'lista_precios_id' => 1, 'lista_descuentos_id' => 1, 'vendedor_id' => 1,'inv_bodega_id' => 1, 'zona_id' => 1, 'liquida_impuestos' => 1, 'condicion_pago_id' => 1, 'estado' => 'Activo' ]
                                     );
                    $cliente->save();
                }

                return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id' . $request->variables_url)->with('flash_message', 'Responsable creado correctamente.');
            } else {
                $t->delete();
                return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id' . $request->variables_url)->with('mensaje_error', 'Error, no se pudo almacenar el responsable.');
            }
        } else {
            return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id' . $request->variables_url)->with('mensaje_error', 'Error, no se pudo almacenar el responsable.');
        }
    }

    //elimina un responsable
    public function gestionresponsables_delete($id)
    {
        $r = Responsableestudiante::find($id);
        $estudiante_id = $r->estudiante_id;
        $t = $r->tercero;
        if ($t->delete()) {
            return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=&estudiante_id=' . $estudiante_id)->with('flash_message', 'Responsable eliminado correctamente.');
        } else {
            return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=&estudiante_id=' . $estudiante_id)->with('mensaje_error', 'Error, no se pudo eliminar el responsable.');
        }
    }

    //modifica un responsable
    public function gestionresponsables_update(Request $request)
    {
        $r = Responsableestudiante::find($request->responsable_id);
        $t = $r->tercero;
        $t->nombre1 = $request->nombre1;
        $t->otros_nombres = $request->otros_nombres;
        $t->apellido1 = $request->apellido1;
        $t->apellido2 = $request->apellido2;
        $t->descripcion = $t->nombre1 . " " . $t->otros_nombres . " " . $t->apellido1 . " " . $t->apellido2;
        $t->id_tipo_documento_id = $request->id_tipo_documento_id;
        $t->numero_identificacion = $request->numero_identificacion;
        $t->telefono1 = $request->telefono1;
        $t->email = $request->email;
        if ($t->save()) {
            $r->ocupacion = $request->ocupacion;
            $r->tiporesponsable_id = $request->tiporesponsable_id;
            if ($request->tiporesponsable_id == 3) {
                $r->direccion_trabajo = $request->direccion_trabajo;
                $r->telefono_trabajo = $request->telefono_trabajo;
                $r->puesto_trabajo = $request->puesto_trabajo;
                $r->empresa_labora = $request->empresa_labora;
                $r->jefe_inmediato = $request->jefe_inmediato;
                $r->telefono_jefe = $request->telefono_jefe;
                $r->descripcion_trabajador_independiente = $request->descripcion_trabajador_independiente;
            } else {
                $r->direccion_trabajo = " ";
                $r->telefono_trabajo = " ";
                $r->puesto_trabajo = null;
                $r->empresa_labora = null;
                $r->jefe_inmediato = null;
                $r->telefono_jefe = null;
                $r->descripcion_trabajador_independiente = null;
            }
            if ($r->save()) {
                return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id' . $request->variables_url)->with('flash_message', 'Responsable modificado correctamente.');
            } else {
                return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id' . $request->variables_url)->with('mensaje_error', 'Alerta!, solo se modificaron los datos personales del responsable, los datos financieros no.');
            }
        } else {
            return redirect('matriculas/estudiantes/gestionresponsables/estudiante_id' . $request->variables_url)->with('mensaje_error', 'Error, no se pudo modificar el responsable.');
        }
    }
}
