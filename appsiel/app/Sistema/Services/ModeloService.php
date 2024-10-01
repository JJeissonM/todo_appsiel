<?php

namespace App\Sistema\Services;

use App\Calificaciones\Logro;
use App\Core\ModeloEavValor;
use App\Sistema\Modelo;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class ModeloService
{
    public function acciones_basicas_modelo( Modelo $modelo, string $parametros_url)
    {
        // Acciones predeterminadas
        $acciones = (object)[
            'index' => 'web' . $parametros_url,
            'create' => '',
            'edit' => '',
            'store' => 'web',
            'update' => 'web/id_fila',
            'show' => 'web/id_fila' . $parametros_url,
            'imprimir' => '',
            'eliminar' => '',
            'cambiar_estado' => '',
            'otros_enlaces' => ''
        ];


        // Se agregan los enlaces que tiene el modelo en la base de datos (ESTO DEBE DESAPARECER, PERO PRIMERO SE DEBEN MIGRAR LOS MODELOS ANTIGUOS)
        if ($modelo->url_crear != '') {
            $acciones->create = $modelo->url_crear . $parametros_url;
        }

        if ($modelo->url_edit != '') {
            $acciones->edit = $modelo->url_edit . $parametros_url;
        }

        if ($modelo->url_form_create != '') {
            $acciones->store = $modelo->url_form_create;
            $acciones->update = $modelo->url_form_create . '/id_fila';
        }

        if ($modelo->url_print != '') {
            $acciones->imprimir = $modelo->url_print . $parametros_url;
        }

        if ($modelo->url_ver != '') {
            $acciones->show = $modelo->url_ver . $parametros_url;
        }

        if ($modelo->url_estado != '') {
            $acciones->cambiar_estado = $modelo->url_estado . $parametros_url;
        }

        if ($modelo->url_eliminar != '') {
            $acciones->eliminar = $modelo->url_eliminar . $parametros_url;
        }

        // Otros enlaces en formato JSON
        if ($modelo->enlaces != '') {
            $acciones->otros_enlaces = $modelo->enlaces;
        }

        // MANEJO DE URLs DESDE EL ARCHIVO CLASS DEL PROPIO MODELO 
        // Se llaman las urls desde la class (name_space) del modelo
        $urls_acciones = json_decode(app($modelo->name_space)->urls_acciones);

        if (!is_null($urls_acciones)) {

            // Acciones particulares, si están definidas en la variable $urls_acciones de la class del modelo

            if (isset($urls_acciones->create)) {
                if ($urls_acciones->create != 'no') {
                    $acciones->create = $urls_acciones->create . $parametros_url;
                }
            }

            if (isset($urls_acciones->edit)) {
                if ($urls_acciones->edit != 'no') {
                    $acciones->edit = $urls_acciones->edit . $parametros_url;
                }
            }

            if (isset($urls_acciones->store)) {
                if ($urls_acciones->store != 'no') {
                    $acciones->store = $urls_acciones->store;
                }
            }

            if (isset($urls_acciones->update)) {
                if ($urls_acciones->update != 'no') {
                    $acciones->update = $urls_acciones->update;
                }
            }

            if (isset($urls_acciones->show)) {
                if ($urls_acciones->show != 'no') {
                    $acciones->show = $urls_acciones->show . $parametros_url;
                }

                if ($urls_acciones->show == 'no') {
                    $acciones->show = '';
                }
            }

            if (isset($urls_acciones->imprimir)) {
                if ($urls_acciones->imprimir != 'no') {
                    $acciones->imprimir = $urls_acciones->imprimir . $parametros_url;
                }
            }

            if (isset($urls_acciones->eliminar)) {
                if ($urls_acciones->eliminar != 'no') {
                    $acciones->eliminar = $urls_acciones->eliminar . $parametros_url;
                }
            }

            if (isset($urls_acciones->cambiar_estado)) {
                if ($urls_acciones->cambiar_estado != 'no') {
                    $acciones->cambiar_estado = $urls_acciones->cambiar_estado . $parametros_url;
                }
            }

            // Otros enlaces en formato JSON
            if (isset($urls_acciones->otros_enlaces)) {
                if ($urls_acciones->otros_enlaces != 'no') {
                    $acciones->otros_enlaces = $urls_acciones->otros_enlaces;
                }
            }
        }

        return $acciones;
    }

    /**
     *  Construir un array con los campos asociados al modelo
     */
    public function get_campos_modelo($modelo, $registro, $accion)
    {
        // Se obtienen los campos asociados a ese modelo
        $lista_campos1 = $modelo->campos()->orderBy('orden')->get();

        $lista_campos = $this->ajustar_valores_lista_campos($lista_campos1->toArray());

        // Ajustar los valores según la acción
        $lista_campos = $this->ajustar_valores_lista_campos_segun_accion($lista_campos, $registro, $modelo, $accion);

        return $lista_campos;
    }

    public function ajustar_valores_lista_campos( $lista_campos )
    {
        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++) {
            $nombre_campo = $lista_campos[$i]['name'];

            // El campo Atributos se ingresa en  formato JSON {"campo1":"valor1","campo2":"valor2"}
            // Luego se tranforma a un array para que pueda ser aceptado por el Facade Form:: de LaravelCollective
            if ($lista_campos[$i]['atributos'] != '') {

                $lista_campos[$i]['atributos'] = json_decode($lista_campos[$i]['atributos'], true);

                // Para el tipo de campo Input Lista Sugerencias
                if (isset($lista_campos[$i]['atributos']['data-url_busqueda'])) {
                    $lista_campos[$i]['atributos']['data-url_busqueda'] = url($lista_campos[$i]['atributos']['data-url_busqueda']);
                }
            } else {
                $lista_campos[$i]['atributos'] = [];
            }

            // Cuando el campo es requerido se agrega el atributo al control html
            if ($lista_campos[$i]['requerido']) {
                $lista_campos[$i]['atributos'] = array_merge($lista_campos[$i]['atributos'], ['required' => 'required']);
            }

            // Cuando se está editando un registro, el formulario llamado por LaravelCollective Form::model(), llena los campos que tienen valor null con los valores del registro del modelo instanciado

            if ($lista_campos[$i]['value'] == 'null') {
                $lista_campos[$i]['value'] = null;
            }

            // Para llenar los campos tipo select, checkbox y multiselect_autocomplete
            if ($lista_campos[$i]['tipo'] == 'select' || $lista_campos[$i]['tipo'] == 'bsCheckBox' || $lista_campos[$i]['tipo'] == 'multiselect_autocomplete') {
                $lista_campos[$i]['opciones'] = (new VistaService())->get_opciones_campo_tipo_select( $lista_campos[$i] );
            }
        }
        return $lista_campos;
    }

    public function ajustar_valores_lista_campos_segun_accion($lista_campos, $registro, $modelo, $accion)
    {
        $user_id = 0;
        $user_email = 'guest@appsiel.com.co';
        $user = Auth::user();
        if ($user != null) {
            $user_id = $user->id;
            $user_email = $user->email;
        }

        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++) {
            $nombre_campo = $lista_campos[$i]['name'];

            if ($accion == 'create') {

                // Valores predeterminados para Algunos campos ocultos
                switch ($lista_campos[$i]['name']) {
                    case 'creado_por':
                        $lista_campos[$i]['value'] = $user_email;
                        break;
                    case 'modificado_por':
                        $lista_campos[$i]['value'] = 0;
                        break;
                    case 'user_id':
                        $lista_campos[$i]['value'] = $user_id;
                        break;
                    default:
                        # code...
                        break;
                }

                if ($lista_campos[$i]['tipo'] == 'input_lista_sugerencias') {
                    // value es un array con los valores para text_input y para el input hidden
                    $lista_campos[$i]['value'] = ['', ''];
                }
            } else { // Si se está editando

                // asignar valor almacenado en la BD al cada campo
                if (isset($registro->$nombre_campo)) {
                    $lista_campos[$i]['value'] = $registro->$nombre_campo;
                }

                // Si el campo NO es editable, se muestra deshabilitado
                if (!$lista_campos[$i]['editable']) {
                    /*
                        Advertencia cuando el campo está deshabilitado NO es enviado en el request del formulario
                        Su valor no es actualizado.
                        No se puede usar su valor (que no existe) en otras acciones.
                    */
                    $lista_campos[$i]['atributos'] = ['disabled' => 'disabled', 'style' => 'background-color:#FBFBFB;'];

                    if ($lista_campos[$i]['tipo'] == 'personalizado') {
                        $lista_campos[$i]['value'] = '';
                    }
                } else {
                    if ($lista_campos[$i]['tipo'] == 'input_lista_sugerencias') {
                        $campo_del_modelo = $lista_campos[$i]['name'];
                        $registro_input = app($lista_campos[$i]['atributos']['data-clase_modelo'])->find($registro->$campo_del_modelo);

                        // value es un array con los valores para text_input y para el input hidden
                        $lista_campos[$i]['value'] = [$registro_input->descripcion . ' (' . number_format($registro_input->numero_identificacion, 0, ',', '.') . ')', $registro->$campo_del_modelo];
                    }
                }

                switch ($lista_campos[$i]['name']) {
                    case 'creado_por':
                        $lista_campos[$i]['value'] = null;
                        break;

                    case 'modificado_por':
                        $lista_campos[$i]['value'] = $user_email;
                        break;

                    case 'role':
                        $usuario = User::find($registro->id);
                        $role = $usuario->roles()->get()[0];
                        $lista_campos[$i]['value'] = $role->id;
                        break;

                    case 'escala_valoracion':
                        $logros = Logro::get_logros_periodo_curso_asignatura($registro->periodo_id, $registro->curso_id, $registro->asignatura_id);
                        $descripciones = [];
                        $el_primero = true;
                        foreach ($logros as $un_logro) {
                            $descripciones[$un_logro->escala_valoracion_id] = $un_logro->descripcion;
                        }

                        $lista_campos[$i]['value'] = $descripciones;
                        break;

                    default:
                        # code...
                        break;
                }

                // Si hay campo tipo imagen, se envía la URL de la imagen para mostrala
                if ($lista_campos[$i]['tipo'] == 'imagen') {
                    $lista_campos[$i]['value'] = config('configuracion.url_instancia_cliente') . "/storage/app/" . $modelo->ruta_storage_imagen . $registro->$nombre_campo;
                }

                // Si hay campo tipo imagenes_multiples, se envía la imagen para mostrala
                if ($lista_campos[$i]['tipo'] == 'imagenes_multiples') {
                    // Esto debe cambiar!!!!!!
                    $lista_campos[$i]['value'] = config('configuracion.url_instancia_cliente') . "/storage/app/" . $modelo->ruta_storage_imagen . $registro->$nombre_campo;
                }

                // Si se está editando un checkbox
                if ($lista_campos[$i]['tipo'] == 'bsCheckBox')
                {
                    // Si el name del campo enviado tiene la palabra core_campo_id-ID, se trata de un campo Atributo de EAV
                    if (strpos($lista_campos[$i]['name'], "core_campo_id-") !== false) {
                        $lista_campos[$i]['value'] = ModeloEavValor::where(["modelo_padre_id" => Input::get('modelo_padre_id'), "registro_modelo_padre_id" => Input::get('registro_modelo_padre_id'), "modelo_entidad_id" => Input::get('modelo_entidad_id'), "core_campo_id" => $lista_campos[$i]['id']])->value('valor');
                    } else {
                        $lista_campos[$i]['value'] = $registro->$nombre_campo;
                    }
                }
            }
        }

        return $lista_campos;
    }

    public function personalizar_campos($id_transaccion, $tipo_transaccion, $lista_campos, $cantidad_campos, $accion )
    {
        $opciones = [];
        // Se crea un select SOLO con las opciones asignadas a la transacción
        if (!is_null($tipo_transaccion)) {
            $tipo_docs_app = $tipo_transaccion->tipos_documentos;
            foreach ($tipo_docs_app as $fila) {
                $opciones[$fila->id] = $fila->prefijo . " - " . $fila->descripcion;
            }
        }

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++) {

            if ($lista_campos[$i]['name'] == 'core_tipo_doc_app_id') {
                $lista_campos[$i]['opciones'] = $opciones;
            }

            // Valores predeterminados para los campos ocultos
            if ($accion == 'create') {
                if ($lista_campos[$i]['name'] == 'core_tipo_transaccion_id') {
                    $lista_campos[$i]['value'] = $tipo_transaccion->id;
                }
                if ($lista_campos[$i]['name'] == 'estado') {
                    $lista_campos[$i]['value'] = 'Activo';
                }

                if ($lista_campos[$i]['name'] == 'user_id') {
                    $lista_campos[$i]['value'] = Auth::user()->id;
                }

                // Cuando la transacción es "Generar CxC"
                if ($lista_campos[$i]['name'] == 'core_tercero_id' and $id_transaccion == 5) {
                    $lista_campos[$i]['requerido'] = false;
                    $lista_campos[$i]['tipo'] = 'hidden';
                }
            } else {
                if ($lista_campos[$i]['name'] == 'core_tipo_transaccion_id') {
                    $lista_campos[$i]['value'] = null;
                }
                if ($lista_campos[$i]['name'] == 'estado') {
                    $lista_campos[$i]['value'] = null;
                }
            }

            unset($vec_m);
            if ($lista_campos[$i]['name'] == 'user_asignado_id') {

                $registros = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Administrador PH', 'SuperAdmin']);
                })->get();

                //$registros = TesoCaja::where('core_empresa_id',Auth::user()->empresa_id)->get();       
                foreach ($registros as $fila) {
                    $vec_m[$fila->id] = $fila->name;
                }

                if (count($vec_m) == 0) {
                    $vec_m[''] = '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }
        }

        return $lista_campos;
    }
}
