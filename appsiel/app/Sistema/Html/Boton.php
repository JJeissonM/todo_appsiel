<?php

namespace App\Sistema\Html;

class Boton
{
    public $url;

    public $title;
    
    public $color_bootstrap;
    
    public $faicon;

    public $estructura_html;


    public function __construct( $btn )
    {
        //$btn = json_encode( $datos_btn );
        $this->url = $btn->url;
        $this->title = $btn->title;
        $this->color_bootstrap = $btn->color_bootstrap;
        $this->faicon = $btn->faicon;
        $this->size = $btn->size;

        switch ( $btn->tag_html ) {
            case 'a':
                $this->estructura_html = '<a btn_atributos> btn_contenido </a>';
                break;

            case 'button':
                $this->estructura_html = '<a btn_atributos> btn_contenido </a>';
                break;
            
            default:
                $this->estructura_html = '<p> El TAG HTML para el botón no existe. ver BotonController. </p>';
                break;
        }
    }

    /**
     * Dibujar botón
     *
     */
    public function dibujar()
    {
        $btn_atributos = '';
        $btn_contenido = $this->title;

        if ( $this->url != '') {
            $btn_atributos .= ' href="'.url($this->url).'"';
        }
        
        if ( $this->title != '') {
            $btn_atributos .= ' title="'.$this->title.'"';
        }
        
        if ( $this->color_bootstrap != '') {
            $btn_atributos .= ' class="btn-gmail"';
        }

        if ( $this->faicon != '') {
            $btn_contenido = ' <i class="fa fa-'.$this->faicon.'"></i>';
        }

        $btn = str_replace("btn_atributos", $btn_atributos, $this->estructura_html);
        return str_replace("btn_contenido", $btn_contenido, $btn);
    }

}