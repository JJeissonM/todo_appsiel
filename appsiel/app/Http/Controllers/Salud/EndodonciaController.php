<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use View;
use Input;

use App\Sistema\Modelo;
use App\Salud\Endodoncia;

class EndodonciaController extends ModeloController
{
    public function create()
    {
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );

        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'modelo_entidad_id':
                    $lista_campos[$i]['value'] = $this->modelo->id;
                    break;
                case 'paciente_id':
                    $lista_campos[$i]['value'] = Input::get('paciente_id');
                    break;
                case 'consulta_id':
                    $lista_campos[$i]['value'] = Input::get('consulta_id');
                    break;
                default:
                    # code...
                    break;
            }
        }

        $form_create = [
                            'url' => 'salud_endodoncia',
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create','datos_columnas') )->render();
    }

    public function store(Request $request)
    {
        $modelo = Modelo::find( $request->modelo_entidad_id );
        
        $record_created = app( $modelo->name_space )->create( $request->all() );
        
        return response()->json( $record_created->get_fields_to_show() );
    }

    public function delete( $id )
    {
        Endodoncia::where('id',$id)->delete();
        return 1;
    }
}   