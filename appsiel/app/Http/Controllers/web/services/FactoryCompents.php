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
            case "Slider":
                $component = new SliderComponent($this->widget);
                break;
            case "Navegación":
                $component = new NavegacionComponent($this->widget);
                break;
            case "Quienes somos":
                $component = new AboutComponent($this->widget);
                break;
            case "Galería":
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
            case "Archivos":
                $component = new ArchivosComponent($this->widget);
                break;
            case "Preguntas Frecuentes":
                $component = new PreguntasConmponent($this->widget);
                break;
            case "Tienda Online":
                $component = new TiendaComponent($this->widget);
                break;
            case "Testimoniales":
                $component = new TestimonialesComponent($this->widget);
                break;
            case "Html personalizado":
                $component = new CustomHtmlComponent($this->widget);
                break;
            case "PQR":
                $component = new PqrComponent($this->widget);
                break;
            case "Parallax":
                $component = new ParallaxComponent($this->widget);
                break;
            case "Sticky":
                $component = new StickyComponent($this->widget);
                break;
            case "Modal" :
                $component =  new ModalComponent($this->widget);
                break;
            case "Guías académicas":
                $component = new GuiasAcademicasComponent($this->widget);
                break;
            case "Login":
                $component = new LoginComponent($this->widget);
                break;
            default:
                $component = false;
        }

        return $component;
    }
}
