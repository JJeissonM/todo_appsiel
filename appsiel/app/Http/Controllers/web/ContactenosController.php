<?php

namespace App\Http\Controllers\web;

use App\web\Contactenos;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ContactenosController extends Controller
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
                'etiqueta' => 'Contactenos'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $contactenos = Contactenos::where('widget_id', $widget)->first();
        return view('web.components.contactenos.create', compact('miga_pan', 'variables_url', 'contactenos', 'widget'));
    }

    public function store(Request $request){
        $contactenos = new Contactenos($request->all());
        $result=$contactenos->save();
        if($result){
            $message = 'El formulario fue creado de forma exitosa.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }else{
            $message = 'El formulario fue creado de forma exitosa.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }
}
