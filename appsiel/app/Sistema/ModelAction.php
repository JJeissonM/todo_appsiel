<?php

namespace App\Sistema;

/*
        NO FUNCIONA - BUSCAR UNA MEJOR FORMA
*/
class ModelAction
{
    protected $urls_acciones;

    protected $acciones = [];

    public function __construct( string $json_urls_acciones )
    {
        $urls_acciones = json_decode( $json_urls_acciones );

        if ( is_null( $urls_acciones ) )
        {
            return null;
        }


        // Acciones particulares, si están definidas en la variable $urls_acciones de la class del modelo
        if ( isset( $urls_acciones->create ) )
        {
            $this->acciones->create = $urls_acciones->create;
        }

        if ( isset( $urls_acciones->edit ) )
        {
            $this->acciones->edit = $urls_acciones->edit;
        }
        
        if ( isset( $urls_acciones->store ) )
        {
            $this->acciones->store = $urls_acciones->store;
        }
        
        if ( isset( $urls_acciones->update ) )
        {
            $this->acciones->update = $urls_acciones->update;
        }
        
        if ( isset( $urls_acciones->show ) )
        {
            $this->acciones->show = $urls_acciones->show;
        }
        
        if ( isset( $urls_acciones->imprimir ) )
        {
            $this->acciones->imprimir = $urls_acciones->imprimir;
        }
        
        if ( isset( $urls_acciones->eliminar ) )
        {
            $this->acciones->eliminar = $urls_acciones->eliminar;
        }
        
        if ( isset( $urls_acciones->cambiar_estado ) )
        {
            $this->acciones->cambiar_estado = $urls_acciones->cambiar_estado;
        }
        
        // Otros enlaces en formato JSON
        if ( isset( $urls_acciones->otros_enlaces ) )
        {
            $this->acciones->otros_enlaces = $urls_acciones->otros_enlaces;
        }

        return $this->acciones;
    }
}
