<?php


namespace App\Http\Controllers\web\services;

use App\web\Album;
use App\web\Configuracionfuente;
use App\web\Foto;
use App\web\Galeria;
use Form;
use Illuminate\Support\Facades\Input;


class GaleriaComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $galeria = Galeria::where('widget_id', $this->widget)->first();
        return Form::galeria($galeria);
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
                'etiqueta' => 'Galeria de Imágenes'
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
        $galeria = Galeria::where('widget_id', $widget)->first();
        return view('web.components.galeria', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'galeria'));
    }
}
