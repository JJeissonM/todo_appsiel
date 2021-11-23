<?php

namespace App\Nomina\Services\Pila;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\PilaPension;

class PensionService
{
    public function get_valores_campos( $planilla_id, $empleado_id, $empleado_planilla_id )
    {
        $novedades_empleado = PilaPension::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaPension;
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
        $entidades_con_movimiento = PilaPension::where('fecha_final_mes',$fecha_final_mes)
                                                    ->get()
                                                    ->unique('codigo_entidad_pension')
                                                    ->values()
                                                    ->all();
        $coleccion_movimientos = [];
        foreach( $entidades_con_movimiento AS $entidad )
        {
            $obj = (object)[];
            $obj->entidad = $entidad->entidad(); 
            $obj->total_cotizacion = PilaPension::where( [
                                                        ['fecha_final_mes', '=', $fecha_final_mes],
                                                        ['codigo_entidad_pension', '=', $entidad->codigo_entidad_pension ] 
                                                    ] 
                                                ) 
                                                ->sum('total_cotizacion_pension');
            $coleccion_movimientos[] = $obj;
        }

        return $coleccion_movimientos;
    }
    
}
