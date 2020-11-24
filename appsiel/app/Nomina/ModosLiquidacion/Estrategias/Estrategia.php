<?php
namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

interface Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion); 
}