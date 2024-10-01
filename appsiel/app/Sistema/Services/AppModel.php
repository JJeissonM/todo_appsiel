<?php

namespace App\Sistema\Services;

use App\Sistema\Modelo;
use Illuminate\Support\Facades\View;

class AppModel
{
    public $modelo;

    public function __construct( $model_id )
    {
        $this->modelo = Modelo::find( $model_id );
    }

    public function get_encabezado_tabla()
    {
        $encabezado_tabla = app( $this->modelo->name_space )->encabezado_tabla;
        if( is_null($encabezado_tabla) )
        {
            $encabezado_tabla = [];
        }
        
        return $encabezado_tabla;
    }

    public function get_records_table($string_search)
    {
        $encabezado_tabla = app( $this->modelo->name_space )->encabezado_tabla;
        if( is_null($encabezado_tabla) )
        {
            $encabezado_tabla = [];
        }

        $registros = [];
        if ( method_exists( app( $this->modelo->name_space ), 'consultar_registros') )
        {
            $registros = app( $this->modelo->name_space )->consultar_registros( 10, $string_search);
        }

        $url_ver = '';

        return View::make('layouts.index_records_table', compact('encabezado_tabla','registros','url_ver') )->render();
    }

    public function get_records_filtered($array_wheres)
    {
        return app( $this->modelo->name_space )->where( $array_wheres )
                ->get();
    }
    public function acciones_basicas_modelo( Modelo $modelo, string $parametros_url)
    {
        // Acciones predeterminadas
        $acciones = (object)[
            'index' => 'web' . $parametros_url,
            'create' => '',
            'edit' => '',
            'store' => 'web',
            'update' => 'web/id_fila',
            'show' => 'web/id_fila' . $parametros_url,
            'imprimir' => '',
            'eliminar' => '',
            'cambiar_estado' => '',
            'otros_enlaces' => ''
        ];


        // Se agregan los enlaces que tiene el modelo en la base de datos (ESTO DEBE DESAPARECER, PERO PRIMERO SE DEBEN MIGRAR LOS MODELOS ANTIGUOS)
        if ($modelo->url_crear != '') {
            $acciones->create = $modelo->url_crear . $parametros_url;
        }

        if ($modelo->url_edit != '') {
            $acciones->edit = $modelo->url_edit . $parametros_url;
        }

        if ($modelo->url_form_create != '') {
            $acciones->store = $modelo->url_form_create;
            $acciones->update = $modelo->url_form_create . '/id_fila';
        }

        if ($modelo->url_print != '') {
            $acciones->imprimir = $modelo->url_print . $parametros_url;
        }

        if ($modelo->url_ver != '') {
            $acciones->show = $modelo->url_ver . $parametros_url;
        }

        if ($modelo->url_estado != '') {
            $acciones->cambiar_estado = $modelo->url_estado . $parametros_url;
        }

        if ($modelo->url_eliminar != '') {
            $acciones->eliminar = $modelo->url_eliminar . $parametros_url;
        }

        // Otros enlaces en formato JSON
        if ($modelo->enlaces != '') {
            $acciones->otros_enlaces = $modelo->enlaces;
        }

        // MANEJO DE URLs DESDE EL ARCHIVO CLASS DEL PROPIO MODELO 
        // Se llaman las urls desde la class (name_space) del modelo
        $urls_acciones = json_decode(app($modelo->name_space)->urls_acciones);

        if (!is_null($urls_acciones)) {

            // Acciones particulares, si están definidas en la variable $urls_acciones de la class del modelo

            if (isset($urls_acciones->create)) {
                if ($urls_acciones->create != 'no') {
                    $acciones->create = $urls_acciones->create . $parametros_url;
                }
            }

            if (isset($urls_acciones->edit)) {
                if ($urls_acciones->edit != 'no') {
                    $acciones->edit = $urls_acciones->edit . $parametros_url;
                }
            }

            if (isset($urls_acciones->store)) {
                if ($urls_acciones->store != 'no') {
                    $acciones->store = $urls_acciones->store;
                }
            }

            if (isset($urls_acciones->update)) {
                if ($urls_acciones->update != 'no') {
                    $acciones->update = $urls_acciones->update;
                }
            }

            if (isset($urls_acciones->show)) {
                if ($urls_acciones->show != 'no') {
                    $acciones->show = $urls_acciones->show . $parametros_url;
                }

                if ($urls_acciones->show == 'no') {
                    $acciones->show = '';
                }
            }

            if (isset($urls_acciones->imprimir)) {
                if ($urls_acciones->imprimir != 'no') {
                    $acciones->imprimir = $urls_acciones->imprimir . $parametros_url;
                }
            }

            if (isset($urls_acciones->eliminar)) {
                if ($urls_acciones->eliminar != 'no') {
                    $acciones->eliminar = $urls_acciones->eliminar . $parametros_url;
                }
            }

            if (isset($urls_acciones->cambiar_estado)) {
                if ($urls_acciones->cambiar_estado != 'no') {
                    $acciones->cambiar_estado = $urls_acciones->cambiar_estado . $parametros_url;
                }
            }

            // Otros enlaces en formato JSON
            if (isset($urls_acciones->otros_enlaces)) {
                if ($urls_acciones->otros_enlaces != 'no') {
                    $acciones->otros_enlaces = $urls_acciones->otros_enlaces;
                }
            }
        }

        return $acciones;
    }
}
