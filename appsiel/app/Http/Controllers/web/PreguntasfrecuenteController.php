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
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $seccion = new Preguntasfrecuentes($request->all());
        if (isset($request->imagen_fondo)) {
            $file = $request->imagen_fondo;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $seccion->fill(['imagen_fondo' => $filename]);
            }
        } else {
            $seccion->imagen_fondo = "img/lading-page/faq-img-1.png";
        }
        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $seccion->fondo = $filename;
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
    public function modificar(Request $request, $id)
    {
        $seccion = Preguntasfrecuentes::find($id);
        $seccion->titulo = $request->titulo;
        $seccion->descripcion = $request->descripcion;
        $seccion->configuracionfuente_id = $request->configuracionfuente_id;
        $seccion->color1 = $request->color1;
        $seccion->color2 = $request->color2;
        if (isset($request->imagen)) {
            $file = $request->imagen_fondo;
            $name = time() . str_slug($file->getClientOriginalName());
            $filename = "img/lading-page" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $seccion->fill(['imagen_fondo' => $filename]);
            }
        }
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
                    $filename = "img/" . $name;
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

    //eliminar seccion pregunta
    public function destroy($id)
    {
        $seccion = Preguntasfrecuentes::find($id);
        $widget = $seccion->widget_id;
        $result = $seccion->delete();
        if ($result) {
            $message = 'Sección eliminada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La pregunta no pudo ser eliminada de forma correcta, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    //eliminar itempregunta
    public function delete($id)
    {
        $pregunta = Itempregunta::find($id);
        $pr = Preguntasfrecuentes::find($pregunta->pregunta_id);
        $widget = $pr->widget_id;
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
