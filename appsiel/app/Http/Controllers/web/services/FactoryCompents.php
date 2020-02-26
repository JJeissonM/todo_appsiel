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
            case "Navegacion" :
                $component = new NavegacionComponent($this->widget);
                break;
            case "Slider" :
                $component = new SliderComponent($this->widget);
                break;
            case "About us":
                $component = new AboutComponent($this->widget);
                break;
            case "Galeria":
                $component = new GaleriaComponent($this->widget);
                break;
            case "servicios":
                $component = new ServicioComponent($this->widget);
                break;
            case "Articulos":
                $component = new ArticleComponent($this->widget);
                break;
            case "Pie de pagina":
                 $component = new FooterComponent($this->widget);
                break;
            default:
                $component = false;
        }

        return $component;
    }

}
