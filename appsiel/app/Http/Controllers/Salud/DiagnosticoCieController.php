<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use View;
use Input;

use App\Sistema\Modelo;
use App\Salud\DiagnosticoCie;
use App\Sistema\Aplicacion;

class DiagnosticoCieController extends ModeloController
{
    public function create()
    {
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );

        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'modelo_entidad_id':
                    $lista_campos[$i]['value'] = $this->modelo->id;
                    break;
                case 'paciente_id':
                    $lista_campos[$i]['value'] = Input::get('paciente_id');
                    break;
                case 'consulta_id':
                    $lista_campos[$i]['value'] = Input::get('consulta_id');
                    break;
                default:
                    # code...
                    break;
            }
        }

        $form_create = [
                            'url' => 'salud_diagnostico_cie',
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create','datos_columnas') )->render();
    }

    public function store(Request $request)
    {
        $modelo = Modelo::find( $request->modelo_entidad_id );
        
        $record_created = app( $modelo->name_space )->create( $request->all() );
        
        return response()->json( $record_created->get_fields_to_show() );
    }

    public function delete( $id )
    {
        DiagnosticoCie::where('id',$id)->delete();
        return 1;
    }


    /*
        DE AQUI PARA ABAJO FALTA TERMINAR
    */
    
    // Parámetro enviados por GET - para la nueva version lista_sugerencias
    public function get_suggestions_list()
    {
        $datos = $this->get_records_list( (int)Input::get('texto_busqueda'), Input::get('texto_busqueda') );

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
        $html .= '<a href="'.url('web/create?id=7&id_modelo='.$modelo_id.'&id_transaccion').'" target="_blank" class="list-group-item list-group-item-sugerencia list-group-item-info" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo </a>';

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

        $aplicacion_contratos_transporte = Aplicacion::where('app','contratos_transporte')->get()->first();

        if ( !is_null($aplicacion_contratos_transporte) )
        {
            if ( $aplicacion_contratos_transporte->estado == 'Activo' )
            {
                $html .= '" data-vehiculo_id="'.$tercero->vehiculo_id;
            }
        }            

        return $html;
    }

    public function get_label_linea( $tercero )
    {
        $label = $tercero->descripcion.' ('.number_format($tercero->numero_identificacion,0,',','.').')';

        $aplicacion_contratos_transporte = Aplicacion::where('app','contratos_transporte')->get()->first();

        if ( !is_null($aplicacion_contratos_transporte) )
        {
            if ( Aplicacion::where('app','contratos_transporte')->get()->first()->estado == 'Activo' )
            {
                $label .= ' (' . $tercero->placa . ')';
            }
        }

        return $label;
    }

    public function get_records_list( $buscar_por_codigo, $cadena_busqueda )
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
                            //[ 'core_terceros.core_empresa_id', '=', Auth::user()->empresa_id ],
                            [ 'core_terceros.'.$campo_busqueda, 'LIKE', $cadena_busqueda ]
                        ];

        $aplicacion_contratos_transporte = Aplicacion::where('app','contratos_transporte')->get()->first();
        if ( !is_null( $aplicacion_contratos_transporte ) )
        {
            if ( $aplicacion_contratos_transporte->estado == 'Activo' )
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
            }
        }
        
        return Tercero::where($array_wheres)
                        ->get()
                        ->take(7);
    }
}   