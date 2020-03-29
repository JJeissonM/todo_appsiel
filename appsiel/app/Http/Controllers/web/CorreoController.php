<?php

namespace App\Http\Controllers\web;

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
        $result = $item->save();
        if ($result) {
            $message = "Correo<strong>" . $item->correo . "</strong> modificado correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "El correo <strong>" . $item->correo . "</strong> no pudo ser modificado.";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
