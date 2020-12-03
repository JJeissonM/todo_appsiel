<?php

namespace App\Http\Controllers\web;

use App\web\Cliente;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Clienteitem;
use Illuminate\Support\Facades\Input;

class ClienteController extends Controller
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
        $clientes = Cliente::where('widget_id', $widget)->get();
        return view('web.components.clientes.create', compact('miga_pan', 'variables_url', 'clientes', 'widget'));
    }

    public function store(Request $request)
    {
        $cliente = new Clienteitem($request->all());
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $cliente->fill(['logo' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $cliente->save();
        if ($result) {
            $message = 'Cliente almacenado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'Cliente no fue almacenado correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }


    //guarda una seccion

    public function storeSeccion(Request $request)
    {
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $cliente = new Cliente($request->all());
        $cliente->title = strtoupper($request->title);
        $cliente->descripcion = strtoupper($request->descripcion);
        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $cliente->fondo = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $cliente->save();
        if ($result) {
            $message = 'Sección almacenada correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue almacenada correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    public function updated(Request $request)
    {
        $cliente = Clienteitem::find($request->cliente_id);
        $img = $cliente->logo;
        $cliente->nombre = $request->nombre;
        $cliente->enlace = $request->enlace;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                if (file_exists($img)) {
                    unlink($img);
                }
                $cliente->fill(['logo' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $cliente->save();
        if ($result) {
            $message = 'Cliente modificado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'Cliente no pudo se modificado de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    public function destroy($id)
    {
        $item = Clienteitem::find($id);
        $widget = $item->cliente->widget_id;
        $img = $item->logo;
        $result = $item->delete();
        if ($result) {
            if (file_exists($img)) {
                unlink($img);
            }
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', 'Cliente eliminado con exito');
        } else {
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', 'El cliente no pudo ser eliminado');
        }
    }

    /*
     * Elimina toda la seccion
     * @param Cliente $id
     */
    public function destroySeccion($id)
    {
        $cliente = Cliente::find($id);
        $widget = $cliente->widget_id;
        if ($cliente->delete()) {
            $message = 'Sección eliminada de correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue eliminada.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    /* Modifica un seccion cliente
     *
     */
    public function updatedSection(Request $request, $id)
    {
        $cliente = Cliente::find($id);
        $cliente->title = strtoupper($request->title);
        $cliente->descripcion = $request->descripcion;
        $tipo_fondo = $cliente->tipo_fondo;
        if ($request->tipo_fondo == '') {
            $cliente->tipo_fondo = $tipo_fondo;
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
                        $cliente->fondo = $filename;
                        $cliente->tipo_fondo = 'IMAGEN';
                        $cliente->repetir = $request->repetir;
                        $cliente->direccion = $request->direccion;
                    } else {
                        $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                        return redirect()->back()->withInput($request->input())
                            ->with('mensaje_error', $message);
                    }
                }
            } else {
                $cliente->fondo = $request->fondo;
                $cliente->tipo_fondo = "COLOR";
            }
        }
        $result = $cliente->save();
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
