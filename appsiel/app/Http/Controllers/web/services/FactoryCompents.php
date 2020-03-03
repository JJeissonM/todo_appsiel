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
            case "Navegacion":
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
            case "Servicios":
                $component = new ServicioComponent($this->widget);
                break;
            case "Artículos":
                $component = new ArticleComponent($this->widget);
                break;
            case "Pie de página":
                $component = new FooterComponent($this->widget);
                break;
            case "Contáctenos":
                $component = new ContactenosComponent($this->widget);
                break;
            case "Clientes":
                $component = new ClientesComponent($this->widget);
                break;
            default:
                $component = false;
        }

        return $component;
    }

}
