<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use View;
use Datatables;

use App\Salud\Endodoncia;

class ProcedimientoCupsController extends ModeloController
{
    public function create()
    {
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );

        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {                
                case 'url_id_modelo':
                    $lista_campos[$i]['value'] = $this->modelo->id;
                    break;
                default:
                    # code...
                    break;
            }
        }

        $form_create = [
                            'url' => 'salud_diagnostico_cie',
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create','datos_columnas') )->render();
    }
    
    /*public function create()
    {
        return View::make('consultorio_medico.odontologia.endodoncia_linea_ingreso_registro')->render();
    }
    */
}   