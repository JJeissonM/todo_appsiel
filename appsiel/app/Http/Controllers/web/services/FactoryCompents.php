<?php

namespace App\Http\Controllers\web\services;

use App\Http\Controllers\web\GaleriaController;

class FactoryCompents
{

    public function __construct($seccion, $widget)
    {
        $this->seccion = $seccion;
        $this->widget = $widget;
    }

    public function __invoke()
    {

        switch ($this->seccion) {
            case "navegacion":
                $component = new NavegacionComponent($this->widget);
                break;
            case "slider":
                $component = new SliderComponent($this->widget);
                break;
            case "About us":
                $component = new AboutComponent($this->widget);
                break;
            case "galeria":
                $component = new GaleriaComponent($this->widget);
                break;
            case "servicios":
                $component = new ServicioComponent($this->widget);
                break;
            case "Artículos":
                $component = new ArticleComponent($this->widget);
                break;
            default:
                $component = false;
        }

        return $component;
    }

}
