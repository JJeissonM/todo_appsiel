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
        }else{
            $testimonio->foto="img/lading-page/avatar.svg";
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
        $seccion = new Testimoniale();
        $seccion->titulo = $request->titulo;
        $seccion->descripcion = $request->descripcion;
        $seccion->widget_id = $request->widget_id;
        if (isset($request->imagen_fondo)) {
            $file = $request->imagen_fondo;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $seccion->fill(['imagen_fondo' => $filename]);
            }
        }else{
            $seccion->imagen_fondo="img/lading-page/map.png";
        }
        $result = $seccion->save();
        if($result){
            $message = 'La sección fue almacenada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }else{
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
        $testimonio->testimonio= $request->testimonio;
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
    public function modificar(Request $request, $id){
        $seccion = Testimoniale::find($id);
        $seccion->titulo = $request->titulo;
        $seccion->descripcion = $request->descripcion;
        if (isset($request->imagen_fondo)) {
            $file = $request->imagen_fondo;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $seccion->fill(['imagen_fondo' => $filename]);
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
    public function delete($id){
        $testimonio = Itemtestimonial::find($id);
        $widget= $testimonio->testimoniale->widget_id;
        $result=$testimonio->delete();
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
