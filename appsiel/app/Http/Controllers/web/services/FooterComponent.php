<?php


namespace App\Http\Controllers\web\services;


use App\web\Contactenos;
use App\web\Footer;
use App\web\RedesSociales;
use Form;

class FooterComponent implements IDrawComponent
{

    /**
     * FooterComponent constructor.
     * @param $widget
     */
    public function __construct($widget)
    {
    }

    function DrawComponent()
    {
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        $contactenos = Contactenos::where('widget_id', $this->widget)->first();
        return Form::footer($footer,$redes,$contactenos);
    }

    function viewComponent()
    {
        // TODO: Implement viewComponent() method.
    }
}