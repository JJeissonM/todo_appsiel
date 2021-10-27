<?php

namespace App\Sistema\Services;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Input;
use DB;
use PDF;
use Auth;
use View;

use App\Sistema\Modelo;
use App\Sistema\Services\FieldsList;

class ShowField
{
    protected $fields_list;
    protected $model;
    protected $model_record;

    public function __construct( int $model_id, $model_record )
    {
        $this->model = Modelo::find( $model_id );
        $this->model_record = $model_record;
        $this->fields_list = $this->model->campos()->orderBy('orden')->get();
    }

    /*
        ** Esta función crea el array lista_campos que es el que se va a pasar a las vistas (create, edit, show) para visualizar los campos a través de VistaController según los tipos de campos y la vista.
        ** 
        $lista_campo = [ 'tipo', 'name', 'descripcion', 'opciones', 'value', 'atributos', 'definicion', 'html_clase', 'html_id', 'requerido', 'editable', 'unico' ];
        
        Por ahora solo se usa para la vista show
    */
    function assign_values_each_field_in_model_record()
    {
        // Se recorre la lista de campos 
        // para formatear-asignar el valor correspondiente del model_record del modelo 
        $quantity = count( $this->fields_list );

        for ($i = 0; $i < $quantity; $i++)
        {
            $field_name = $this->fields_list[$i]['name'];

            if( isset( $this->model_record->$field_name ) )
            {
                $this->fields_list[$i]['value'] = $this->model_record->$field_name;
            }


            if ($this->fields_list[$i]['tipo'] == 'imagen')
            {
                if ($this->model_record->$field_name == '' && $field_name == 'imagen') {
                    $campo_imagen = 'avatar.png';
                    $btn_quitar_img = '';
                } else {
                    $campo_imagen = $this->model_record->$field_name;
                    $btn_quitar_img = '<a type="button" class="close" href="' . url('quitar_imagen?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&model_record_id=' . $this->model_record->id) . '" title="Quitar imagen">&times;</a>';
                }
                $url = config('configuracion.url_instancia_cliente') . "/storage/app/" . $this->model->ruta_storage_imagen . $campo_imagen;
                $imagen = '<div class="form-group" style="border:1px solid gray; text-align:center; overflow:auto;" oncontextmenu="return false" onkeydown="return false">' . $btn_quitar_img . '<img alt="imagen.jpg" src="' . asset($url) . '" style="width: auto; height: 160px;" />
                        </div>';
                $this->fields_list[$i]['value'] = $imagen;
            }
        } // Cierre for cada campo

        return $this->fields_list;
    }

    // Construir un array con los campos asociados al modelo
    public function get_campos_modelo( $accion )
    {
        // Se obtienen los campos asociados a ese modelo
        $this->fields_list1 = $this->model->campos()->orderBy('orden')->get();

        $this->fields_list = $this->ajustar_valores_lista_campos($this->fields_list1->toArray());

        // Ajustar los valores según la acción
        $this->fields_list = $this->ajustar_valores_lista_campos_segun_accion($this->fields_list, $this->model_record, $this->model, $accion);

        return $this->fields_list;
    }

    public function ajustar_valores_lista_campos()
    {
        $cant = count($this->fields_list);
        for ($i = 0; $i < $cant; $i++) {
            $field_name = $this->fields_list[$i]['name'];

            // El campo Atributos se ingresa en  formato JSON {"campo1":"valor1","campo2":"valor2"}
            // Luego se tranforma a un array para que pueda ser aceptado por el Facade Form:: de LaravelCollective
            if ($this->fields_list[$i]['atributos'] != '') {

                $this->fields_list[$i]['atributos'] = json_decode($this->fields_list[$i]['atributos'], true);

                // Para el tipo de campo Input Lista Sugerencias
                if (isset($this->fields_list[$i]['atributos']['data-url_busqueda'])) {
                    $this->fields_list[$i]['atributos']['data-url_busqueda'] = url($this->fields_list[$i]['atributos']['data-url_busqueda']);
                }
            } else {
                $this->fields_list[$i]['atributos'] = [];
            }

            // Cuando el campo es requerido se agrega el atributo al control html
            if ($this->fields_list[$i]['requerido']) {
                $this->fields_list[$i]['atributos'] = array_merge($this->fields_list[$i]['atributos'], ['required' => 'required']);
            }

            // Cuando se está editando un model_record, el formulario llamado por LaravelCollective Form::model(), llena los campos que tienen valor null con los valores del model_record del modelo instanciado

            if ($this->fields_list[$i]['value'] == 'null') {
                $this->fields_list[$i]['value'] = null;
            }

            // Para llenar los campos tipo select y checkbox
            if ($this->fields_list[$i]['tipo'] == 'select' || $this->fields_list[$i]['tipo'] == 'bsCheckBox') {
                $this->fields_list[$i]['opciones'] = VistaController::get_opciones_campo_tipo_select($this->fields_list[$i]);
            }
        }
        return $this->fields_list;
    }



    public function ajustar_valores_lista_campos_segun_accion( $accion )
    {
        $cant = count($this->fields_list);
        for ($i = 0; $i < $cant; $i++) {
            $field_name = $this->fields_list[$i]['name'];

            if ($accion == 'create') {

                // Valores predeterminados para Algunos campos ocultos
                switch ($this->fields_list[$i]['name']) {
                    case 'creado_por':
                        $this->fields_list[$i]['value'] = Auth::user()->email;
                        break;
                    case 'modificado_por':
                        $this->fields_list[$i]['value'] = 0;
                        break;
                    case 'user_id':
                        $this->fields_list[$i]['value'] = Auth::user()->id;
                        break;
                    default:
                        # code...
                        break;
                }

                if ($this->fields_list[$i]['tipo'] == 'input_lista_sugerencias') {
                    // value es un array con los valores para text_input y para el input hidden
                    $this->fields_list[$i]['value'] = ['', ''];
                }
            } else { // Si se está editando

                // asignar valor almacenado en la BD al cada campo
                if (isset($this->model_record->$field_name)) {
                    $this->fields_list[$i]['value'] = $this->model_record->$field_name;
                }


                // Si el campo NO es editable, se muestra deshabilitado
                if (!$this->fields_list[$i]['editable']) {
                    /*
                        Advertencia cuando el campo está deshabilitado NO es enviado en el request del formulario
                        Su valor no es actualizado.
                        No se puede usar su valor (que no existe) en otras acciones.
                    */
                    $this->fields_list[$i]['atributos'] = ['disabled' => 'disabled', 'style' => 'background-color:#FBFBFB;'];

                    if ($this->fields_list[$i]['tipo'] == 'personalizado') {
                        $this->fields_list[$i]['value'] = '';
                    }
                } else {
                    if ($this->fields_list[$i]['tipo'] == 'input_lista_sugerencias') {
                        $campo_del_modelo = $this->fields_list[$i]['name'];
                        $this->model_record_input = app($this->fields_list[$i]['atributos']['data-clase_modelo'])->find($this->model_record->$campo_del_modelo);

                        // value es un array con los valores para text_input y para el input hidden
                        $this->fields_list[$i]['value'] = [$this->model_record_input->descripcion . ' (' . number_format($this->model_record_input->numero_identificacion, 0, ',', '.') . ')', $this->model_record->$campo_del_modelo];
                    }
                }

                switch ($this->fields_list[$i]['name']) {
                    case 'creado_por':
                        $this->fields_list[$i]['value'] = null;
                        break;

                    case 'modificado_por':
                        $this->fields_list[$i]['value'] = Auth::user()->email;
                        break;

                    case 'role':
                        $usuario = User::find($this->model_record->id);
                        $role = $usuario->roles()->get()[0];
                        $this->fields_list[$i]['value'] = $role->id;
                        break;

                    case 'escala_valoracion':
                        $logros = Logro::get_logros_periodo_curso_asignatura($this->model_record->periodo_id, $this->model_record->curso_id, $this->model_record->asignatura_id);
                        $descripciones = [];
                        $el_primero = true;
                        foreach ($logros as $un_logro) {
                            $descripciones[$un_logro->escala_valoracion_id] = $un_logro->descripcion;
                        }

                        $this->fields_list[$i]['value'] = $descripciones;
                        break;

                    default:
                        # code...
                        break;
                }

                // Si hay campo tipo imagen, se envía la URL de la imagen para mostrala
                if ($this->fields_list[$i]['tipo'] == 'imagen') {
                    $this->fields_list[$i]['value'] = config('configuracion.url_instancia_cliente') . "/storage/app/" . $this->model->ruta_storage_imagen . $this->model_record->$field_name;
                }

                // Si hay campo tipo imagenes_multiples, se envía la imagen para mostrala
                if ($this->fields_list[$i]['tipo'] == 'imagenes_multiples') {
                    // Esto debe cambiar!!!!!!
                    $this->fields_list[$i]['value'] = config('configuracion.url_instancia_cliente') . "/storage/app/" . $this->model->ruta_storage_imagen . $this->model_record->$field_name;
                }

                // Si se está editando un checkbox
                if ($this->fields_list[$i]['tipo'] == 'bsCheckBox')
                {
                    // Si el name del campo enviado tiene la palabra core_campo_id-ID, se trata de un campo Atributo de EAV
                    if (strpos($this->fields_list[$i]['name'], "core_campo_id-") !== false) {
                        $this->fields_list[$i]['value'] = ModeloEavValor::where(["modelo_padre_id" => Input::get('modelo_padre_id'), "model_record_modelo_padre_id" => Input::get('model_record_modelo_padre_id'), "modelo_entidad_id" => Input::get('modelo_entidad_id'), "core_campo_id" => $this->fields_list[$i]['id']])->value('valor');
                    } else {
                        $this->fields_list[$i]['value'] = $this->model_record->$field_name;
                    }
                }
            }
        }
        return $this->fields_list;
    }

}
