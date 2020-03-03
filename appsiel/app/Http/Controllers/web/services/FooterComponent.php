<?php

namespace App\Http\Controllers\web\services;

use App\web\Contactenos;
use App\web\Footer;
use App\web\RedesSociales;
use App\web\Seccion;
use App\web\Widget;
use Form;

class FooterComponent implements IDrawComponent
{

    /**
     * FooterComponent constructor.
     * @param $widget
     */
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        $widget =  Widget::find($this->widget);
        $seccion =  Seccion::where('nombre','Contáctenos')->first();
        $contacto = Widget::where([
            ['seccion_id',$seccion->id],
            ['pagina_id',$widget->pagina_id]
        ])->first();
        $contactenos = Contactenos::where('widget_id', $contacto->id)->first();
        return Form::footer($footer,$redes,$contactenos);
    }

    function viewComponent()
    {
        // TODO: Implement viewComponent() method.
    }
}