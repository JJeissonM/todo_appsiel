<?php

namespace App\Http\Controllers\web\services;

use App\web\Contactenos;
use App\web\Modal;
use App\web\Sticky;
use Form;
use Illuminate\Support\Facades\Input;

class ModalComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $modal = Modal::where('widget_id', $this->widget)->first();
        return Form::modal($modal);
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
                'etiqueta' => 'Modal'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $modal = Modal::where('widget_id', $widget)->first();
        return view('web.components.modal', compact('miga_pan', 'variables_url', 'widget', 'modal'));
    }

}