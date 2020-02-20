<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Core\Tercero;
use Auth;
use Input;

class TerceroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function validar_numero_identificacion( $numero_identificacion )
    {
        return Tercero::where('numero_identificacion',$numero_identificacion)->value('numero_identificacion');
    }

    public function validar_email( $email )
    {
        return Tercero::where('email',$email)->value('email');
    }
    
    // Parámetro enviados por GET
    public function consultar_terceros()
    {
        $campo_busqueda = Input::get('campo_busqueda');
        
        switch ( $campo_busqueda ) 
        {
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%'.Input::get('texto_busqueda').'%';
                break;
            case 'numero_identificacion':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda').'%';
                break;
            
            default:
                # code...
                break;
        }

        $datos = Tercero::where('core_terceros.estado','Activo')->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)->where('core_terceros.'.$campo_busqueda,$operador,$texto_busqueda)->select('core_terceros.id AS tercero_id','core_terceros.descripcion','core_terceros.numero_identificacion')->get()->take(7);

        //dd($datos);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        foreach ($datos as $linea) 
        {
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
            }

            $html .= '<a class="list-group-item list-group-item-autocompletar '.$clase.'" data-tipo_campo="tercero" data-id="'.$linea->id.
                                '" data-tercero_id="'.$linea->tercero_id.
                                '" > '.$linea->descripcion.' ('.number_format($linea->numero_identificacion,0,',','.').') </a>';
        }
        $html .= '</div>';

        return $html;
    }
}
