<?php


namespace App\Http\Controllers\web\services;

use App\web\Configuracionfuente;
use App\web\Testimoniale;
use Illuminate\Support\Facades\Input;
use Form;

class TestimonialesComponent implements IDrawComponent
{

    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $testimonial = Testimoniale::where('widget_id', $this->widget)->first();
        return Form::testimoniales($testimonial);
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
        $fuentes = Configuracionfuente::all();
        $fonts = null;
        if (count($fuentes) > 0) {
            foreach ($fuentes as $f) {
                $fonts[$f->id] = $f->fuente->font;
            }
        }
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $testimonial = Testimoniale::where('widget_id', $this->widget)->first();
        return view('web.components.testimoniales', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'testimonial'));
    }
}
