<?php

namespace App\Nomina\Services\Pila;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomEntidad;
use App\Nomina\PilaParafiscales;

class ParafiscalService
{
    public function get_valores_campos( $planilla_id, $empleado_id, $empleado_planilla_id )
    {
        $novedades_empleado = PilaParafiscales::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaParafiscales;
        $campos = $pila_novedades->getFillable();
        foreach ($campos as $key => $value)
        {
            if ($key > 2)
            {
                if( is_null( $novedades_empleado ) )
                {
                    $vector[] = '';
                }else{
                    $vector[] = $novedades_empleado->$value;
                }             
            }                
        }

        array_pop($vector);
        return $vector;
    }

    public function get_total_cotizacion_por_entidad( $fecha_final_mes )
    {
        $entidades_con_movimiento = PilaParafiscales::where('fecha_final_mes',$fecha_final_mes)
                                                    ->get()
                                                    ->unique('codigo_entidad_ccf')
                                                    ->values()
                                                    ->all();
        $coleccion_movimientos = [];
        foreach( $entidades_con_movimiento AS $entidad )
        {
            $obj = (object)[];
            $obj->entidad = $entidad->entidad(); 
            $obj->total_cotizacion = PilaParafiscales::where( [
                                                        ['fecha_final_mes', '=', $fecha_final_mes],
                                                        ['codigo_entidad_ccf', '=', $entidad->codigo_entidad_ccf ] 
                                                    ] 
                                                ) 
                                                ->sum('cotizacion_ccf');
            $coleccion_movimientos[] = $obj;
        }

        // SENA
        $obj = (object)[];
        $obj->entidad = NomEntidad::where('codigo_nacional','PASENA')->get()->first(); 
        $obj->total_cotizacion = PilaParafiscales::where( [
                                                    ['fecha_final_mes', '=', $fecha_final_mes]
                                                ] 
                                            ) 
                                            ->sum('cotizacion_sena');
        $coleccion_movimientos[] = $obj;

        // ICBF
        $obj = (object)[];
        $obj->entidad = NomEntidad::where('codigo_nacional','PAICBF')->get()->first(); 
        $obj->total_cotizacion = PilaParafiscales::where( [
                                                    ['fecha_final_mes', '=', $fecha_final_mes]
                                                ] 
                                            ) 
                                            ->sum('cotizacion_icbf');
        $coleccion_movimientos[] = $obj;


        return $coleccion_movimientos;
    }
    
}
