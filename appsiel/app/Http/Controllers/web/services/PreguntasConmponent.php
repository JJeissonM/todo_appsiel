<?php


namespace App\Http\Controllers\web\services;


use App\web\Preguntasfrecuentes;
use Illuminate\Support\Facades\Input;
use Form;

class PreguntasConmponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $preguntas = Preguntasfrecuentes::where('widget_id', $this->widget)->get();
        return Form::preguntas($preguntas);
    }

    public function viewComponent()
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
                'etiqueta' => 'Clientes'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $preguntas = Preguntasfrecuentes::where('widget_id', $this->widget)->get();
        return view('web.components.preguntas', compact('miga_pan', 'variables_url', 'widget', 'preguntas'));
    }
}