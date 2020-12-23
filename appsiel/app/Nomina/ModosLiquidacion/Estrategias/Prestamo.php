<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomPrestamo;

class Prestamo implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$prestamos = NomPrestamo::where( [
                                            ['estado', '=', 'Activo'],
                                            ['core_tercero_id','=', $liquidacion['empleado']->core_tercero_id],
                                            ['nom_concepto_id','=', $liquidacion['concepto']->id],
                                            ['fecha_inicio', '<=', $liquidacion['documento_nomina']->fecha]
                                        ] )
                                    ->get();

        $valores_prestamos = [];
        foreach( $prestamos as $prestamo )
        {
            // El valor_acumulado no se puede pasar del valor_prestamo
            $saldo_pendiente = $prestamo->valor_prestamo - $prestamo->valor_acumulado;
                
            if ( $saldo_pendiente < $prestamo->valor_cuota )
            {
                $prestamo->valor_acumulado += $saldo_pendiente;
                $valor_real_prestamo = $saldo_pendiente;
            }else{
                $prestamo->valor_acumulado += $prestamo->valor_cuota;
                $valor_real_prestamo = $prestamo->valor_cuota;
            }

            if ( $prestamo->valor_acumulado >= $prestamo->valor_prestamo ) 
            {
                $prestamo->estado = "Inactivo";
            }
            
            $prestamo->save();

            $valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, $valor_real_prestamo );
            
            $valores_prestamos[] = [
                                    'valor_devengo' => $valores->devengo,
                                    'valor_deduccion' => $valores->deduccion,
                                    'nom_prestamo_id' => $prestamo->id 
                                ];
        }

        return $valores_prestamos;
	}

    public function retirar(NomDocRegistro $registro)
    {
        $prestamo = $registro->prestamo;
        
        switch( $registro->concepto->naturaleza )
        {
            case 'devengo':
                $prestamo->valor_acumulado -= $registro->valor_devengo;
                break;
            case 'deduccion':
                $prestamo->valor_acumulado -= $registro->valor_deduccion;
                break;
            default:
                break;
        }

        $prestamo->estado = "Activo";
        $prestamo->save();

        $registro->delete();
    }
}