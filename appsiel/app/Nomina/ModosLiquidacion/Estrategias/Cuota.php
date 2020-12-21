<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomCuota;

class Cuota implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$cuotas = NomCuota::where('estado', 'Activo')
                            ->where('core_tercero_id', $liquidacion['empleado']->core_tercero_id)
                            ->where('nom_concepto_id', $liquidacion['concepto']->id)
                            ->where('fecha_inicio', '<=', $liquidacion['documento_nomina']->fecha)
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

            $valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, $valor_real_cuota );
            
            $valores_cuotas[] = [
                                    'valor_devengo' => $valores->devengo,
                                    'valor_deduccion' => $valores->deduccion,
                                    'nom_cuota_id' => $cuota->id 
                                ];
        }

        return $valores_cuotas;
	}

    public function retirar(NomDocRegistro $registro)
    {
        $cuota = $registro->cuota;

        switch( $registro->concepto->naturaleza )
        {
            case 'devengo':
                $cuota->valor_acumulado -= $registro->valor_devengo;
                break;
            case 'deduccion':
                $cuota->valor_acumulado -= $registro->valor_deduccion;
                break;
            default:
                break;
        }

        $cuota->estado = "Activo";
        $cuota->save();

        $registro->delete();

        return 0;
    }
}