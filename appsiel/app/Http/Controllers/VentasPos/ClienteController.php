<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

use App\Sistema\Modelo;
use App\Ventas\Cliente;
use App\Ventas\Services\CustomerServices;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ClienteController extends ModeloController
{
    public function create()
    {
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );

        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'inv_bodega_id':
                    $lista_campos[$i]['value'] = config('ventas.inv_bodega_id');
                    break;

                case 'numero_identificacion':
                    $lista_campos[$i]['name'] = 'numero_identificacion_aux';
                    break;
                    
                default:
                    # code...
                    break;
            }
        }

        $form_create = [
                            'url' => 'pos_clientes',
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create','datos_columnas') )->render();
    }

    public function store(Request $request)
    {
        $datos = $request->all();
        $datos['numero_identificacion'] = $request->numero_identificacion_aux;

        $Cliente = (new CustomerServices())->store_new_customer($datos);
        
        return response()->json( $Cliente );
    }

    public function delete( $id )
    {
        Cliente::where('id',$id)->delete();
        return 1;
    }
}   