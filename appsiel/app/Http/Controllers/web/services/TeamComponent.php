<?php


namespace App\Http\Controllers\web\services;

use App\web\Configuracionfuente;
use App\web\Team;
use Form;
use Illuminate\Support\Facades\Input;

class TeamComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $team = Team::where('widget_id', $this->widget)->first();
        if ($team != null) {
            return Form::team($team);
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
                'etiqueta' => 'Componente Tarjetas'
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
        $team = Team::where('widget_id', $widget)->first();
        return view('web.components.teams', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'team'));
    }
}
