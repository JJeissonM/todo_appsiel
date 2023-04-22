<!DOCTYPE html>
<html>
<head>
    <title> Listado Etiquetas de referencias </title>
    <style type="text/css">

		*{
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
        
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12mm;
            /*margin: { {$margenes->superior}}px { {$margenes->derecho}}px { {$margenes->inferior}}px { {$margenes->izquierdo}}px;*/
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
		
    </style>
</head>
<body>
        
        <?php
            $numero_columnas = 4;
            $i = $numero_columnas;
            $minimo_comun_multiplo_columnas = 12;

            $alto_celda = 100; // px
        ?>

        <table class="table" style="width: 100%; font-size: 12px;">
            <tbody>            
            
                @foreach($items as $fila)
                
                    @if($i % $numero_columnas == 0)
                        <tr style="height:{{$alto_celda}}px;">
                    @endif

                    <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}">
                        
                        <div style="padding: 5px; text-align: center; height:{{$alto_celda-30}}px; vertical-align: middle;">
                            <br><br>
                            <p style="font-size:120%;font-family:cursive">
                                <b>$ {{ number_format($fila->get_precio_venta(), 0, ',', '.') }}</b>
                            </p>                            
                            {{ $fila->referencia }}
                        </div>
                            
                    </td>

                    <?php
                        $i++;
                    ?>

                    @if($i % $numero_columnas == 0)
                        </tr>
                    @endif

                @endforeach
            </tbody>
        </table>	
</body>
</html>