<?php

namespace App\Http\Controllers\web;

use App\web\ItemSlider;
use App\web\Pagina;
use App\web\Slider;
use App\web\Widget;
use Illuminate\Http\Request;
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
        foreach ($item->attributesToArray() as $key => $value)
        {
            if( $key == 'imagen' )
            {
                $item->$key = $value;
            }else{
              $item->$key = strtoupper($value);
            
            }
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

    public function  edit($id){

        $item = ItemSlider::find($id);
        $widget =  $item->slider->widget->id;
        if($item){

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
                    'etiqueta' => 'Item slider'
                ]
            ];

            $paginas = Pagina::all();
            $variables_url = '?id='.Input::get('id');
            return view('web.components.slider.edit',compact('miga_pan','variables_url','widget','paginas','item'));

        }else {

            return redirect()->back()
                ->with('mensaje_error',"El item selecionado no existe en nuestros registros, por favor intente nuevamente.");
        }

    }

    public function update(Request $request, $id)
    {
        $pagina = Pagina::find($request->pagina);

        $item  = ItemSlider::find($id);

        if($item){
            
            $old_image = '';
            if ( $pagina->imagen != '')
            {
                $old_image = $pagina->imagen;
            }

            $item->fill($request->all());
            $item->imagen = $old_image;
            foreach ($item->attributesToArray() as $key => $value){
                $item->$key = strtoupper($value);
            }

            if($request->tipo_enlace == 'pagina' ){
                if($request->seccion == 'principio' )
                {                    
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
                if ($flag !== false)
                {
                    if ( $old_image != '' )
                    {
                        unlink($old_image);
                    }
                    
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

    public function destroyItem($id){

        $item = ItemSlider::find($id);

        if($item==null){
           return redirect()->back()
               ->with('mensaje_error',"El item a eliminar no se encuentra en nuestros registros.");
        }

        $flag = $item->delete();

        if($flag){
            unlink($item->imagen);
            return redirect()->back()
                ->with('flash_message',"Item eliminado correctamente");
        }else {
            return redirect()->back()
                ->with('mensaje_error',"Error inesperado, por favor intente más tarde.");
        }

    }


}
