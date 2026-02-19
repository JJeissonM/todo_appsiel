<?php

namespace App\Sistema\Services;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Input;
use DB;
use PDF;
use Auth;
use View;
use Illuminate\Support\Facades\Schema;
use Throwable;

use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Sistema\Services\FieldsList;

class RenderField
{
    protected $action;
    protected $field;

    public function __construct( Campo $field )
    {
        $this->field = $field;
        //$this->action = $action;
    }

    function show()
    {
        $value = $this->field->value;

        switch( $this->field->tipo )
        {
            case 'select':
                $value = $this->get_value_description();
                break;
            default:
                break;
        }

        return '{"label":"' . $this->field->descripcion . '","value":"' . htmlentities($value) . '"}';
    }

    public function get_value_description()
    {   
        $value = 'Valor no encontrado.';

        $texto_opciones = $this->field->opciones;
        $vec['']='';
        switch (substr($texto_opciones,0,strpos($texto_opciones, '_')))
        {
            case 'table':
                $tabla = substr($texto_opciones,6,strlen($texto_opciones)-1);

                if ( !Schema::hasTable($tabla) ) {
                    return $value;
                }

                $registro = DB::table($tabla)->where('id', $this->field->value)->get();
                break;

            case 'model':

                $model = substr($texto_opciones,6,strlen($texto_opciones)-1);

                try {
                    $registro = app($model)->where('id', $this->field->value)->get();
                } catch (Throwable $e) {
                    return $value;
                }

                if ( method_exists( $model, 'get_label_to_show') && count($registro) != 0 )
                {
                    return $registro[0]->get_label_to_show();
                }

                break;
            
            default:
                $vec = json_decode($texto_opciones,true);

                if ( isset( $vec[ $this->field->value ] ) )
                {
                    $value = $vec[ $this->field->value ];
                }
                
                break;
        }
        
        if ( isset($registro) ) 
        {
            if ( count($registro) != 0 )
            {
                $opcion = $registro[0];
                if (isset($opcion->descripcion))
                {
                    $value = $opcion->descripcion;
                }

                if (isset($opcion->name))
                {
                    $value = $opcion->name;
                }

                if (isset($opcion->nombres))
                {
                    $value = $opcion->apellido1.' '.$opcion->apellido2.' '.$opcion->nombres;
                }
            }
        }

        return $value;
    }

}
