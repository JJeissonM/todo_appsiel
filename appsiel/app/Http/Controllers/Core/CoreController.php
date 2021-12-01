<?php

namespace App\Http\Controllers\Core;

use SoapClient;
USE SoapHeader;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Core\TransaccionController;

use Input;
use DB;
use Cache;
use View;

use App\Sistema\Modelo;

class CoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
        NOTA: El $id_modelo no corresponda al modelo padre para el cúal se quieran traer los hijo. Por tanto debe existir el método get_registros_select_hijo() en el modelo del formulario y devolver los registros para select hijo

        Ejemplo, El modelo EncabezadoCalificacion tiene en su formulario los campos Curso (select_dependientes_padre) y Asignatura (select_dependientes_hijo); cuando se llama este método (select_dependientes) desde el formulario create o edit se van a buscar las asignaturas en el modelo EncabezadoCalificacion a través del método get_registros_select_hijo() y la variable $id_modelo.

    */
    public function select_dependientes( $id_modelo, $id_select_padre)
    {        
        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($id_modelo);
        
        $opciones = app($modelo->name_space)->get_registros_select_hijo($id_select_padre);   

        return $opciones;
    }

}