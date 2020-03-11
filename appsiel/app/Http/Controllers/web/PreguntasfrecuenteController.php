<?php

namespace App\Http\Controllers\web;

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
        $preguntas = Preguntasfrecuentes::where('widget_id', $widget)->get();
        return view('web.components.preguntas.create', compact('miga_pan', 'variables_url', 'clientes', 'widget'));
    }

    public function store(Request $request)
    {
        $pregunta = new Preguntasfrecuentes($request->all());
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

    public function updated(Request $request)
    {
        $pregunta = Preguntasfrecuentes::find($request->pregunta_id);
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

    public function destroy($id)
    {
        $pregunta = Preguntasfrecuentes::find($id);
        $widget = $pregunta->widget_id;
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
        $result = $pregunta->delete();
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
