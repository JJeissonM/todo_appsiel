<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\web\Comparallax;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ParallaxController extends Controller
{
    /*
    Almacena un módulo parallax en la base de datos
    */
    public function store(Request $request)
    {
        $p = new Comparallax($request->all());
        if ($request->modo == 'IMAGEN') {
            //imagen
            if (isset($request->fondo)) {
                $file = $request->file("fondo");
                $name = str_slug($file->getClientOriginalName()) . '-' . time() . '.' . $file->clientExtension();
                $path = "img/parallax/" . $name;
                $flag = file_put_contents($path, file_get_contents($file->getRealPath()), LOCK_EX);
                if ($flag) {
                    $p->fondo = $name;
                }
            }
        }
        if ($request->modo == 'COLOR') {
            $p->fondo = $request->fondo;
        }
        $result = $p->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser almacenada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }


    //Eliminar parallax
    public function delete($id)
    {
        $p = Comparallax::find($id);
        $result = $p->delete();
        if ($result) {
            $message = 'La configuración de la sección fue eliminada correctamente.';
            return redirect(url('seccion/' . $p->widget_id) .  '?id=' . Input::get('id'))->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser eliminada, intente mas tarde.';
            return redirect(url('seccion/' . $p->widget_id) .  '?id=' . Input::get('id'))->with('flash_message', $message);
        }
    }

    //actualiza un paralax
    public function updated(Request $request, $id)
    {
        $p = Comparallax::find($id);
        $p->titulo = $request->titulo;
        $p->descripcion = $request->descripcion;
        $p->modo = $request->modo;
        $p->content_html = $request->content_html;
        $p->textcolor=$request->textcolor;
        if ($request->modo == 'IMAGEN') {
            //imagen
            if (isset($request->fondo)) {
                $file = $request->file("fondo");
                $name = str_slug($file->getClientOriginalName()) . '-' . time() . '.' . $file->clientExtension();
                $path = "img/parallax/" . $name;
                $flag = file_put_contents($path, file_get_contents($file->getRealPath()), LOCK_EX);
                if ($flag) {
                    $p->fondo = $name;
                }
            }
        }
        if ($request->modo == 'COLOR') {
            $p->fondo = $request->fondo;
        }
        $result = $p->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue modificada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser modificada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
