<?php

	$datos_envio = (object)[ 
							'tokenEmpresa' => '3aee7876b2820e85141ba9ad299b79a22e218a2e',  
							'tokenPassword' => 'ddefd3c0b98ee4551c24249816c1f3c8a71acf33',  
							'RangoNumeracion' => 'FPRU-1',  
							'consecutivoDocumento' => '',  
							'Fecha' => '',  
							'correo' => '',  
							'Check' => '',  
							'file' => '',  
							'tokenEmpresa' => '',  
							'tokenEmpresa' => '',  
							'tokenEmpresa' => '',  
							'tokenEmpresa' => '',  
							'tokenEmpresa' => '' ];

?>

<html>
	<head></head>
	<body>

		<h1>DEMO PHP FEL CO 2.1</h1></br>
		
		<form method="post" action = "Procesar.php" enctype="multipart/form-data">
			
			
			Token Empresa: <input type="text" name="tokenEmpresa" value = ""></br></br>

			Token Password: <input type="text" name="tokenPassword" value = ""></br></br>

			Rango de Numeración: <input type="text" name="RangoNumeracion" value = ""></br></br>

			Número de Consecutivo: <input type="text" name="consecutivoDocumento" value = "1"></br></br>

			Fecha de emisión: <input type="date" name="Fecha" value="<?php echo date('Y-m-d') ?>" ></br></br>	

			Correo Adquiriente: <input type="text" name="correo" value = "ing.adalberto"></br></br>

			Enviar Adjunto: <input type="checkbox" name="Check" value= "TRUE"></br>

			Seleccionar archivo adjunto: <input type="file" name="archivo" id ="archivo" ></br></br>	

			<input type="submit" name="Enviar" value="Enviar factura">

			<input type="submit" name="EnviarNC" value="Enviar Nota Crédito">

			<input type="submit" name="EnviarND" value="Enviar Nota Débito"></br></br></br>

			<input type="submit" name="Folios" value="Consultar Folios"></br></br></br>

			Documento a consultar: <input type="text" name="ConsultaDoc" value = ""></br></br>

			<input type="submit" name="EstadoDoc" value="Estado Documento" ></br></br>

			</form>
			

	
	</body>

</html>		