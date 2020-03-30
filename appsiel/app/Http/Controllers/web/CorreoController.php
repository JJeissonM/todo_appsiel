<?php

namespace App\Http\Controllers\web;

use App\web\Correo;
use App\web\Itemcorreo;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CorreoController extends Controller
{
    /*modifica los itemcorreo de la tienda online
     *
     * @param Request
     * @return Response
     */
    public function modificaritem(Request $request)
    {
        $item = Itemcorreo::find($request->itemcorreo);
        $item->asunto = $request->asunto;
        $item->encabezado = $request->encabezado;
        $item->contenido = $request->contenido;
        if(isset($request->activo)){
            $item->activo = 'SI';
        }else{
            $item->activo = 'NO';
        }
        $result = $item->save();
        if ($result) {
            $message = "Correo <strong>" . $item->correo . "</strong> modificado correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "El correo <strong>" . $item->correo . "</strong> no pudo ser modificado.";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
     * modifica datos del correo
     * modifica el estado del los itemcorreos
     * @param Resques $request and correo_id
     * @return Response
     */
    public function updated(Request $request,$id){
        $correo = Correo::find($id);
        $correo->fill($request->all());
        $itemscorreos = $correo->itemcorreos;
        if(count($itemscorreos)>0){
            foreach ($itemscorreos as $item){
                if(count($request->activos)>0){
                    if(array_key_exists($item->id,$request->activos)){
                        $item->activo = 'SI';
                    }else{
                        $item->activo='NO';
                    }
                }else{
                    $item->activo = 'NO';
                }
                $item->save();
            }
        }
        $result=$correo->save();
        if($result){
            $message = "Las configuraciones generales del correo se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "Las configuraciones generales del correo no se pudieron modificar.";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
