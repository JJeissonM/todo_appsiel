<?php

namespace App\Nomina\Services\Pila;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\PilaRiesgoLaboral;

class RiesgoLaboralService
{
    public function get_valores_campos( $planilla_id, $empleado_id, $empleado_planilla_id )
    {
        $novedades_empleado = PilaRiesgoLaboral::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaRiesgoLaboral;
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
        array_pop($vector);
        return $vector;
    }

    public function get_total_cotizacion_por_entidad( $fecha_final_mes )
    {
        $entidades_con_movimiento = PilaRiesgoLaboral::where('fecha_final_mes',$fecha_final_mes)
                                                    ->get()
                                                    ->unique('codigo_arl')
                                                    ->values()
                                                    ->all();
        $coleccion_movimientos = [];
        foreach( $entidades_con_movimiento AS $entidad )
        {
            $obj = (object)[];
            $obj->entidad = $entidad->entidad(); 
            $obj->total_cotizacion = PilaRiesgoLaboral::where( [
                                                        ['fecha_final_mes', '=', $fecha_final_mes],
                                                        ['codigo_arl', '=', $entidad->codigo_arl ] 
                                                    ] 
                                                ) 
                                                ->sum('total_cotizacion_riesgos_laborales');
            $coleccion_movimientos[] = $obj;
        }

        return $coleccion_movimientos;
    }
    
}
