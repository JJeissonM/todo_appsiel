<?php


namespace App\Http\Controllers\web\services;

use App\web\PqrForm;
use App\web\Widget;

use App\Sistema\Campo;
use App\web\Configuracionfuente;
use Illuminate\Support\Facades\Input;
use Form;

class PqrComponent implements IDrawComponent
{

    public function __construct($widget_id)
    {
        $this->widget = $widget_id;
    }

    public function DrawComponent()
    {
        $registro = PqrForm::where('widget_id', $this->widget)->first();

        $pagina = Widget::find($this->widget)->pagina;

        // Se llama al FormServiceProvider
        return Form::pqr($registro, $pagina);
    }

    public function viewComponent()
    {

        $widget = Widget::find($this->widget);

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
                'etiqueta' => $widget->seccion->nombre
            ]
        ];

        $fuentes = Configuracionfuente::all();
        $fonts = null;
        if (count($fuentes) > 0) {
            foreach ($fuentes as $f) {
                $fonts[$f->id] = $f->fuente->font;
            }
        }

        $variables_url = '?id=' . Input::get('id');

        $campos = Campo::opciones_campo_select();

        $registro = PqrForm::where('widget_id', $this->widget)->first();

        $pagina = Widget::find($this->widget)->pagina;

        return view('web.components.pqr', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'campos', 'registro', 'pagina'));
    }
}
