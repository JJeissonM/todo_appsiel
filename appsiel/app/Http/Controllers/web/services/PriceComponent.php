<?php


namespace App\Http\Controllers\web\services;

use App\web\Price;
use Form;
use Illuminate\Support\Facades\Input;

class PriceComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $Price = Price::where('widget_id', $this->widget)->first();
        if ($Price != null) {
            return Form::Price($Price);
        }
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
                'etiqueta' => 'Componente Price (Planes de Precios)'
            ]
        ];
        $widget = $this->widget;

        $variables_url = '?id=' . Input::get('id');
        $Price = Price::where('widget_id', $widget)->first();
        return view('web.components.prices', compact('miga_pan', 'variables_url', 'widget', 'Price'));
    }
}
