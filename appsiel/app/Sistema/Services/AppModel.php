<?php

namespace App\Sistema\Services;

use Input;
use DB;
use PDF;
use Auth;
use View;

use App\Sistema\Modelo;

class AppModel
{
    public $modelo;

    public function __construct( $model_id )
    {
        $this->modelo = Modelo::find( $model_id );
    }

    public function get_records_table()
    {
        $encabezado_tabla = app( $this->modelo->name_space )->encabezado_tabla;
        if( is_null($encabezado_tabla) )
        {
            $encabezado_tabla = [];
        }

        $registros = [];
        if ( method_exists( app( $this->modelo->name_space ), 'consultar_registros') )
        {
            $registros = app( $this->modelo->name_space )->consultar_registros( 10, '');
        }

        $url_ver = '';

        return View::make('layouts.index_records_table', compact('encabezado_tabla','registros','url_ver') )->render();
    }
}
