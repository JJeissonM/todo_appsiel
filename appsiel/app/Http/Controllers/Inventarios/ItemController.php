<?php

namespace App\Http\Controllers\Inventarios;

use App\Http\Controllers\Sistema\ModeloController;

use App\Inventarios\InvFichaProducto;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\ListaPrecioDetalle;
use App\web\Footer;
use App\web\RedesSociales;
use Illuminate\Http\Request;
use Input;

use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Sistema\Aplicacion;

use App\Http\Controllers\Controller;
use App\Inventarios\InvProducto;
use App\Sistema\Html\MigaPan;

class ItemController extends  ModeloController {

    public function show($id)
    {
        $modelo = Modelo::find( Input::get('id_modelo') );
        
        $registro = InvProducto::find( $id );
        $reg_anterior = app($modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($modelo->name_space)->where('id', '>', $registro->id)->min('id');

        // Se obtienen los campos asociados a ese modelo
        $lista_campos = $modelo->campos()->orderBy('orden')->get()->toArray();

        // Formatear-asignar el valor correspondiente del registro del modelo
        
        // 1ro. Para los campos del modelo
        $lista_campos = Campo::asignar_valores_registro( $lista_campos, $registro );

        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        $acciones = $this->acciones_basicas_modelo( $modelo, $variables_url );

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;

        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo, $registro->descripcion );

        $tabla = '';

        return view( 'inventarios.items.show', compact('form_create','miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente','tabla') );
    }

}