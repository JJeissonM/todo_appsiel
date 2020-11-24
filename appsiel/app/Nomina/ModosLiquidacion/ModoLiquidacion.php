<?php

namespace App\Nomina\ModosLiquidacion;

use App\Nomina\ModosLiquidacion\Estrategias\TiempoLaborado;
use App\Nomina\ModosLiquidacion\Estrategias\AuxilioTransporte;
use App\Nomina\ModosLiquidacion\Estrategias\Cuota;
use App\Nomina\ModosLiquidacion\Estrategias\Prestamo;
use App\Nomina\ModosLiquidacion\Estrategias\SeguridadSocial;

// Facade
class ModoLiquidacion
{
	protected $modos_liquidacion_automaticos = [
													1 => TiempoLaborado::class,
													6 => AuxilioTransporte::class,
													3 => Cuota::class,
													4 => Prestamo::class,
													8 => SeguridadSocial::class
												];


	public function calcular( int $modo_liquidacion_id, LiquidacionConcepto $liquidacion )
	{
		$estrategia = new $this->modos_liquidacion_automaticos[$modo_liquidacion_id];
		$contexto = new Contexto( $estrategia );

		return $contexto->liquidar_concepto($liquidacion);
	}
}