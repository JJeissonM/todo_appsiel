<?php

namespace App\Http\Controllers\web\services;


use App\web\ItemSlider;
use App\web\Slider;
use App\web\Widget;
use Illuminate\Support\Facades\Input;
use Form;


class SliderComponent implements IDrawComponent
{

    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $widget = Widget::find($this->widget);
        $slider = Slider::where('widget_id', $widget->id)->first();

        if ($slider != null) {
            if ($slider->disposicion == 'DEFAULT')
            {
                return Form::slider($slider);
            }else {
                return Form::sliderpremiun($slider);
            }
        }
    }

    function viewComponent()
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
                'etiqueta' => 'Slider'
            ]
        ];

        $widget = $this->widget;
        $slider = Slider::where('widget_id', $widget)->first();
        $variables_url = '?id=' . Input::get('id');
        return view('web.components.slider', compact('miga_pan', 'variables_url', 'widget', 'slider'));
    }
}