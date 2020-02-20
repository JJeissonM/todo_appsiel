<?php

namespace App\Http\Controllers\web\services;

class FactoryCompents {

    public function __construct($seccion,$widget)
    {
        $this->seccion =  $seccion;
        $this->widget = $widget;
    }

    public function __invoke()
    {

        switch ($this->seccion){
            case "navegacion" :
                 $component = new NavegacionComponent($this->widget);
                 break;
            case "slider" :
                $component = new SliderComponent($this->widget);
                break;
            default :
                   $component= false;
        }

        return $component;
    }

}
