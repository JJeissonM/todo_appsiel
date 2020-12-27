<?php

namespace App\Http\Controllers\web;

use App\web\Itemtestimonial;
use App\web\Testimoniale;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class TestimonialController extends Controller
{
    //guardar itemtestimonial
    public function store(Request $request)
    {
        $testimonio = new Itemtestimonial($request->all());
        $testimonio->cargo = strtoupper($request->cargo);
        if (isset($request->foto)) {
            $file = $request->foto;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $testimonio->fill(['foto' => $filename]);
            }
        } else {
            $testimonio->foto = "img/lading-page/avatar.svg";
        }
        $result = $testimonio->save();
        if ($result) {
            $message = 'El testimonio fue almacenado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El testimonio no fue almacenado correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //guardar seccion testimonial
    public function guardar(Request $request)
    {
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $seccion = new Testimoniale($request->all());
        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/lading-page/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $seccion->fill(['fondo' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $seccion->save();
        if ($result) {
            $message = 'La sección fue almacenada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue almacenada correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //modificar itempregunta
    public function updated(Request $request)
    {
        $testimonio = Itemtestimonial::find($request->itemtestimonial_id);
        $testimonio->nombre = $request->nombre;
        $testimonio->cargo = strtoupper($request->cargo);
        $testimonio->testimonio = $request->testimonio;
        if (isset($request->foto)) {
            $file = $request->foto;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $testimonio->fill(['foto' => $filename]);
            }
        }
        $result = $testimonio->save();
        if ($result) {
            $message = 'Testimonio modificado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El testimonio no pudo ser modificado de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //modificar seccion testimoniales
    public function modificar(Request $request, $id)
    {
        $seccion = Testimoniale::find($id);
        $seccion->titulo = $request->titulo;
        $seccion->descripcion = $request->descripcion;
        $seccion->configuracionfuente_id = $request->configuracionfuente_id;
        $tipo_fondo = $seccion->tipo_fondo;
        if ($request->tipo_fondo == '') {
            $seccion->tipo_fondo = $tipo_fondo;
        }
        if ($request->tipo_fondo != '') {
            if ($request->tipo_fondo == 'IMAGEN') {
                if (isset($request->fondo)) {
                    //el fondo es una imagen
                    $file = $request->file('fondo');
                    $name = time() . $file->getClientOriginalName();
                    $filename = "img/lading-page/" . $name;
                    $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
                    if ($flag !== false) {
                        $seccion->fondo = $filename;
                        $seccion->tipo_fondo = 'IMAGEN';
                        $seccion->repetir = $request->repetir;
                        $seccion->direccion = $request->direccion;
                    } else {
                        $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                        return redirect()->back()->withInput($request->input())
                            ->with('mensaje_error', $message);
                    }
                }
            } else {
                $seccion->fondo = $request->fondo;
                $seccion->tipo_fondo = "COLOR";
            }
        }
        $result = $seccion->save();
        if ($result) {
            $message = 'Sección modificada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La Sección no pudo ser modificada de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //eliminar seccion testimoniales
    public function destroy($id)
    {
        $seccion = Testimoniale::find($id);
        $widget = $seccion->widget_id;
        $result = $seccion->delete();
        if ($result) {
            $message = 'Sección eliminada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La Sección no pudo ser eliminada de forma correcta, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    //eliminar itemtestimonial
    public function delete($id)
    {
        $testimonio = Itemtestimonial::find($id);
        $widget = $testimonio->testimoniale->widget_id;
        $result = $testimonio->delete();
        if ($result) {
            $message = 'Testimonio eliminado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El testimonio no pudo ser eliminado de forma correcta, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }
}
