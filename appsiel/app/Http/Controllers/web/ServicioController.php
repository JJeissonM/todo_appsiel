<?php

namespace App\Http\Controllers\web;

use App\web\Icon;
use App\web\Itemservicio;
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
        $iconos = Icon::all();
        $variables_url = '?id=' . Input::get('id');
        $servicios = Servicio::where('widget_id', $widget)->first();
        return view('web.components.servicios.create', compact('miga_pan', 'variables_url', 'servicios', 'iconos', 'widget'));

    }

    /* Guarda un servicio
     *  @param $request
     */

    public function store(Request $request)
    {
        $servicios = new Servicio($request->all());
        $servicios->titulo = strtoupper($request->titulo);
        $servicios->descripcion = $request->descripcion;
        $result = $servicios->save();
        if ($result) {
            $message = 'La sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue almacenada de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    /* Modifica un servicio
     *
     */
    public function updated(Request $request, $id)
    {
        $servicio = Servicio::find($id);
        $servicio->titulo = strtoupper($request->titulo);
        $servicio->descripcion = $request->descripcion;
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

    /* Guarda los itemservicios de un servicio
     * @param $request
     */
    public function guardar(Request $request)
    {
        $variables_url = $request->variables_url;
        $item = new Itemservicio();
        $item->titulo = strtoupper($request->titulo);
        $item->descripcion = $request->descripcion;
        $item->icono = $request->icono;
        $item->servicio_id = $request->servicio_id;
        $result = $item->save();
        if ($request) {
            $message = 'La sección fue almacenada correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);

        } else {
            $message = 'La sección no fue almacenada de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);

        }
    }

    /* vista para edita un itemservicio
     * @param recibe un $id Itemservicio
     * @return view
     */
    public function edit($itemservicio_id)
    {
        $item = Itemservicio::find($itemservicio_id);
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
                'url' => 'seccion/' . $item->servicio->widget_id . '?id=' . Input::get('id'),
                'etiqueta' => 'Servicios'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Editar Servicio'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $widget = $item->servicio->widget_id;
        $iconos = Icon::all();
        return view('web.components.servicios.edit', compact('miga_pan', 'variables_url', 'item', 'iconos', 'widget'));
    }

    /*
     * Modifica un Itemservicio
     * @param $request Itemservicio $id
     *
     */
    public function modificar(Request $request, $id)
    {
        $item = Itemservicio::find($id);
        $item->titulo = strtoupper($request->titulo);
        $item->descripcion = $request->descripcion;
        $item->icono = $request->icono;
        $result = $item->save();
        if ($result) {
            $message = 'El servicio fue modificado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'El servicio no fue modificado de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Elimina un itemservicio
     * @param Itemservicio $id
     */
    public function destroy($id){
        $item = Itemservicio::find($id);
        $widget = $item->servicio->widget_id;
        $result = $item->delete();
        if($result){
            $message = 'El Servicio fue eliminado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }else{
            $message = 'El Servicio no fue eliminado de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Elimina toda los servicios
     * @param Servicio $id
     */
    public function delete($id){
        $servicio = Servicio::find($id);
        $widget = $servicio->widget_id;
        $result = $servicio->delete();
        if($result){
            $message = 'Los servicios fueron eliminados de correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }else{
            $message = 'Los servicios no fueron eliminados de forma correcta.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }
}
