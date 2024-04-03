<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

use App\Sistema\Modelo;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class DireccionEntregaController extends ModeloController
{

    public function create()
    {
        $cliente_id = Input::get('cliente_id');
        
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );

        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'cliente_id':
                    $lista_campos[$i]['value'] = $cliente_id;
                    break;
                
                case 'url_id_modelo':
                    $lista_campos[$i]['value'] = $this->modelo->id;
                    break;
                default:
                    # code...
                    break;
            }
        }

        $form_create = [
                            'url' => 'vtas_direcciones_entrega',
                            'campos' => $lista_campos
                        ];

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create') )->render();
    }

    public function store(Request $request)
    {
        $registro = $this->crear_nuevo_registro($request);

        $registro->actualizar_por_defecto( $request->por_defecto );

        return redirect( 'ecommerce/public/account/nav-directorio-tab' )->with( 'domi_message','Domicilio CREADO correctamente.' );
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $registro = app($this->modelo->name_space)->find($id);

        $lista_campos = $this->get_campos_modelo($this->modelo, $registro, 'edit');

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
                            'url' => 'vtas_direcciones_entrega/'.$registro->id,
                            'campos' => $lista_campos
                        ];

        return View::make( 'layouts.modelo_form_edit_sin_botones', compact('form_create','registro') )->render();
    }

    public function update(Request $request, $id)
    {
        $modelo = Modelo::find($request->url_id_modelo);
        
        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $registro->fill( $request->all() );
        $registro->save();

        $registro->actualizar_por_defecto( $request->por_defecto );

        return redirect( 'ecommerce/public/account/nav-directorio-tab' )->with( 'domi_message','Domicilio MODIFICADO correctamente.' );
    }

    public function destroy(Request $request, $id)
    {
        $modelo = Modelo::find($request->url_id_modelo);
        
        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $registro->delete();

        return redirect( 'ecommerce/public/account/nav-directorio-tab' )->with( 'domi_message','Domicilio ELIMINADO correctamente.' );
    }
}