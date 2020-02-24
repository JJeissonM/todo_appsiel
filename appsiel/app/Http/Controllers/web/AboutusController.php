<?php

namespace App\Http\Controllers\web;

use App\web\Aboutus;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

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
                'url' => 'NO',
                'etiqueta' => 'About Us'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $aboutus = Aboutus::where('widget_id', $widget)->first();
        return view('web.components.about_us.create', compact('miga_pan', 'variables_url', 'aboutus', 'widget'));
    }

    public function store(Request $request)
    {
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
        $result = $aboutus->save();
        if ($result) {
            $message = 'About us almacenado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }else{
            $message = 'About us no fue almacenado correctamente, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
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
        $result = $aboutus->save();
        if($result){
            $message = 'About us modificado correctamente.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }else{
            $message = 'About us no pudo se modificado de forma correcta, intente mas tarde.';
            $variables_url = '?id=' . Input::get('id');
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }

    }
}
