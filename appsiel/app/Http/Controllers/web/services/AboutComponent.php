<?php


namespace App\Http\Controllers\web\services;


use App\web\Aboutus;
use App\web\Configuracionfuente;
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
        if ($aboutus != null) {
            if ($aboutus->disposicion != 'DEFAULT') {
                return Form::aboutuspremiun($aboutus);
            } else {
                return Form::aboutus($aboutus);
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
                'etiqueta' => 'Quiénes somos'
            ]
        ];
        $widget = $this->widget;
        $fuentes = Configuracionfuente::all();
        $fonts = null;
        if (count($fuentes) > 0) {
            foreach ($fuentes as $f) {
                $fonts[$f->id] = $f->fuente->font;
            }
        }
        $variables_url = '?id=' . Input::get('id');
        $aboutus = Aboutus::where('widget_id', $widget)->first();
        return view('web.components.about_us', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'aboutus'));
    }
}
