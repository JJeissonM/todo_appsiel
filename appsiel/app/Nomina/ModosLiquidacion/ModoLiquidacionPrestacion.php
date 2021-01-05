<?php

namespace App\Nomina\ModosLiquidacion;

use App\Nomina\ModosLiquidacion\PrestacionesSociales\Vacaciones;
use App\Nomina\ModosLiquidacion\PrestacionesSociales\PrimaServicios;
use App\Nomina\ModosLiquidacion\PrestacionesSociales\Cesantias;
use App\Nomina\ModosLiquidacion\PrestacionesSociales\InteresesCesantias;

use App\Nomina\NomDocRegistro;

// Facade
class ModoLiquidacionPrestacion
{
	protected $modos_liquidacion = [
										'vacaciones' => Vacaciones::class,
										'prima_legal' => PrimaServicios::class,
										'cesantias' => Cesantias::class,
										'intereses_cesantias' => InteresesCesantias::class,
									];


	public function calcular( string $prestacion, LiquidacionPrestacionSocial $liquidacion )
	{
		$estrategia = new $this->modos_liquidacion[$prestacion];
		$contexto = new Contexto( $estrategia );

		return $contexto->liquidar_concepto($liquidacion);
	}

	public function retirar( string $prestacion, NomDocRegistro $registro )
	{
		$estrategia = new $this->modos_liquidacion[$prestacion];
		$contexto = new Contexto( $estrategia );

		return $contexto->retirar_registro_concepto( $registro );
	}
}