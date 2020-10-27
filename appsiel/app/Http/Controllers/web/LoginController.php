<?php

namespace App\Http\Controllers\web;

use App\web\Login;
use App\web\Pagina;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class LoginController extends Controller
{
    public function create($widget){

        $miga_pan = [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id='.Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Slider'
            ]
        ];
        $login = Login::where('widget_id',$widget)->first();
        $paginas = Pagina::all();
        $variables_url = '?id='.Input::get('id');
        return view('web.components.login.login',compact('miga_pan','variables_url','widget','','paginas'));
    }

    public function store(Request $request)
    {
        $login = new Login($request->all());

        if ($request->hasFile('imagen')) {

            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();

            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                $login->fill(['imagen' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }

        }
        $result = $login->save();
        if ($result) {
            $message = 'Almacenado correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La información no fue almacenada correctamente, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }

    }
    
    public function updated(Request $request, $id){
        $login = Login::find($id);
        $img = $login->imagen;
        $login->fill($request->all());
        if ($request->hasFile('imagen')) {

            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();

            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);

            if ($flag !== false) {
                if ( file_exists( $filename ) )
                    { unlink($filename); }

                $login->fill(['imagen' => url($filename)]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        $result = $login->save();
        if ($result) {
            $message = 'Datos modificados correctamente.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La información no pudo se modificar  de forma correcta, intente mas tarde.';
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }
}
