<?php


namespace App\Http\Controllers\web\services;    


use App\web\CustomHtml;
use Illuminate\Support\Facades\Input;
use Form;

class CustomHtmlComponent implements IDrawComponent
{

    public function __construct($widget_id)
    {
        $this->widget = $widget_id;
    }

    public function DrawComponent()
    {
        // Se llama al FormServiceProvider
        return Form::custom_html( CustomHtml::where( 'widget_id',$this->widget)->first() );
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
                'etiqueta' => 'Html personalizado'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $registro = CustomHtml::where('widget_id',$this->widget)->first();
        return view('web.components.custom_html', compact('miga_pan', 'variables_url', 'widget', 'registro'));
    }
}