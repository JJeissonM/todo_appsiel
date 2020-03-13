<?php

namespace App\Http\Controllers\web;

use App\web\Itempregunta;
use App\web\Preguntasfrecuentes;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class PreguntasfrecuenteController extends Controller
{
    public function create($widget)
    {
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id=' . Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Clientes'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $pregunta = Preguntasfrecuentes::where('widget_id', $widget)->first();
        return view('web.components.preguntas.create', compact('miga_pan', 'variables_url', 'pregunta', 'widget'));
    }

    //guardar itempregunta
    public function store(Request $request)
    {
        $pregunta = new Itempregunta($request->all());
        $result = $pregunta->save();
        if ($result) {
            $message = 'La pregunta fue almacenada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La pregunta no fue almacenada correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }


    //guardar seccion pregunta
    public function guardar(Request $request)
    {
        $seccion = new Preguntasfrecuentes();
        $seccion->titulo = $request->titulo;
        $seccion->descripcion = $request->descripcion;
        $seccion->widget_id = $request->widget_id;
        if (isset($request->imagen)) {
            $file = $request->imagen_fondo;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $seccion->fill(['imagen_fondo' => $filename]);
            }
        }else{
            $seccion->imagen_fondo="img/lading-page/faq-img-1.png";
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
        $pregunta = Itempregunta::find($request->itempregunta_id);
        $pregunta->pregunta = $request->pregunta;
        $pregunta->respuesta = $request->respuesta;
        $result = $pregunta->save();
        if ($result) {
            $message = 'Pregunta modificada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La Pregunta no pudo ser modificada de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    //modificar seccion pregunta
    public function modificar(Request $request){
        $seccion = Preguntasfrecuentes::find($request->pregunta_id);
        $seccion->titulo = $request->titulo;
        $seccion->descripcion = $request->descripcion;
        if (isset($request->imagen)) {
            $file = $request->imagen_fondo;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page" . $name;
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

    //eliminar seccion pregunta
    public function destroy($id)
    {
        $seccion = Preguntasfrecuentes::find($id);
        $widget = $seccion->widget_id;
        $result = $seccion->delete();
        if ($result) {
            $message = 'Pregunta eliminada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La pregunta no pudo ser eliminada de forma correcta, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    //eliminar itempregunta
    public function delete($id){
        $pregunta = Itempregunta::find($id);
        $widget= $pregunta->preguntasfercuente->widget_id;
        $result=$pregunta->delete();
        if ($result) {
            $message = 'Pregunta eliminada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La pregunta no pudo ser eliminada de forma correcta, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

}
