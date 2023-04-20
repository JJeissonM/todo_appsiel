<?php

namespace App\Sistema\Services;

use App\Sistema\Modelo;
use Illuminate\Support\Facades\View;

class AppModel
{
    public $modelo;

    public function __construct( $model_id )
    {
        $this->modelo = Modelo::find( $model_id );
    }

    public function get_encabezado_tabla()
    {
        $encabezado_tabla = app( $this->modelo->name_space )->encabezado_tabla;
        if( is_null($encabezado_tabla) )
        {
            $encabezado_tabla = [];
        }
        
        return $encabezado_tabla;
    }

    public function get_records_table($string_search)
    {
        $encabezado_tabla = app( $this->modelo->name_space )->encabezado_tabla;
        if( is_null($encabezado_tabla) )
        {
            $encabezado_tabla = [];
        }

        $registros = [];
        if ( method_exists( app( $this->modelo->name_space ), 'consultar_registros') )
        {
            $registros = app( $this->modelo->name_space )->consultar_registros( 10, $string_search);
        }

        $url_ver = '';

        return View::make('layouts.index_records_table', compact('encabezado_tabla','registros','url_ver') )->render();
    }

    public function get_records_filtered($array_wheres)
    {
        return app( $this->modelo->name_space )->where( $array_wheres )
                ->get();
    }
}
