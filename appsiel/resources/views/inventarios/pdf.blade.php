<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">

	    <title>APPSIEL ..:: Software de Gestión Académica ::..</title>

	    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

	    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">

	    <style>

	        table {
			    width: 100%;
			    color:black;
			    font-family:arial;
			    margin-top: 2em;
			}
			    
		   thead {
		     background-color: #eeeeee;			     
		   }
		    
		   tbody {
		     background-color: #ffffff;     
		   }
		    
		   th,td {
		     padding: 3pt;
		   } 

		   table.tabla_pdf {
		      border-collapse: collapse;
		     border-top: 1px solid black;
		     border-bottom: 1px solid black;
		   }
		   .celda_right{
		    border-right: 1px solid black;
		   }
		   .celda_left{
		    border-left: 1px solid black;
		   }
		   
		   table.tabla_pdf th {
		     border-top: 1px solid black;
		    border-bottom: 1px solid black;
		   }
		   
		   table.tabla_pdf td {
		     border: 1px solid gray;
		     
		   }

			.page-break {
				page-break-after: always;
			}
	    </style>

	</head>
	<body id="app-layout">
		<?php echo $encabezado_transaccion;  ?>
		<?php echo $show_productos;  ?>
		<?php echo $firmas;  ?>
	</body>
</html>
