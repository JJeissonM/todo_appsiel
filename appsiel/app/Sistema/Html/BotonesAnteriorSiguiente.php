<?php

namespace App\Sistema\Html;

use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\Auth;

class BotonesAnteriorSiguiente
{
    public $reg_anterior;

    public $reg_siguiente;

    /*
     * Recibe una instancia de la trancacción y el id del encabezado del documento.
     */
    public function __construct( $transaccion, $doc_encabezado_id, $aditional_wheres = [] )
    {
        $array_wheres = [
            ['core_empresa_id', Auth::user()->empresa_id],
            ['core_tipo_transaccion_id', $transaccion->id]
        ];

        if ( !empty($aditional_wheres) ) {
            $array_wheres = array_merge($array_wheres, $aditional_wheres);
        }

        if ( Auth::check() )
        {
            // Enlaces para botones Anterior y Siguiente
            $this->reg_anterior = app( $transaccion->modelo_encabezados_documentos )
                                ->where( 'id', '<', $doc_encabezado_id )
                                ->where( $array_wheres )
                                ->max('id');
            $this->reg_siguiente = app( $transaccion->modelo_encabezados_documentos )
                                ->where('id', '>', $doc_encabezado_id)
                                ->where( $array_wheres )
                                ->min('id');
        }
    }

    /**
     * Dibujar botón
     *
     */
    public function dibujar( $url_show, $variables_url )
    {
        $botones = '';
        if($this->reg_anterior!='')
        {
            $botones .= Form::bsBtnPrev( $url_show.$this->reg_anterior.$variables_url );
        }

        if($this->reg_siguiente!='')
        {
            $botones .= Form::bsBtnNext( $url_show.$this->reg_siguiente.$variables_url );
        }

        return $botones;
    }

}