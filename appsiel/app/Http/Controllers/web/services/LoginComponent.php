<?php


namespace App\Http\Controllers\web\services;


use App\web\Login;
use App\web\Navegacion;
use Form;
use Illuminate\Support\Facades\Input;

class LoginComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent() {
        $login = Login::where('widget_id', $this->widget)->first();
        if($login->disposicion == 'DEFAULT'){
            $nav = Navegacion::all()->first();
            return Form::login($login,$nav);
        }
    }

    public function viewComponent() {
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
        $login = Login::where('widget_id', $widget)->first();
        return view('web.components.login.login', compact('miga_pan', 'variables_url', 'widget', 'login'));
    }

}