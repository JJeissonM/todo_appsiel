<?php

namespace App\Http\Controllers\web\services;


use Illuminate\Support\Facades\Input;

class SliderComponent implements IDrawComponent
{

    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
       return false;
    }

    function viewComponent()
    {
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
        $widget = $this->widget;
        $variables_url = '?id='.Input::get('id');
        return view('web.components.slider',compact('miga_pan','variables_url','widget'));
    }
}