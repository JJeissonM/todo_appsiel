<?php

namespace App\FacturacionElectronica\TFHKA;

use App\FacturacionElectronica\TFHKA\Cliente;

// declaramos factura
class FacturaGeneral
{

  public $cantidadDecimales;
  public $cliente;	  
  public $consecutivoDocumento;
  public $documentosReferenciados = array();//es un array de tipo DocumentoReferenciado 
  public $detalleDeFactura = array();//es un array de tipo FacturaDetalle 
  public $fechaEmision;    
  public $impuestosGenerales = array();// almacenar todos los tipos de impuestos que estaran en la clase FacturaImpuestos
  public $impuestosTotales = array();
  public $mediosDePago = array(); 
  public $moneda;
  public $redondeoAplicado;
  public $rangoNumeracion;
  public $tipoDocumento;
  public $tipoOperacion;
  public $totalBaseImponible;
  public $totalBrutoConImpuesto;
  public $totalMonto;
  public $totalProductos;
  public $totalDescuentos;
  public $totalSinImpuestos;

  public function __construct(){
    $this->cliente = new Cliente();	
  }
}