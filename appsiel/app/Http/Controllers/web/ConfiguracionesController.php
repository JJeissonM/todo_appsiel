<?php

namespace App\Http\Controllers\web;

use App\Core\Configuracion;
use App\web\Configuraciones;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConfiguracionesController extends Controller
{

    public function index(){
    }

    public function store(Request $request){

         $conf = new Configuraciones($request->all());
         $flag = $conf->save();

        if($flag){
            $message = 'Configuración almacenada correctamente';
            return redirect()->back()
                ->with('flash_message',$message);
        }else {
            $message = 'Error inesperado, por favor intente nuevamente mas tarde';
            return redirect()->back()
                ->with('mensaje_error',$message);
        }

    }

    public function update(Request $request, $id){

        $conf = Configuraciones::find($id);
        $conf->fill($request->all());
        $flag = $conf->save();

        if($flag){
            $message = 'Configuración almacenada correctamente';
            return redirect()->back()
                ->with('flash_message',$message);
        }else {
            $message = 'Error inesperado, por favor intente nuevamente mas tarde';
            return redirect()->back()
                ->with('mensaje_error',$message);
        }

    }

}
