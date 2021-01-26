<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Sticky;
use App\web\Stickyboton;
use Illuminate\Support\Facades\Input;

class StickyController extends Controller
{


    /*
    Almacena un módulo sticky en la base de datos
    */
    public function store(Request $request)
    {
        $s = new Sticky($request->all());
        $result = $s->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser almacenada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //Eliminar sticky
    public function delete($id)
    {
        $s = Sticky::find($id);
        $result = $s->delete();
        if ($result) {
            $message = 'La configuración de la sección fue eliminada correctamente.';
            return redirect(url('seccion/' . $s->widget_id) .  '?id=' . Input::get('id'))->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser eliminada, intente mas tarde.';
            return redirect(url('seccion/' . $s->widget_id) .  '?id=' . Input::get('id'))->with('flash_message', $message);
        }
    }

    //actualiza un sticky
    public function updated(Request $request, $id)
    {
        $s = Sticky::find($id);
        $s->posicion = $request->posicion;
        $s->ancho_boton = $request->ancho_boton;
        $s->configuracionfuente_id = $request->configuracionfuente_id;

        $result = $s->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue modificada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser modificada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
    Almacena botones para un sticky en la base de datos
    */
    public function storeboton(Request $request)
    {
        $s = new Stickyboton($request->all());
        if (isset($request->imagen)) {
            $file = $request->file("imagen");
            //$name = "Archivo_" . $date['year'] . $date['mon'] . $date['mday'] . $date['hours'] . $date['minutes'] . $date['seconds'] . "." . $f->getClientOriginalExtension();
            $name = str_slug($file->getClientOriginalName()) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = "docs/" . $name;
            $flag = file_put_contents($path, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $s->imagen = $name;
            }
        }
        $result = $s->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'El botón fue almacenado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El botón no pudo ser almacenado, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //Eliminar botón de sticky
    public function deleteboton($id)
    {
        $s = Stickyboton::find($id);
        $result = $s->delete();
        if ($result) {
            $message = 'El botón fue eliminado correctamente.';
            return redirect(url('seccion/' . $s->sticky->widget_id) .  '?id=' . Input::get('id'))->with('flash_message', $message);
        } else {
            $message = 'El botón no pudo ser eliminado, intente mas tarde.';
            return redirect(url('seccion/' . $s->sticky->widget_id) .  '?id=' . Input::get('id'))->with('flash_message', $message);
        }
    }
}
