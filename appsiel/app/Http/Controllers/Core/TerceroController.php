<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Input;

use App\Sistema\Aplicacion;

use App\User;
use App\Core\Tercero;
use App\Matriculas\Inscripcion;

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

    // Para Inscripciones de estudiantes
    public function validar_numero_identificacion2( $numero_identificacion )
    {
        $tercero = Tercero::where('numero_identificacion',$numero_identificacion)
                            ->get()
                            ->first();

        if ( is_null( $tercero ) )
        {
            return 'tercero_no_existe';
        }
        
        $tercero->email2 = $tercero->email;

        $inscripcion = Inscripcion::where('core_tercero_id',$tercero->id)
                                    ->where('estado','Pendiente')
                                    ->get()->first();

        if ( is_null($inscripcion) )
        {
            return response()->json( $tercero->toArray() );
        }

        return 'ya_inscrito';
    }

    public function validar_inscripcion( $numero_identificacion )
    {
        $tercero = Tercero::lefJoin('sga_inscripciones','sga_inscripciones.core_tercero_id','=','core_terceros.id')
                            ->where('core_terceros.numero_identificacion',$numero_identificacion)
                            ->where('sga_inscripciones.estado', 'Pendiente')
                            ->get()
                            ->first();

        if ( is_null($tercero) )
        {
            return '';
        }
                            //dd($tercero);
        $tercero->email2 = $tercero->email;
        return response()->json( $tercero->toArray() );
    }


    public function validar_email( $email )
    {

        return User::where('email',$email)->value('email');
    }
    
    // Parámetro enviados por GET - para la nueva version lista_sugerencias
    public function consultar_terceros_v2()
    {
        /*
            (int)Input::get('texto_busqueda') arroja un valor numérico o cero cuando es texto
        */
        $datos = $this->get_listado_terceros( (int)Input::get('texto_busqueda'), Input::get('texto_busqueda') );

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_datos = count( $datos->toArray() );
        foreach ($datos as $linea) 
        {
            $primer_item = 0;
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
                $primer_item = 1;
            }


            if ( $num_item == $cantidad_datos )
            {
                $ultimo_item = 1;
            }

            $html .= '<a class="list-group-item list-group-item-sugerencia '.$clase.'" data-registro_id="'.$linea->id.
                                '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item; // Esto debe ser igual en todas las busquedas

            $html .= $this->get_atributos_data_adicionales($linea);

            $html .=            '" > ' . $this->get_label_linea( $linea ) . ' </a>';

            $num_item++;
        }

        // Linea crear nuevo registro
        $modelo_id = 7; // App\Core\Tercero
        $html .= '<a href="'.url('web/create?id=7&id_modelo='.$modelo_id.'&id_transaccion').'" target="_blank" class="list-group-item list-group-item-sugerencia list-group-item-warning" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo </a>';

        $html .= '</div>';

        return $html;
    }

    public function get_atributos_data_adicionales( $tercero )
    {
        $html =            '" data-tipo_campo="tercero" ';

        $html .=            '" data-descripcion="'.$tercero->descripcion;
        $html .=            '" data-numero_identificacion="'.number_format($tercero->numero_identificacion,0,',','.');
        $html .=            '" data-direccion1="'.$tercero->direccion1;
        $html .=            '" data-telefono1="'.$tercero->telefono1;
        $html .=            '" data-email="'.$tercero->email;

        if ( Aplicacion::where('app','contratos_transporte')->get()->first()->estado == 'Activo' )
        {
            $html .=            '" data-vehiculo_id="'.$tercero->vehiculo_id;
        }

        return $html;
    }

    public function get_label_linea( $tercero )
    {
        $label = $tercero->descripcion.' ('.number_format($tercero->numero_identificacion,0,',','.').')';

        if ( Aplicacion::where('app','contratos_transporte')->get()->first()->estado == 'Activo' )
        {
            $label .= ' (' . $tercero->placa . ')';
        }

        return $label;
    }

    public function get_listado_terceros( $buscar_por_codigo, $cadena_busqueda )
    {
        if( $buscar_por_codigo == 0 )
        {
            $campo_busqueda = 'descripcion';
            $cadena_busqueda = '%' . str_replace( " ", "%", $cadena_busqueda ) . '%';
        }else{
            $campo_busqueda = 'numero_identificacion';
            $cadena_busqueda = $cadena_busqueda.'%';
        }

        $array_wheres = [ 
                            [ 'core_terceros.estado', '=', 'Activo' ],
                            [ 'core_terceros.core_empresa_id', '=', Auth::user()->empresa_id ],
                            [ 'core_terceros.'.$campo_busqueda, 'LIKE', $cadena_busqueda ]
                        ];

        if ( Aplicacion::where('app','contratos_transporte')->get()->first()->estado == 'Activo' )
        {
            $vehiculos = \App\Contratotransporte\Vehiculo::leftJoin('cte_propietarios','cte_propietarios.id','=','cte_vehiculos.propietario_id')
                            ->leftJoin('core_terceros','core_terceros.id','=','cte_propietarios.tercero_id')
                            ->where($array_wheres)
                            ->orWhere('cte_vehiculos.placa','LIKE',$cadena_busqueda)
                            ->select('core_terceros.id','core_terceros.descripcion','core_terceros.numero_identificacion','core_terceros.direccion1','core_terceros.telefono1','core_terceros.email','cte_vehiculos.placa','cte_vehiculos.id AS vehiculo_id')
                            ->get()
                            ->take(7);

            if( empty( $vehiculos->toArray() ) )
            {
                return Tercero::where($array_wheres)
                            ->get()
                            ->take(7);
            }

            return $vehiculos;
        }/**/

        return Tercero::where($array_wheres)
                        ->get()
                        ->take(7);
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

        $datos = Tercero::where('core_terceros.estado','Activo')
                    ->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)
                    ->where('core_terceros.'.$campo_busqueda,$operador,$texto_busqueda)
                    ->select('core_terceros.id AS tercero_id','core_terceros.descripcion','core_terceros.numero_identificacion')
                    ->get()
                    ->take(7);

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