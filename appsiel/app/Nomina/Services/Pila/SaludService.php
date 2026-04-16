<?php

namespace App\Nomina\Services\Pila;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\PilaSalud;

class SaludService
{
    public function get_valores_campos( $planilla_id, $empleado_id, $empleado_planilla_id )
    {
        $novedades_empleado = PilaSalud::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaSalud;
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

    public function get_total_cotizacion_por_entidad( $fecha_final_mes, array $planilla_ids = [] )
    {
        $query = PilaSalud::where('fecha_final_mes',$fecha_final_mes);
        if (!empty($planilla_ids)) {
            $query->whereIn('planilla_generada_id', $planilla_ids);
        }

        $entidades_con_movimiento = $query->get()
                                                    ->unique('codigo_entidad_salud')
                                                    ->values()
                                                    ->all();
        $coleccion_movimientos = [];
        foreach( $entidades_con_movimiento AS $entidad )
        {
            $obj = (object)[];
            $obj->entidad = $entidad->entidad(); 
            $total_query = PilaSalud::where( [
                                                        ['fecha_final_mes', '=', $fecha_final_mes],
                                                        ['codigo_entidad_salud', '=', $entidad->codigo_entidad_salud ] 
                                                    ] 
                                                );
            if (!empty($planilla_ids)) {
                $total_query->whereIn('planilla_generada_id', $planilla_ids);
            }
            $movimientos = $total_query
                ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_pila_liquidacion_salud.nom_contrato_id')
                ->select(
                    'nom_pila_liquidacion_salud.total_cotizacion_salud',
                    'nom_pila_liquidacion_salud.tarifa_salud',
                    'nom_contratos.es_pasante_sena'
                )
                ->get();

            $obj->total_cotizacion = $movimientos->sum('total_cotizacion_salud');
            $obj->total_cotizacion_empresa = $movimientos->sum(function ($movimiento) {
                return $this->getValorAporteEmpresa($movimiento);
            });
            $coleccion_movimientos[] = $obj;
        }

        return $coleccion_movimientos;
    }

    protected function getValorAporteEmpresa($movimiento)
    {
        $totalCotizacion = (float)$movimiento->total_cotizacion_salud;
        $tarifaSalud = (float)$movimiento->tarifa_salud;

        if ($totalCotizacion <= 0 || $tarifaSalud <= 0) {
            return 0;
        }

        if ((bool)$movimiento->es_pasante_sena) {
            return $totalCotizacion;
        }

        $tarifaTrabajador = 4 / 100;
        if ($tarifaSalud <= $tarifaTrabajador) {
            return 0;
        }

        return round($totalCotizacion * (($tarifaSalud - $tarifaTrabajador) / $tarifaSalud), 0);
    }
    
}
