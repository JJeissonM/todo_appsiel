<?php

namespace App\FacturacionElectronica\TFHKA;

// declaramos la clase 
class Cliente
{
  public $actividadEconomicaCIIU;
  public $destinatario = array();
  public $detallesTributarios = array();
  public $direccionCliente;
  public $direccionFiscal;
  public $email;
  public $informacionLegalCliente;
  public $nombreRazonSocial;
  public $notificar;
  public $numeroDocumento;
  public $numeroIdentificacionDV;
  public $responsabilidadesRut = array();
  public $tipoIdentificacion;
  public $tipoPersona;       
}