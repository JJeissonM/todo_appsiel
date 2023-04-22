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
        }

        .page-break {
            page-break-after: always;
        }

    </style>
</head>
<body>
        
        <?php
            $numero_columnas = 4;
            $i = $numero_columnas;
            $minimo_comun_multiplo_columnas = 12;

            $alto_celda = 95; // px
            
            $primera_fila = true;
        ?>

        <table class="table" style="width: 100%; font-size: 12px;">
            <tbody>            
                <tr>
                    <td colspan="{{$numero_columnas}}">
                        <br><br><br><br>
                    </td>
                </tr>
                @foreach($items as $fila)
                
                    @if($i % $numero_columnas == 0)
                        <tr>
                    @endif

                    <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}" style="width: 25%; height:{{$alto_celda}}px;">
                        
                        <div style="margin: 0px 15px 0px 15px; text-align: center; height: 90%; border: 1px #ddd solid;">
                            <p style="font-size:110%; font-family:cursive; line-height: 30px; padding-top: 20px;">
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