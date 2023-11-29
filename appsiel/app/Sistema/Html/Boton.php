<?php

namespace App\Sistema\Html;

class Boton
{
    public $url;

    public $title;
    
    public $color_bootstrap;
    
    public $faicon;

    public $estructura_html;

    public $size;

    public $id;
    
    public $target = '_self';

    public function __construct( $btn )
    {
        $this->url = $btn->url;
        $this->title = $btn->title;
        $this->color_bootstrap = $btn->color_bootstrap;
        $this->faicon = $btn->faicon;
        $this->size = $btn->size;

        if (isset($btn->id)) {
            $this->id = $btn->id;
        }
        
        if (isset($btn->target)) {
            if ($btn->target != null) {
                $this->target = $btn->target;
            }
        }        

        switch ( $btn->tag_html ) {
            case 'a':
                $this->estructura_html = '<a btn_atributos class="btn-gmail" target="' . $this->target .'"> btn_contenido </a>';
                break;

            case 'button':
                if ($this->id == null) {
                    $this->estructura_html = '<button btn_atributos class="btn-gmail"> btn_contenido </button>';
                }else{
                    $this->estructura_html = '<button btn_atributos class="btn-gmail" id="' . $this->id . '"> btn_contenido </button>';
                }
                
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
        
        /*
        if ( $this->color_bootstrap != '') {
            $btn_atributos .= ' class="btn btn-'.$this->color_bootstrap.' btn-'.$this->size.'"';
        }*/

        if ( $this->faicon != '') {
            $btn_contenido = ' <i class="fa fa-'.$this->faicon.'"></i>';
        }

        $btn = str_replace("btn_atributos", $btn_atributos, $this->estructura_html);

        return str_replace("btn_contenido", $btn_contenido, $btn);
    }

}