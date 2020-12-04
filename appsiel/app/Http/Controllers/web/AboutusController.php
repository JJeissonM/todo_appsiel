<?php

namespace App\Http\Controllers\web;

use App\web\Aboutus;
use App\web\Icon;
use App\web\Navegacion;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use App\web\RedesSociales;
use App\web\Footer;
use App\web\Pagina;

use App\Http\Controllers\web\services\NavegacionComponent;

class AboutusController extends Controller
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
                'url' => 'seccion/' . $widget . '?id=' . Input::get('id'),
                'etiqueta' => 'Quiénes Somos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Definir Información'
            ]
        ];
        $iconos = Icon::all();
        $variables_url = '?id=' . Input::get('id');
        $aboutus = Aboutus::where('widget_id', $widget)->first();
        return view('web.components.about_us.create', compact('miga_pan', 'variables_url', 'aboutus', 'iconos', 'widget'));
    }

    public function store(Request $request)
    {
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $aboutus = new Aboutus($request->all());
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $aboutus->fill(['imagen' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $aboutus->fondo = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $aboutus->save();
        if ($result) {
            $message = 'About us almacenado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'About us no fue almacenado correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }

    }

    public function updated(Request $request, $id)
    {
        $aboutus = Aboutus::find($id);
        $img = $aboutus->imagen;
        $aboutus->fill($request->all());
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                // $bool = unlink($img);
                $aboutus->fill(['imagen' => url($filename)]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $tipo_fondo = $aboutus->tipo_fondo;
        if ($request->tipo_fondo == '') {
            $aboutus->tipo_fondo = $tipo_fondo;
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
                        $aboutus->fondo = $filename;
                        $aboutus->tipo_fondo = 'IMAGEN';
                        $aboutus->repetir = $request->repetir;
                        $aboutus->direccion = $request->direccion;
                    } else {
                        $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                        return redirect()->back()->withInput($request->input())
                            ->with('mensaje_error', $message);
                    }
                }
            } else {
                $aboutus->fondo = $request->fondo;
                $aboutus->tipo_fondo = "COLOR";
            }
        }
        $result = $aboutus->save();
        if ($result) {
            $message = 'About us modificado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'About us no pudo se modificado de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }

    }

    //Leer institucional
    public function leer_institucional($id)
    {
        return view('web.components.about_us_leer_mas')->with( 'empresa', Aboutus::find($id) );
    }
}
