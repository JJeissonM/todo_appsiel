<?php
namespace App\Nomina\ModosLiquidacion\PrestacionesSociales;

use App\Nomina\ModosLiquidacion\LiquidacionPrestacionSocial;
use App\Nomina\NomDocRegistro;

interface Estrategia
{
	public function calcular(LiquidacionPrestacionSocial $liquidacion); 
	public function retirar(NomDocRegistro $registro); 
}