<?php
namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;

interface Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion); 
	public function retirar(NomDocRegistro $registro); 
}