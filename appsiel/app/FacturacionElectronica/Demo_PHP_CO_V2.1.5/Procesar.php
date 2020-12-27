<?php

include_once "webservice_receptor.php";
error_reporting(E_ERROR);
$WebService = new WebService();
$options = array('exceptions' => true, 'trace' => true, 'location'=> WSDL);
$params;

$TokenEnterprise = $_POST["tokenEmpresa"];// //SE DEBE SETEAR ESTE VALOR (SUMINSTRADO POR TFHKA)
$TokenAutorizacion = $_POST["tokenPassword"];// //SE DEBE SETEAR ESTE VALOR (SUMINSTRADO POR TFHKA)
$enviarAdjunto = $_POST["Check"];

//var_dump($_FILES['archivo']);

if(isset($_POST['Enviar']) OR isset($_POST['EnviarNC']) OR isset($_POST['EnviarND'])){

    $factura = new FacturaGeneral();
    $factura->cliente = new Cliente();
	$factura->cantidadDecimales = "2";
    //$factura->cliente->actividadEconomicaCIIU = "0010"; (No obligatorio)
	//Datos del Cliente Completo
	$destinatarios = new Destinatario();	
		$destinatarios->canalDeEntrega = "0";
	
		$correodestinatario = new strings();	 
			$correodestinatario->string = $_POST["correo"];
	
		$destinatarios->email = $correodestinatario;
		$destinatarios->nitProveedorReceptor = "1";
		$destinatarios->telefono = "123456789";	
	
	$factura->cliente->destinatario[0] = $destinatarios;
	
	$tributos1 = new Tributos();	
		$tributos1->codigoImpuesto = "01";
		
	$extensible1 = new Extensibles();
		$extensible1->controlInterno1 = "";
		$extensible1->controlInterno2 = "";
		$extensible1->nombre = "";
		$extensible1->valor = "";
		
		$tributos1->extras[0] = $extensible1;
		
	$factura->cliente->detallesTributarios[0] = $tributos1;
	
	$DireccionCliente = new Direccion();	
		$DireccionCliente->aCuidadoDe = "";
		$DireccionCliente->aLaAtencionDe = "";
		$DireccionCliente->bloque = "";
		$DireccionCliente->buzon = "";
		$DireccionCliente->calle = "";
		$DireccionCliente->calleAdicional = "";
		$DireccionCliente->ciudad = "MANIZALES";
		$DireccionCliente->codigoDepartamento = "11";
		$DireccionCliente->correccionHusoHorario = "";
		$DireccionCliente->departamento = "Bogotá";
		$DireccionCliente->departamentoOrg = "";
		$DireccionCliente->direccion = "Direccion";
		$DireccionCliente->habitacion = "";
		$DireccionCliente->distrito = "";
		$DireccionCliente->lenguaje = "es";
		$DireccionCliente->municipio = "11001";
		$DireccionCliente->nombreEdificio = "";
		$DireccionCliente->numeroParcela = "";
		$DireccionCliente->pais = "CO";
		$DireccionCliente->piso = "";
		$DireccionCliente->region = "";
		$DireccionCliente->subDivision = "";
		$DireccionCliente->ubicacion = "";
		$DireccionCliente->zonaPostal = "110211";	

	$factura->cliente->direccionCliente = $DireccionCliente;
	
	$DireccionFiscal = new Direccion();	
		$DireccionFiscal->aCuidadoDe = "";
		$DireccionFiscal->aLaAtencionDe = "";
		$DireccionFiscal->bloque = "";
		$DireccionFiscal->buzon = "";
		$DireccionFiscal->calle = "";
		$DireccionFiscal->calleAdicional = "";
		$DireccionFiscal->ciudad = "MANIZALES";
		$DireccionFiscal->codigoDepartamento = "11";
		$DireccionFiscal->correccionHusoHorario = "";
		$DireccionFiscal->departamento = "Bogotá";
		$DireccionFiscal->departamentoOrg = "";
		$DireccionFiscal->direccion = "Direccion";
		$DireccionFiscal->habitacion = "";
		$DireccionFiscal->distrito = "";
		$DireccionFiscal->lenguaje = "es";
		$DireccionFiscal->municipio = "11001";
		$DireccionFiscal->nombreEdificio = "";
		$DireccionFiscal->numeroParcela = "";
		$DireccionFiscal->pais = "CO";
		$DireccionFiscal->piso = "";
		$DireccionFiscal->region = "";
		$DireccionFiscal->subDivision = "";
		$DireccionFiscal->ubicacion = "";
		$DireccionFiscal->zonaPostal = "110211";	
	
	$factura->cliente->direccionFiscal = $DireccionFiscal;
	
    $factura->cliente->email = $_POST["correo"];
	
	
	$InfoLegalCliente = New InformacionLegalCliente;
		$InfoLegalCliente->codigoEstablecimiento = "00001";
		$InfoLegalCliente->nombreRegistroRUT = "CONSORCIO ALIANZA SAN CRISTOBAL 4";
		$InfoLegalCliente->numeroIdentificacion = "901041710";
		$InfoLegalCliente->numeroIdentificacionDV = "5";
		$InfoLegalCliente->tipoIdentificacion = "31";	
	
	$factura->cliente->informacionLegalCliente = $InfoLegalCliente;
	
	
    $factura->cliente->nombreRazonSocial  = "The Factory HKA Colombia";
    $factura->cliente->notificar = "SI";
    $factura->cliente->numeroDocumento = "901041710";
	$factura->cliente->numeroIdentificacionDV = "5";
    
	$obligacionesCliente = new Obligaciones();
		$obligacionesCliente->obligaciones = "O-06";
		$obligacionesCliente->regimen = "04";
	
    $factura->cliente->responsabilidadesRut[0] = $obligacionesCliente;
	
    $factura->cliente->tipoIdentificacion = "31";
    $factura->cliente->tipoPersona = "1";
    //FIN cliente
	
	
	$factura->consecutivoDocumento = $_POST["consecutivoDocumento"];
    /**
    * Capturar el detalle de la factura
    */

    //Producto #1
    $factDetalle = new FacturaDetalle();
		$factDetalle->cantidadPorEmpaque = "1";
    	$factDetalle->cantidadReal = "1.00";
    	$factDetalle->cantidadRealUnidadMedida = "WSD"; // $fdetfact->codigo;
    	$factDetalle->cantidadUnidades = "1.00";
    	$factDetalle->codigoProducto = "P000001";
    	$factDetalle->descripcion = "Impresora HKA80";//Campo en blanco no requeridos
    	$factDetalle->descripcionTecnica = "Impresora térmica de punto de venta, ideal para puntos de venta con alto rendimiento";//Campo en blanco no requeridos
		$factDetalle->estandarCodigo = "999";
		$factDetalle->estandarCodigoProducto = "PHKA80";
		
		$impdet = new FacturaImpuestos;
			$impdet->baseImponibleTOTALImp = "100.00";
			$impdet->codigoTOTALImp = "01";
			$impdet->controlInterno = "";
			$impdet->porcentajeTOTALImp = "19.00";
			$impdet->unidadMedida = "";
			$impdet->unidadMedidaTributo = "";
			$impdet->valorTOTALImp = "19.00";
			$impdet->valorTributoUnidad = "";
			
		$factDetalle->impuestosDetalles[0] = $impdet;
		
		
		$impTot = new ImpuestosTotales;
			$impTot->codigoTOTALImp = "01";
			$impTot->montoTotal = "19.00";
		
		$factDetalle->impuestosTotales[0] = $impTot;
		
		$factDetalle->marca = "HKA";
		$factDetalle->muestraGratis = "0";
		$factDetalle->precioTotal = "119.00";
		$factDetalle->precioTotalSinImpuestos = "100.00";
		$factDetalle->precioVentaUnitario = "100.00";
		$factDetalle->secuencia = "1";
		$factDetalle->unidadMedida = "WSD";		

        $factura->detalleDeFactura [0] = $factDetalle; //Se registra el primer item en el objeto factura
		
		$factDetalle1 = new FacturaDetalle();
		$factDetalle1->cantidadPorEmpaque = "1";
    	$factDetalle1->cantidadReal = "1.00";
    	$factDetalle1->cantidadRealUnidadMedida = "WSD"; // $fdetfact->codigo;
    	$factDetalle1->cantidadUnidades = "1.00";
    	$factDetalle1->codigoProducto = "P000003";
    	$factDetalle1->descripcion = "Impresora SRP-812";//Campo en blanco no requeridos
    	$factDetalle1->descripcionTecnica = "Impresora térmica de punto de venta, ideal para puntos de venta con alto rendimiento";//Campo en blanco no requeridos
		$factDetalle1->estandarCodigo = "999";
		$factDetalle1->estandarCodigoProducto = "SRP-812";
		
		$impdet1 = new FacturaImpuestos;
			$impdet1->baseImponibleTOTALImp = "200.00";
			$impdet1->codigoTOTALImp = "01";
			$impdet1->controlInterno = "";
			$impdet1->porcentajeTOTALImp = "19.00";
			$impdet1->unidadMedida = "";
			$impdet1->unidadMedidaTributo = "";
			$impdet1->valorTOTALImp = "38.00";
			$impdet1->valorTributoUnidad = "";
			
		$factDetalle1->impuestosDetalles[0] = $impdet1;
		
		
		$impTot1 = new ImpuestosTotales;
			$impTot1->codigoTOTALImp = "01";
			$impTot1->montoTotal = "38.00";
		
		$factDetalle1->impuestosTotales[0] = $impTot1;
		
		$factDetalle1->marca = "HKA";
		$factDetalle1->muestraGratis = "0";
		$factDetalle1->precioTotal = "238.00";
		$factDetalle1->precioTotalSinImpuestos = "200.00";
		$factDetalle1->precioVentaUnitario = "200.00";
		$factDetalle1->secuencia = "2";
		$factDetalle1->unidadMedida = "WSD";	

		$factura->detalleDeFactura [1] = $factDetalle1; // segundo item

		$factura->fechaEmision = $_POST['Fecha'] ." 00:00:00";
    //fin de detalle
	
	
    //IMPUESTOS GENERALES1
    		
	$objImpGen = new FacturaImpuestos;
			$objImpGen->baseImponibleTOTALImp = "300.00";
			$objImpGen->codigoTOTALImp = "01";
			$objImpGen->controlInterno = "";
			$objImpGen->porcentajeTOTALImp = "19.00";
			$objImpGen->unidadMedida = "";
			$objImpGen->unidadMedidaTributo = "";
			$objImpGen->valorTOTALImp = "57.00";
			$objImpGen->valorTributoUnidad = "0.00";
   
   	$factura->impuestosGenerales[0] = $objImpGen;
	
	$impTot2 = new ImpuestosTotales;
			$impTot2->codigoTOTALImp = "01";
			$impTot2->montoTotal = "57.00";
			
	$factura->impuestosTotales[0] = $impTot2;
	
	$pagos = new MediosDePago();
		$pagos->medioPago = "10";
		$pagos->metodoDePago = "1";
		$pagos->numeroDeReferencia = "01";	

    $factura->mediosDePago[0] = $pagos;
    $factura->moneda = "COP";
	$factura->redondeoAplicado = "0.00"	;
    $factura->rangoNumeracion = $_POST["RangoNumeracion"]; // //SE DEBE SETEAR ESTE VALOR (SUMINSTRADO POR TFHKA EN PRUEBAS, POR LA DIAN EN PRODUCCION)
    
    $factura->tipoOperacion = "10";
    $factura->totalBaseImponible = "300.00";//$factura->impuestosGenerales["baseImponibleTOTALImp"];
    $factura->totalBrutoConImpuesto = "357.00";
    $factura->totalMonto ="357.00";
    $factura->totalProductos="2";
	$factura->totalSinImpuestos="300.00";


 //Facturas
	if(isset($_POST['Enviar'])){

		$factura->tipoDocumento="01";

	}elseif(isset($_POST['EnviarNC'])){ //Notas de credito
		$factura->tipoDocumento="91";


		$DocRef = new DocumentoReferenciado();

			$DocRef->codigoEstatusDocumento = '2';
			$DocRef->codigoInterno = '4';
			$DocRef->cufeDocReferenciado = "d04f549ebdda4095b6cbc2595f0b36ddb97e39b7b405e3120918844043a070d4911b099a953880be71210f6dc281b0e4" ; //$_POST['CUFE'];
			//$DocRef->descripcion[0] = "Prueba NC Campos Minimos con Anulación de factura electrónica";
			$DocRef->numeroDocumento= "PABC14";
		
	    $factura->documentosReferenciados[0] =$DocRef;

	    $DocRef1 = new DocumentoReferenciado();

			$DocRef1->codigoInterno = '5';
			$DocRef1->cufeDocReferenciado = "d04f549ebdda4095b6cbc2595f0b36ddb97e39b7b405e3120918844043a070d4911b099a953880be71210f6dc281b0e4";// $_POST['CUFE'];
			$DocRef1->fecha = "2019-07-19";
			$DocRef1->numeroDocumento= "PABC14";

		$factura->documentosReferenciados[1] =$DocRef1;

	}elseif(isset($_POST['EnviarND'])){ //Notas de debito
		$factura->tipoDocumento="92";

		$DocRef2 = new DocumentoReferenciado();

			$DocRef2->codigoEstatusDocumento = '2';
			$DocRef2->codigoInterno = '4';
			$DocRef2->cufeDocReferenciado = "d04f549ebdda4095b6cbc2595f0b36ddb97e39b7b405e3120918844043a070d4911b099a953880be71210f6dc281b0e4" ; //$_POST['CUFE'];
			//$DocRef->string[0] = "Prueba NC Campos Minimos con Anulación de factura electrónica";
			$DocRef2->numeroDocumento= "PABC14";
		
	    $factura->documentosReferenciados[0] =$DocRef2;

	    $DocRef3 = new DocumentoReferenciado();

			$DocRef3->codigoInterno = '5';
			$DocRef3->cufeDocReferenciado = "d04f549ebdda4095b6cbc2595f0b36ddb97e39b7b405e3120918844043a070d4911b099a953880be71210f6dc281b0e4";// $_POST['CUFE'];
			$DocRef3->fecha = "2019-07-19";
			$DocRef3->numeroDocumento= "PABC14";

		$factura->documentosReferenciados[1] =$DocRef3;

	}

	if ($enviarAdjunto == "TRUE"){

		$adjuntos="1";

	}else{

		$adjuntos="0";
		}

	 
	 $params = array(
         'tokenEmpresa' =>  $TokenEnterprise,
         'tokenPassword' =>$TokenAutorizacion,
         'factura' => $factura ,
         'adjuntos' => $adjuntos);
	 //Enviar Objeto Factura
	 $resultado = $WebService->enviar(WSDL,$options,$params);
	 //capturar codigo de respuesta del WS del Autofact para dar respuesta al usuario
	 
	 echo "<h1>Resultado de la Emisión</h1></br>";
	 
	if($resultado["codigo"]==200){
		
			print_r("Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Consecutivo:  " .$resultado["consecutivoDocumento"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Hash:  " .$resultado["hash"] ."</br>Reglas de validación DIAN:  " .$resultado["reglasValidacionDIAN"] ."</br>Resultado:  " .$resultado["resultado"] ."</br>Tipo de CUFE:  " .$resultado["tipoCufe"] ."</br>Mensaje Validación:  " );

			$max = sizeof($resultado["mensajesValidacion"]->string);
			 for($i = 0; $i < $max;$i++){

			 	print_r("</br>" .$resultado["mensajesValidacion"]->string[$i]  );
			 }
	
	// ENVIO DE ADJUNTOS

		if ($enviarAdjunto == "TRUE"){

				$handle = fopen($_FILES['archivo']["tmp_name"],"r");
				$conten = fread($handle,filesize($_FILES['archivo']["tmp_name"]));
        		

        		$nombreformato = explode(".", $_FILES['archivo']['name']);
        		$tm = sizeof($nombreformato);
        		

        		$Adjunto = new adjunto();
				$Adjunto->archivo= $conten;

				$correo = new strings();
				$correo = $_POST["correo"];

      			$Adjunto->email[0] = $correo;
      			$Adjunto->enviar = "1";
     			$Adjunto->formato = $nombreformato[$tm-1];
      			$Adjunto->nombre= $nombreformato[0];
      			$Adjunto->numeroDocumento = $resultado["consecutivoDocumento"];
      			$Adjunto->tipo = "2";

      			$params = new CargarAdjuntos();
         			$params->tokenEmpresa =  $TokenEnterprise;
         			$params->tokenPassword = $TokenAutorizacion;
         			$params->adjunto = $Adjunto;

				$options = array('exceptions' => true, 'trace' => true);
      			$resultado = $WebService->CargarAdjuntos(WSANEXO,$options,$params);

      			echo "<h2>Resultado del Envío de Adjuntos</h2></br>";

      			print_r("Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Resultado:  " .$resultado["resultado"]);

			}

	}else{
			$max = sizeof($resultado["mensajesValidacion"]->string);

			print_r("Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Mensaje Validación:  " );
			 for($i = 0; $i < $max;$i++){

			 	print_r("</br>" .$resultado["mensajesValidacion"]->string[$i]  );
			 }
			 //."</br>Resultado:  " .$resultado["resultado"] );
		
	}
	
    exit();

}elseif(isset($_POST['Folios'])){

	$params = array(
         'tokenEmpresa' =>  $TokenEnterprise,
         'tokenPassword' =>$TokenAutorizacion);
	 //Enviar Objeto Factura
	 $resultado = $WebService->foliosrestantes(WSDL,$options,$params);

	echo "<h1>Resultado de la consulta de Folios</h1></br>";

	 print_r("Código: " .$resultado["codigo"] ."</br>Folios Restantes:  " .$resultado["foliosRestantes"] ."</br>Resultado:  " .$resultado["resultado"] ."</br>Mensaje:  " .$resultado["mensaje"] );

}elseif(isset($_POST['EstadoDoc'])){

	$params = array(
         'tokenEmpresa' =>  $TokenEnterprise,
         'tokenPassword' =>$TokenAutorizacion,
     	 'documento' => $_POST["ConsultaDoc"]);
		 
	 //Enviar Objeto Factura
	 $resultado = $WebService->getEstadoDocumento(WSDL,$options,$params);

	echo "<h1>Resultado de la consulta de Documento</h1></br>";

	 print_r("Código: " .$resultado["codigo"] ."</br>Aceptación Fisica:  " .$resultado["aceptacionFisica"] ."</br>Ambiente:  " .$resultado["ambiente"] ."</br>Consecutivo:  " .$resultado["consecutivo"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Descripcion Documento:  " .$resultado["descripcionDocumento"] ."</br>Fecha Documento:  " .$resultado["fechaDocumento"] ."</br>Resultado:  " .$resultado["resultado"] );

}

?>