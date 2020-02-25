<?php

namespace App\Http\Controllers\web;

use App\web\Icon;
use App\web\Servicio;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ServicioController extends Controller
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
                'etiqueta' => 'Servicios'
            ]
        ];
       // $servicios = Servicio::find($servicio_id);
        $iconos = Icon::all();
        $variables_url = '?id=' . Input::get('id');
        $servicios = Servicio::where('widget_id', $widget)->first();
        return view('web.components.servicios.create', compact('miga_pan', 'variables_url', 'servicios','iconos', 'widget'));

    }

    public function store(Request $request)
    {
        $servicios = new Servicio($request->all());
        $servicios->titulo = strtoupper($request->titulo);
        $servicios->descripcion = strtoupper($request->descripcion);
        $result = $servicios->save();
        if ($result) {
            $message = 'La sección fue almacenada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);

        } else {
            $message = 'La sección no fue almacenada de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    public function updated(Request $request, $id)
    {
        $servicio = Servicio::find($id);
        $servicio->titulo = strtoupper($request->titulo);
        $servicio->descripcion = strtoupper($request->descripcion);
        $result = $servicio->save();
        if ($result) {
            $message = 'La sección fue modificada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue modificada de forma correcta.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
