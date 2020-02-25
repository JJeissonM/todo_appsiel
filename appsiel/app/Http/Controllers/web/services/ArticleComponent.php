<?php


namespace App\Http\Controllers\web\services;

use App\web\Articlesetup;
use Form;
use Illuminate\Support\Facades\Input;

class ArticleComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $articlesetup = Articlesetup::where('widget_id', $this->widget)->first();
        return Form::articles($articlesetup);
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
                'etiqueta' => 'Artículos'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $articlesetup = Articlesetup::where('widget_id', $widget)->first();
        return view('web.components.articles', compact('miga_pan', 'variables_url', 'widget', 'articlesetup'));
    }
}
