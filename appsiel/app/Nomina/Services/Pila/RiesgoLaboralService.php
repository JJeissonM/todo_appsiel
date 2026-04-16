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

    public function get_total_cotizacion_por_entidad( $fecha_final_mes, array $planilla_ids = [] )
    {
        $query = PilaRiesgoLaboral::where('fecha_final_mes',$fecha_final_mes);
        if (!empty($planilla_ids)) {
            $query->whereIn('planilla_generada_id', $planilla_ids);
        }

        $entidades_con_movimiento = $query->get()
                                                    ->unique('codigo_arl')
                                                    ->values()
                                                    ->all();
        $coleccion_movimientos = [];
        foreach( $entidades_con_movimiento AS $entidad )
        {
            $obj = (object)[];
            $obj->entidad = $entidad->entidad(); 
            $total_query = PilaRiesgoLaboral::where( [
                                                        ['fecha_final_mes', '=', $fecha_final_mes],
                                                        ['codigo_arl', '=', $entidad->codigo_arl ] 
                                                    ] 
                                                );
            if (!empty($planilla_ids)) {
                $total_query->whereIn('planilla_generada_id', $planilla_ids);
            }
            $obj->total_cotizacion = $total_query->sum('total_cotizacion_riesgos_laborales');
            $coleccion_movimientos[] = $obj;
        }

        return $coleccion_movimientos;
    }
    
}
