<?php

namespace App\FacturacionElectronica\TFHKA;

class FacturaDetalle 
{
  public $cantidadPorEmpaque;
  public $cantidadReal;
  public $cantidadRealUnidadMedida;
  public $cantidadUnidades;
  public $codigoProducto;	
  public $descripcion;
  public $descripcionTecnica;
  public $documentosReferenciados = array();//es un array de tipo DocumentoReferenciado 
  public $estandarCodigo;
  public $estandarCodigoProducto;
  public $impuestosDetalles = array();
  public $impuestosTotales = array();
  public $marca;
  public $muestraGratis;
  public $precioTotal;
  public $precioTotalSinImpuestos;
  public $precioVentaUnitario;
  public $secuencia;
  public $unidadMedida;
}