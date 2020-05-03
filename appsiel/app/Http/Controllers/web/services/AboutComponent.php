<?php


namespace App\Http\Controllers\web\services;


use App\web\Aboutus;
use Form;
use Illuminate\Support\Facades\Input;

class AboutComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $aboutus = Aboutus::where('widget_id', $this->widget)->first();
        if($aboutus->disposicion != 'DEFAULT'){
            return Form::aboutuspremiun($aboutus);
        }else{
            return Form::aboutus($aboutus);
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
                'etiqueta' => 'Quiénes somos'
            ]
        ];
        $widget = $this->widget;

        $variables_url = '?id=' . Input::get('id');
        $aboutus = Aboutus::where('widget_id', $widget)->first();
        return view('web.components.about_us', compact('miga_pan', 'variables_url', 'widget', 'aboutus'));
    }

}