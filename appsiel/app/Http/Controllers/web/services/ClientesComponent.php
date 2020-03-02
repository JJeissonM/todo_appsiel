<?php


namespace App\Http\Controllers\web\services;


use App\web\Cliente;
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
        $clientes = Cliente::where('widget_id',$this->widget)->first();
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
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $clientes = Cliente::where('widget_id', $widget)->get();
        return view('web.components.clientes', compact('miga_pan', 'variables_url', 'widget', 'clientes'));
    }
}