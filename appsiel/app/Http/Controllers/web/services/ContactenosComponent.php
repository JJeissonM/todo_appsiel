<?php


namespace App\Http\Controllers\web\services;

use App\web\Configuracionfuente;
use App\web\Contactenos;
use Form;
use Illuminate\Support\Facades\Input;

class ContactenosComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $contactenos = Contactenos::where('widget_id', $this->widget)->first();
        return Form::formcontacto($contactenos);
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
                'etiqueta' => 'Contáctenos'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $contactenos = Contactenos::where('widget_id', $widget)->first();
        $fuentes = Configuracionfuente::all();
        $fonts = null;
        if (count($fuentes) > 0) {
            foreach ($fuentes as $f) {
                $fonts[$f->id] = $f->fuente->font;
            }
        }
        return view('web.components.contactenos', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'contactenos'));
    }
}
