<?php

namespace App\FacturacionElectronica\TFHKA;

/*
	Posibles valores:
	“0” No se admiten adjuntos. Se genera XML y	representación Gráfica estándar por The Factory HKA
	“1” Admite archivos adjuntos. Se genera XML y representación Gráfica estándar por The Factory HKA
	“10” No se admiten adjuntos. Se genera solo XML sin	representación Gráfica estándar por The Factory HKA
	“11” Admite archivos adjuntos. Se genera solo XML sin representación gráfica estándar por The Factory HKA
*/
	
class adjunto
{

  public $archivo;
  public $email =array();
  public $enviar;
  public $formato;
  public $nombre;
  public $numeroDocumento;
  public $tipo;

}

