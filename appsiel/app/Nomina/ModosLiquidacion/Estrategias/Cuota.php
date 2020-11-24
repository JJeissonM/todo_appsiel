<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomCuota;

class Cuota implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$cuotas = NomCuota::where('estado', 'Activo')
                        ->where('core_tercero_id', $liquidacion->empleado->core_tercero_id)
                        ->where('nom_concepto_id', $liquidacion->concepto->id)
                        ->where('fecha_inicio', '<=', $liquidacion->documento_nomina->fecha)
                        ->get();

        $valores_cuotas = [];
        foreach( $cuotas as $cuota )
        {
            if ( $cuota->tope_maximo != '' ) // si la cuota maneja tope máximo 
            {
                // El valor_acumulado no se puede pasar del tope_maximo
                $saldo_pendiente = $cuota->tope_maximo - $cuota->valor_acumulado;
                
                if ( $saldo_pendiente < $cuota->valor_cuota )
                {
                    $cuota->valor_acumulado += $saldo_pendiente;
                    $valor_real_cuota = $saldo_pendiente;
                }else{
                    $cuota->valor_acumulado += $cuota->valor_cuota;
                    $valor_real_cuota = $cuota->valor_cuota;
                }

                if ( $cuota->valor_acumulado >= $cuota->tope_maximo ) 
                {
                    $cuota->estado = "Inactivo";
                }
            }else{
                $cuota->valor_acumulado += $cuota->valor_cuota;
                $valor_real_cuota = $cuota->valor_cuota;
            }
            
            $cuota->save();
            
            $valores_cuotas[] = $valor_real_cuota;
        }

        return $valores_cuotas;
	}
}