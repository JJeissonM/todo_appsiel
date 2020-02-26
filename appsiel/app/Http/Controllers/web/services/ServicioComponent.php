<?php


namespace App\Http\Controllers\web\services;


use App\web\Servicio;
use Form;
use Illuminate\Support\Facades\Input;

class ServicioComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $servicio = Servicio::where('widget_id', $this->widget)->first();
        return Form::servicios($servicio);
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
                'etiqueta' => 'Servicios'
            ]
        ];

        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $servicios = Servicio::where('widget_id', $widget)->first();
        return view('web.components.servicios', compact('miga_pan', 'variables_url', 'widget', 'servicios'));
    }
}