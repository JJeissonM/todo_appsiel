<?php

namespace App\Http\Controllers\web;

use App\web\ItemSlider;
use App\web\Pagina;
use App\web\Slider;
use App\web\Widget;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class SliderController extends Controller
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

        $paginas = Pagina::all();
        $variables_url = '?id='.Input::get('id');
        return view('web.components.slider.create',compact('miga_pan','variables_url','widget','paginas'));
    }

    public function store(Request $request)
    {

        $slider = Slider::where('widget_id', $request->widget_id)->first();

        if ($slider == null) {
            $slider = new Slider($request->all());
            $slider->save();
        }

        $item = new ItemSlider($request->all());
        foreach ($item->attributesToArray() as $key => $value){
              $item->$key = strtoupper($value);
        }

        $item->slider_id = $slider->id;

        if($request->tipo_enlace == 'pagina' ){
            if($request->seccion == 'principio' ){
                $pagina = Pagina::find($request->pagina);
                $item->enlace = url('/'.$pagina->slug);
            }else {
                $widget = Widget::find($request->seccion);
                $item->enlace = url('/'. $widget->pagina->slug.'#'.$widget->seccion->nombre);
            }
        }else {
            $item->enlace =  $request->url;
        }

        if ($request->hasFile('imagen')) {

            $file = $request->file('imagen');
            $name = time() . $file->getClientOriginalName();

            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $item->fill(['imagen' => $filename]);
            } else {
                $message = 'Error inesperado al intentar guardar la imagen, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }

        $flag = $item->save();

        if($flag){
            $message = 'item almacenado correctamente';
            return redirect(url('seccion/'.$request->widget_id).$request->variables_url)->with('flash_message',$message);
        }else {
            $message = 'Error inesperado, por favor intente nuevamente más tarde';
            return redirect()->back()
                ->withInput($request->input())
                ->with('mensaje_error',$message);
        }


    }

}
