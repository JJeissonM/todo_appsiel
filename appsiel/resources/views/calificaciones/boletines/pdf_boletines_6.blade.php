<!DOCTYPE html>
<html>
<head>
    <title> Informes curso {{ $curso->descripcion }} </title>
    <style type="text/css">

		*{
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
        
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{$tam_letra}}mm;
            /*margin: {{$margenes->superior}}px {{$margenes->derecho}}px {{$margenes->inferior}}px {{$margenes->izquierdo}}px;*/
        }

        @page{ margin: 60px 40px 20px 40px !important; }
        
        .page-break {
            page-break-after: always;
        }

        table{
            width: 100%;
            border-collapse: collapse;	
			margin: -1px 0;		
        }

        table.table-bordered, .table-bordered>tbody>tr>td{
            border: 1px solid gray;
        }

		.imagen {
			  /**/display: block;
			  margin-left: auto;
			  margin-right: auto;
			  width: 50%;
		}

		th {
			background-color: #E0E0E0;
			border: 1px solid;
		}

		li{
			list-style-image: url({{ url('/') . '/nube/check-mark-icon-small.png' }});
		}

		table.banner{
	        font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
	        font-style: italic;
	        font-size: 16px;
	        border: 1px solid gray;
	        /*padding-top: -30px;*/
	    }

		table.encabezado{
			border: 1px solid gray;
			padding: 0px;
		}

		table.encabezado>tr>td{
			font-size: {{$tam_letra+2}}mm;
		}

		table.contenido>tr>th{
			font-size: {{$tam_letra}}mm;
		}
		
		span.etiqueta{
			font-weight: bold;
			width: 100px;
			text-align:right;
		}

		.watermark-letter {
			position: absolute;
			top: 12%;
			left: 15%;
			text-align: center;
			opacity: .2;
			z-index: -1000;
			width: 70%;
		}

		.watermark-folio {
			position: absolute;
			top: 20%;
			left: 15%;
			text-align: center;
			opacity: .2;
			z-index: -1000;
			width: 70%;
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

    {!! $all_boletines !!}
	
</body>
</html>