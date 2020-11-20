<?php

namespace App\FacturacionElectronica\TFHKA;

use App\FacturacionElectronica\TFHKA\adjunto;
use App\FacturacionElectronica\TFHKA\CargarAdjuntos;
use App\FacturacionElectronica\TFHKA\Cliente;
use App\FacturacionElectronica\TFHKA\Destinatario;
use App\FacturacionElectronica\TFHKA\Direccion;
use App\FacturacionElectronica\TFHKA\DocumentoReferenciado;
use App\FacturacionElectronica\TFHKA\Extensibles;
use App\FacturacionElectronica\TFHKA\Extras;
use App\FacturacionElectronica\TFHKA\FacturaDetalle;
use App\FacturacionElectronica\TFHKA\FacturaGeneral;
use App\FacturacionElectronica\TFHKA\FacturaImpuestos;
use App\FacturacionElectronica\TFHKA\ImpuestosTotales;
use App\FacturacionElectronica\TFHKA\InformacionLegalCliente;
use App\FacturacionElectronica\TFHKA\MediosDePago;
use App\FacturacionElectronica\TFHKA\Obligaciones;
use App\FacturacionElectronica\TFHKA\strings;
use App\FacturacionElectronica\TFHKA\Tributos;
use App\FacturacionElectronica\TFHKA\uploadAttachment;
use App\FacturacionElectronica\TFHKA\WebService;

class DocumentoElectronico
{
	public $WebService;
	public $options;
	//protected $params;
	
	function __construct()
	{
		$this->WebService = new WebService();
		$this->options = array( 'exceptions' => true, 'trace' => true, 'location'=> config('facturacion_electronica.WSDL') );
	}

	public function preparar_direccion( $tercero )
	{
		$direccion = new Direccion();	
		$direccion->aCuidadoDe = "";
		$direccion->aLaAtencionDe = "";
		$direccion->bloque = "";
		$direccion->buzon = "";
		$direccion->calle = "";
		$direccion->calleAdicional = "";
		$direccion->ciudad = $tercero->ciudad->descripcion;
		$direccion->codigoDepartamento = substr($tercero->codigo_ciudad, 3, 2);
		$direccion->correccionHusoHorario = "";
		$direccion->departamento = $tercero->departamento()->descripcion;
		$direccion->departamentoOrg = "";
		$direccion->direccion = $tercero->direccion1;
		$direccion->habitacion = "";
		$direccion->distrito = "";
		$direccion->lenguaje = "es";
		$direccion->municipio = substr($tercero->codigo_ciudad, 3);
		$direccion->nombreEdificio = "";
		$direccion->numeroParcela = "";
		$direccion->pais = "CO";
		$direccion->piso = "";
		$direccion->region = "";
		$direccion->subDivision = "";
		$direccion->ubicacion = "";
		$direccion->zonaPostal = $tercero->codigo_postal;

		return $direccion;
	}

	public function preparar_destinatarios( $tercero )
	{
		$destinatario = new Destinatario();
		$destinatario->canalDeEntrega = "0";
	
		$correodestinatario = new strings();
		$correodestinatario->string = $tercero->email;
	
		$destinatario->email = $correodestinatario;
		$destinatario->nitProveedorReceptor = $tercero->numero_identificacion;
		$destinatario->telefono = $tercero->telefono1;

		return $destinatario;			
	}

	public function preparar_tributos()
	{
		$tributos1 = new Tributos();	
			$tributos1->codigoImpuesto = "01";
			
		$extensible1 = new Extensibles();
			$extensible1->controlInterno1 = "";
			$extensible1->controlInterno2 = "";
			$extensible1->nombre = "";
			$extensible1->valor = "";
			
			$tributos1->extras[0] = $extensible1;

		return $tributos1;
	}

	public function preparar_informacion_legal( $tercero )
	{
		$InfoLegalCliente = New InformacionLegalCliente;
		$InfoLegalCliente->codigoEstablecimiento = "";
		$InfoLegalCliente->nombreRegistroRUT = $tercero->descripcion;
		$InfoLegalCliente->numeroIdentificacion = $tercero->numero_identificacion;
		$InfoLegalCliente->numeroIdentificacionDV = $tercero->digito_verificacion;
		$InfoLegalCliente->tipoIdentificacion = $tercero->id_tipo_documento_id;
		return $InfoLegalCliente;
	}

	public function preparar_responsabilidades_rut( $tercero )
	{
		$obligacionesCliente = new Obligaciones();
		$obligacionesCliente->obligaciones = "O-06";
		$obligacionesCliente->regimen = "04";
		return $obligacionesCliente;
	}

	public function preparar_objeto_cliente( $datos_cliente )
	{
		$cliente = new Cliente();

		$cliente->destinatario[0] = $this->preparar_destinatarios( $datos_cliente->tercero );

		$cliente->detallesTributarios[0] = $this->preparar_tributos();

		$cliente->direccionCliente = $this->preparar_direccion( $datos_cliente->tercero );	
		
		$cliente->direccionFiscal = $this->preparar_direccion( $datos_cliente->tercero );
		
		$cliente->informacionLegalCliente = $this->preparar_informacion_legal( $datos_cliente->tercero );
		
	    $cliente->responsabilidadesRut[0] = $this->preparar_responsabilidades_rut( $datos_cliente->tercero );
		
	    $cliente->email = $datos_cliente->tercero->email;
	    $cliente->nombreRazonSocial  = $datos_cliente->tercero->descripcion;
	    $cliente->notificar = "SI";
	    $cliente->numeroDocumento = $datos_cliente->tercero->numero_identificacion;
		$cliente->numeroIdentificacionDV = $datos_cliente->tercero->digito_verificacion;		
	    $cliente->tipoIdentificacion = $datos_cliente->tercero->id_tipo_documento_id;
	    $cliente->tipoPersona = "1"; // Persona jurídica
	    if ( $datos_cliente->tercero->tipo == 'Persona natural' )
	    {
	    	$datos_cliente->tipoPersona = "2"; // Persona natural
	    }

	    return $cliente;
	}

	public function preparar_detalle_impuestos_linea_registro( $linea )
	{
		$impdet = new FacturaImpuestos;
		$impdet->baseImponibleTOTALImp = number_format( $linea->base_impuesto_total, 2, '.', '');
		$impdet->codigoTOTALImp = "01"; // IVA
		$impdet->controlInterno = "";
		$impdet->porcentajeTOTALImp = number_format( $linea->tasa_impuesto, 2, '.', '');
		$impdet->unidadMedida = "";
		$impdet->unidadMedidaTributo = "";
		$impdet->valorTOTALImp = number_format( ( $linea->base_impuesto_total * $linea->tasa_impuesto / 100 ), 2, '.', '');
		$impdet->valorTributoUnidad = "";
		return $impdet;
	}

	public function preparar_detalle_impuestos_totales_linea_registro( $linea )
	{
		$impTot = new ImpuestosTotales;
		$impTot->codigoTOTALImp = "01"; // IVA
		$impTot->montoTotal = number_format( ( $linea->base_impuesto_total * $linea->tasa_impuesto / 100 ), 2, '.', '');
		return $impTot;
	}

	public function preparar_cargos_descuentos( $linea, $secuencia )
	{
		$cargoDescuento = new CargosDescuentos;
		$cargoDescuento->codigo = '11'; // Otros descuentos
		$cargoDescuento->descripcion = 'Descuento comercial';
		$cargoDescuento->extras = [];
		$cargoDescuento->indicador = '0';
		$cargoDescuento->monto = number_format( $linea->valor_total_descuento, 2, '.', '');
		$cargoDescuento->montoBase = abs( number_format( ( $linea->precio_unitario * $linea->cantidad ), 2, '.', '') );
		$cargoDescuento->porcentaje = number_format( $linea->tasa_descuento, 2, '.', '');
		$cargoDescuento->secuencia = $secuencia;
		return $cargoDescuento;
	}

	public function preparar_linea_detalle_factura( $linea, $secuencia_anterior )
	{
	    $factDetalle = new FacturaDetalle();

		$factDetalle->cantidadPorEmpaque = "1";
    	$factDetalle->cantidadReal = abs( $linea->cantidad );
    	$factDetalle->cantidadRealUnidadMedida = "WSD"; // Código estándar
		$factDetalle->unidadMedida = "WSD";
    	$factDetalle->cantidadUnidades = abs( $linea->cantidad );

    	$factDetalle->cargosDescuentos[0] = $this->preparar_cargos_descuentos( $linea, $secuencia_anterior + 1 );

    	$codigoProducto = $linea->item->id;
    	if ( $linea->item->referencia != '' )
    	{
    		$codigoProducto = $linea->item->referencia;
    	}

    	$factDetalle->codigoProducto = $codigoProducto;

    	$factDetalle->descripcion = $linea->item->descripcion;
    	$factDetalle->descripcionTecnica = "";
		$factDetalle->estandarCodigo = "999"; // Estándar de adopción del contribuyente
		$factDetalle->estandarCodigoProducto = $codigoProducto;

		$factDetalle->impuestosDetalles[0] = $this->preparar_detalle_impuestos_linea_registro( $linea );

		$factDetalle->impuestosTotales[0] = $this->preparar_detalle_impuestos_totales_linea_registro( $linea );

		$factDetalle->marca = "";
		$factDetalle->muestraGratis = "0";

		$precioTotal = $linea->cantidad * $linea->base_impuesto + $linea->valor_impuesto_total();
		$factDetalle->precioTotal = abs( number_format($precioTotal, 2, '.', '') );
		$factDetalle->precioTotalSinImpuestos = abs( number_format($precioTotal - $linea->valor_impuesto_total(), 2, '.', '') );
		$factDetalle->precioVentaUnitario = number_format($linea->base_impuesto, 2, '.', '');
		$factDetalle->secuencia = $secuencia_anterior + 1;

	    return $factDetalle;
	}

	public function preparar_impuesto_general( $lineas_registros, $tasa_impuesto )
	{
		$baseImponibleTOTALImp = $lineas_registros->where('tasa_impuesto', $tasa_impuesto)->sum('base_impuesto_total');
		$valorTOTALImp = $baseImponibleTOTALImp * ( $tasa_impuesto / 100 );

		$objImpGen = new FacturaImpuestos;
		$objImpGen->baseImponibleTOTALImp = number_format($baseImponibleTOTALImp, 2, '.', '');
		$objImpGen->codigoTOTALImp = "01"; // IVA
		$objImpGen->controlInterno = "";
		$objImpGen->porcentajeTOTALImp = number_format($tasa_impuesto,2, '.', '');
		$objImpGen->unidadMedida = "";
		$objImpGen->unidadMedidaTributo = "";
		$objImpGen->valorTOTALImp = number_format($valorTOTALImp, 2, '.', '');
		$objImpGen->valorTributoUnidad = "0.00";

		return $objImpGen;		
	}

	public function preparar_medios_pago( $encabezado_factura )
	{
		$pagos = new MediosDePago();
		$pagos->medioPago = "1"; // Instrumento no definido
		
		$metodoDePago = "1"; // Contado
		if ( $encabezado_factura->forma_pago == 'credito' )
		{
			$metodoDePago = "2"; // Crédito
			$pagos->fechaDeVencimiento = $encabezado_factura->fecha_vencimiento;
		}
		$pagos->metodoDePago = $metodoDePago;
		$pagos->numeroDeReferencia = "";

		return $pagos;
	}

	public function preparar_objeto_documento( $encabezado_factura )
	{
		$factura = new FacturaGeneral();

	    $factura->cliente = $this->preparar_objeto_cliente( $encabezado_factura->cliente );
		
		$factura->cantidadDecimales = "2";

		$resolucion_facturacion = $encabezado_factura->tipo_documento_app->resolucion_facturacion->where('estado','Activo')->first();

		$factura->rangoNumeracion = $resolucion_facturacion->prefijo . '-' . $resolucion_facturacion->numero_fact_inicial; // Formato: Pefijo-campoDesde
		$consecutivo = '';
		switch ( config('facturacion_electronica.modalidad_asignada') )
		{
			case '1': // Automática
				# code...
				break;
			
			case '2': // Manual Con Prefijo
				$consecutivo = $resolucion_facturacion->prefijo . $encabezado_factura->consecutivo;
				break;
			
			case '3': // Manual Sin Prefijo
				$consecutivo = $encabezado_factura->consecutivo;
				break;
			
			case '4': // Manual Contingencia
				# code...
				break;
			
			default:
				# code...
				break;
		}

		$factura->consecutivoDocumento = $consecutivo;

		$l = 0;
		$montoTotalImpuestos = 0;
		$totalBaseImponible = 0;
		$totalSinImpuestos = 0;
		$precioTotal = 0;
		$totalDescuentos = 0;
		foreach ($encabezado_factura->lineas_registros as $linea )
		{
			$factura->detalleDeFactura[$l] = $this->preparar_linea_detalle_factura( $linea, $l );

			// Sumatoria de totales
			$montoTotalImpuestos += abs( $linea->valor_impuesto_total() );
			$totalBaseImponible += $linea->base_impuesto_total;
			$totalSinImpuestos += $linea->base_impuesto_total;
			$precioTotal += abs( $linea->precio_total );
			$totalDescuentos += $linea->valor_total_descuento;

			$l++;
		}

		$factura->fechaEmision = $encabezado_factura->fecha . " 00:00:00";
		

		// IMPUESTOS GENERALES (Un registro por cada tasa de impuesto)
		$unique = $encabezado_factura->lineas_registros->unique('tasa_impuesto');
		
		$tipos_impuestos = $unique->pluck('tasa_impuesto');
		$ig = 0;
		foreach ($tipos_impuestos as $tasa_impuesto)
		{
			$factura->impuestosGenerales[$ig] = $this->preparar_impuesto_general( $encabezado_factura->lineas_registros, $tasa_impuesto );
			$ig++;
		}
		
		$impTot2 = new ImpuestosTotales;
		$impTot2->codigoTOTALImp = "01"; // IVA
		$impTot2->montoTotal = number_format( $montoTotalImpuestos, 2, '.', '');
				
		$factura->impuestosTotales[0] = $impTot2;

	    $factura->mediosDePago[0] = $this->preparar_medios_pago( $encabezado_factura );

	    $factura->moneda = "COP";
		$factura->redondeoAplicado = "100.00"	;
	    
	    $factura->totalBaseImponible = number_format( $totalBaseImponible, 2, '.', '');
	    $factura->totalBrutoConImpuesto =  number_format( $totalSinImpuestos + $montoTotalImpuestos, 2, '.', '');
	    $factura->totalMonto = number_format( $precioTotal, 2, '.', '');
	    $factura->totalProductos = count( $encabezado_factura->lineas_registros->toArray() );
	    //$factura->totalCargosAplicados = '0.00';
	    $factura->totalDescuentos = number_format( $totalDescuentos, 2, '.', '');
		$factura->totalSinImpuestos = number_format( $totalSinImpuestos, 2, '.', '');

		return $factura;
	}
}