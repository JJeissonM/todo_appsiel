<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\web\services\FactoryCompents;
use App\web\Widget;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SeccionController extends Controller
{
    public function orquestador($id){

        $widget = Widget::find($id);


        if($widget){
            $factory = new FactoryCompents($widget->seccion->nombre,$widget->id);

            $componente = $factory();
            if(!$componente){
                return redirect()->back()->with('flash_message','el componente selecciónado no se encuentra registrado');
            }
            return $componente->viewComponent();
        }else{
            return redirect()->back();
        }

    }


}
