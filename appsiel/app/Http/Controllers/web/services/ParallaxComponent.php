<?php


namespace App\Http\Controllers\web\services;

use App\web\Comparallax;
use Form;
use Illuminate\Support\Facades\Input;

class ParallaxComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $parallax = Comparallax::where('widget_id', $this->widget)->first();
        return Form::parallax($parallax);
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
                'etiqueta' => 'Parallax'
            ]
        ];
        $widget = $this->widget;

        $variables_url = '?id=' . Input::get('id');
        $parallax = Comparallax::where('widget_id', $widget)->first();
        return view('web.components.parallax', compact('miga_pan', 'variables_url', 'widget', 'parallax'));
    }
}
