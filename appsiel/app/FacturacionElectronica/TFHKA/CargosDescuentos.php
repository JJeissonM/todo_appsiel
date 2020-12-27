<?php

namespace App\FacturacionElectronica\TFHKA;

class CargosDescuentos
{
  public $codigo;
  public $descripcion;
  public $extras = array();
  public $indicador; // Indicador de si es Cargo ”1” o Descuento “0”
  public $monto;
  public $montoBase;
  public $porcentaje;
  public $secuencia;
}