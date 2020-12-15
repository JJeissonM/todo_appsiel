<?php


namespace App\Http\Controllers\web\services;


use App\web\Cliente;
use App\web\Configuracionfuente;
use Form;
use Illuminate\Support\Facades\Input;

class ClientesComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $clientes = Cliente::where('widget_id', $this->widget)->first();
        return Form::clientes($clientes);
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
        $clientes = Cliente::where('widget_id', $widget)->first();
        return view('web.components.clientes', compact('miga_pan', 'fonts', 'variables_url', 'widget', 'clientes'));
    }
}
