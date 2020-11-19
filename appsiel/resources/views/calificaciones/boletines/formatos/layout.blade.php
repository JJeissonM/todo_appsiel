<!DOCTYPE html>
<html>
<head>
    <title> Boletines curso {{ $curso->descripcion }} </title>
    <style type="text/css">
        
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{$tam_letra}}mm;
        }

        .page-break {
            page-break-after: always;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table.table-bordered, .table-bordered>tbody>tr>td{
            border: 1px solid gray;
        }

		img {
			padding-left:30px;
		}

		th {
			background-color: #E0E0E0;
			border: 1px solid;
		}

		ul{
			padding:0px;
			margin:0px;
		}

		li{
			list-style-type: none;
		}

		table.banner{
	        font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
	        font-style: italic;
	        font-size: 16px;
	        border: 1px solid gray;
	        padding-top: -30px;
	    }

		table.encabezado{
			border: 1px solid gray;
			padding-top: -30px;
		}

		table.encabezado>tr>td{
			font-size: {{$tam_letra+2}}mm;
		}

		table.contenido>tr>th{
			font-size: {{$tam_letra}}mm;
		}
		
		span.etiqueta{
			font-weight: bold;
			display: inline-block;
			width: 100px;
			text-align:right;
		}

    </style>
</head>
<body>
	<?php

		if ( $mostrar_areas == 'Si')
		{
			$lbl_asigatura = 'Área / Asignaturas';
		}else{

			$lbl_asigatura = 'Asignaturas';
		}
		
	?>

	@yield('contenido_formato')

</body>
</html>