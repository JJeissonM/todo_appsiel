<?php

namespace App\FacturacionElectronica\TFHKA;

class Extras {
    public $controlInterno1;
    public $controlInterno2;
    public $nombre;
    public $pdf;
    public $valor;
    public $xml;
   }
   
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
   
   
   class Destinatario
   {
   	
		public $canalDeEntrega;
		public $email = array();
		public $nitProveedorReceptor;
		public $telefono;
	
   }
   
   class strings 
   {   		
   		public $string;	
   }
   
   class Tributos
   {
   		public $codigoImpuesto;	
      public $extras = array();
   }
   
   class Direccion
   {
   	public $aCuidadoDe;
		public $aLaAtencionDe;
		public $bloque;
		public $buzon;
		public $calle;
		public $calleAdicional;
		public $ciudad;
		public $codigoDepartamento;
		public $correccionHusoHorario;
		public $departamento;
		public $departamentoOrg;
		public $direccion;
		public $distrito;
		public $habitacion;
		public $lenguaje;
		public $municipio;
		public $nombreEdificio;
		public $numeroParcela;
		public $pais;
		public $piso;
		public $region;
		public $subDivision;
		public $ubicacion;
		public $zonaPostal;
   }
   
   class InformacionLegalCliente
   {
   		public $codigoEstablecimiento;
		public $nombreRegistroRUT;
		public $numeroIdentificacion;
		public $numeroIdentificacionDV;
		public $tipoIdentificacion;
   }
   
   class Obligaciones
   {
   		public $obligaciones;
		public $regimen;
   }
    
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
   
   
   class FacturaImpuestos
   {
		public $baseImponibleTOTALImp;
		public $codigoTOTALImp;
		public $controlInterno;
		public $porcentajeTOTALImp;
		public $unidadMedida;
		public $unidadMedidaTributo;
	    public $valorTOTALImp;
		public $valorTributoUnidad;
   }
   
   class ImpuestosTotales
   {
   		public $codigoTOTALImp;
		public $montoTotal;
   }
   
   class MediosDePago
   {
   		public $medioPago;
		public $metodoDePago;
		public $numeroDeReferencia;
   }

   class uploadAttachment {
       public $archivo;
       public $numeroDocumento;
       public $nombre;
       public $formato;
       public $tokenEmpresa;
       public $tokenPassword;
       public $tipo;
       public $enviar;
	}


  class Extensibles {

        public $controlInterno1;
        public $controlInterno2;
        public $nombre;
        public $valor;
  }

  class DocumentoReferenciado{

        public $codigoEstatusDocumento;
        public $codigoInterno;
        public $cufeDocReferenciado;
        public $descripcion = Array();
        public $fecha;
        public $fechaFinValidez;
        public $fechaInicioValidez;
        public $numeroDocumento;
        public $tipoCUFE;
        public $tipoDocumento;
        public $tipoDocumentoCodigo;
        

  }

  class CargarAdjuntos{

      public $tokenEmpresa;
      public $tokenPassword;
      public $adjunto;
  }

  class adjunto{

      public $archivo;
      public $email =array();
      public $enviar;
      public $formato;
      public $nombre;
      public $numeroDocumento;
      public $tipo;

  }


?>