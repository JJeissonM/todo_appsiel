<?php


namespace App\Http\Controllers\web\services;


use App\web\Contactenos;
use Illuminate\Support\Facades\Input;
use Symfony\Component\DomCrawler\Form;

class ContactenosComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    public function DrawComponent()
    {
        $contactenos = Contactenos::where('widget_id', $this->widget)->first();
        return Form::contactenos($contactenos);
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
        return view('web.components.contactenos', compact('miga_pan', 'variables_url', 'widget', 'contactenos'));
    }
}