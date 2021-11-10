<?php

namespace App\Sistema\Services;

use Input;
use DB;
use PDF;
use Auth;
use View;

use App\Sistema\Modelo;

class FieldsList
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
        $quantity = count( $this->fields_list );

        for ($i = 0; $i < $quantity; $i++)
        {
            $field_name = $this->fields_list[$i]['name'];

            if( isset( $this->model_record->$field_name ) )
            {
                $this->fields_list[$i]['value'] = $this->model_record->$field_name;
            }
        }
    }

    public function get_list_to_show()
    {
        $this->assign_values_each_field_in_model_record();
        $fields_list_valued = $this->fields_list;
        
        $the_first = true;
        $n = 0;
        $json_fields_list = '{';
        $json_fields_list .= '"id":' .  $this->model_record->id;
        foreach ( $fields_list_valued as $field )
        {
            $obj_render_field = new RenderField( $field );
            
            $json_fields_list .= ',"' . $field->name . '":' .  $obj_render_field->show();
        }
        
        $json_fields_list .= '}';

        return json_decode( $json_fields_list );
    }
}
