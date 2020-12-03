<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Price;
use App\web\Priceitem;
use Illuminate\Support\Facades\Input;

use function GuzzleHttp\json_decode;

class PriceController extends Controller
{
    //Guarda un plan de precio como componente
    public function store(Request $request)
    {
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $item = new Price($request->all());
        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $item->fondo = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $item->title = strtoupper($request->title);
        $result = $item->save();
        if ($result) {
            $message = 'La sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue almacenada de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    //Actualiza un plan de precio como componente
    public function update(Request $request, $id)
    {
        $item = Price::find($id);
        $item->title = strtoupper($request->title);
        $tipo_fondo = $item->tipo_fondo;
        if ($request->tipo_fondo == '') {
            $item->tipo_fondo = $tipo_fondo;
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
                        $item->fondo = $filename;
                        $item->tipo_fondo = 'IMAGEN';
                        $item->repetir = $request->repetir;
                        $item->direccion = $request->direccion;
                    } else {
                        $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                        return redirect()->back()->withInput($request->input())
                            ->with('mensaje_error', $message);
                    }
                }
            } else {
                $item->fondo = $request->fondo;
                $item->tipo_fondo = "COLOR";
            }
        }
        $result = $item->save();
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
                'url' => 'seccion/' . $widget . '?id=' . Input::get('id'),
                'etiqueta' => 'Componente Price (Planes de Precios)'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Crear Plan'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $price = Price::where('widget_id', $widget)->first();
        return view('web.components.prices.create', compact('miga_pan', 'variables_url', 'price', 'widget'));
    }

    /* Guarda los item de un price
     * @param $request
     */
    public function guardar(Request $request)
    {
        $variables_url = $request->variables_url;
        $item = new Priceitem($request->all());
        //la lista de ítems
        if (isset($request->icono)) {
            $array = null;
            foreach ($request->icono as $key => $i) {
                $array[] = [
                    'icono' => $i,
                    'item' => $request->item[$key]
                ];
            }
            if ($array != null) {
                $item->lista_items = json_encode($array);
            } else {
                $item->lista_items = "null";
            }
        } else {
            $item->lista_items = "null";
        }
        //la imagen de cabecera
        if (isset($request->imagen_cabecera)) {
            //la imagen de la cabecera
            $file = $request->file('imagen_cabecera');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $item->imagen_cabecera = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        } else {
            $item->imagen_cabecera = "";
        }
        $result = $item->save();
        if ($request) {
            $message = 'El ítem fue almacenado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El ítem no fue almacenado de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Elimina un Priceitem
     * @param Priceitem_id $id
     */
    public function destroy($id)
    {
        $item = Priceitem::find($id);
        $widget = $item->price->widget_id;
        $result = $item->delete();
        if ($result) {
            $message = 'Plan eliminado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'El plan no fue eliminado.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Priceitem::find($id);
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
                'url' => 'seccion/' . $item->price->widget_id . '?id=' . Input::get('id'),
                'etiqueta' => 'Componente Price (Planes de Precios)'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Editar Plan'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $widget = $item->price->widget_id;
        $price = $item->price;
        $lista = null;
        if ($item->lista_items != 'null') {
            $lista = json_decode($item->lista_items);
        }
        return view('web.components.prices.edit', compact('miga_pan', 'lista', 'price', 'variables_url', 'item', 'widget'));
    }

    /*
     * Modifica un Item
     * @param $request Item $id
     *
     */
    public function modificar(Request $request, $id)
    {
        $item = Priceitem::find($id);
        $imagen = $item->imagen_cabecera;
        $item->precio = $request->precio;
        $item->button_color = $request->button_color;
        $item->button2_color = $request->button2_color;
        $item->text_color = $request->text_color;
        $item->url = $request->url;
        $item->background_color = $request->background_color;
        if (isset($request->imagen_cabecera)) {
            //el fondo es una imagen
            $file = $request->file('imagen_cabecera');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $item->imagen_cabecera = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        } else {
            $item->imagen_cabecera = $imagen;
        }
        //la lista de ítems
        if (isset($request->icono)) {
            $array = null;
            foreach ($request->icono as $key => $i) {
                $array[] = [
                    'icono' => $i,
                    'item' => $request->item[$key]
                ];
            }
            if ($array != null) {
                $item->lista_items = json_encode($array);
            } else {
                $item->lista_items = "null";
            }
        } else {
            $item->lista_items = "null";
        }
        $result = $item->save();
        if ($result) {
            $message = 'El plan fue modificado correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            $message = 'El plan no fue modificado de forma correcta.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    /*
     * Elimina toda la sección
     * @param Price $id
     */
    public function delete($id)
    {
        $price = Price::find($id);
        $widget = $price->widget_id;
        $result = $price->delete();
        if ($result) {
            $message = 'Sección eliminada con éxito';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La sección no fue eliminada.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $widget) . $variables_url)->with('flash_message', $message);
        }
    }
}
