<?php


namespace App\Http\Controllers\web\services;

use App\web\Icon;
use App\web\Sticky;
use Form;
use Illuminate\Support\Facades\Input;

class StickyComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $sticky = Sticky::where('widget_id', $this->widget)->first();
        return Form::sticky($sticky);
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
                'etiqueta' => 'Sticky'
            ]
        ];
        $widget = $this->widget;

        $variables_url = '?id=' . Input::get('id');
        $sticky = Sticky::where('widget_id', $widget)->first();
        $iconos = Icon::all();
        return view('web.components.sticky', compact('miga_pan', 'variables_url', 'widget', 'sticky','iconos'));
    }
}
