<?php

namespace App\Http\Controllers\web\services;

use App\web\Navegacion;
use Form;

class NavegacionComponent implements IDrawComponent
{

    public function __construct($widget){
      $this->widget = $widget;
    }

    function DrawComponent()
    {
       $nav = Navegacion::all()->first();
       return Form::navegacion($nav);
    }

    function viewComponent()
    {
        // TODO: Implement viewComponent() method.
    }
}