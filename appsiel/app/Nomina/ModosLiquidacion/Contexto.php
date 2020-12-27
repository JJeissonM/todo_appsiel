<?php
namespace App\Nomina\ModosLiquidacion;

//use App\Nomina\ModosLiquidacion\Estrategia;

class Contexto
{
	private $estrategia;

	public function __construct($estrategia)
	{
		$this->estrategia = $estrategia;
	}

	public function liquidar_concepto($modo_liquidacion)
	{
		return $this->estrategia->calcular($modo_liquidacion);
	}

	public function retirar_registro_concepto( $registro )
	{
		return $this->estrategia->retirar( $registro );
	}
}