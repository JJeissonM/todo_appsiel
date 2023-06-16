<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>APPSIEL ..:: Sistemas de Información en Línea ::..</title>

        <style>
            body {
                font-size: 7px;
            }

            @page { 
                margin: 15px;
            }

            p { margin: 1px; }

            img{
                width: 105px;
                height: 70px;
            }

            .table
            {
                width: 100%;
            }
       </style>
    </head>
    <body id="app-layout">
    
        <?php
            $numero_columnas = 3;
            $i = 3;
            $minimo_comun_multiplo_columnas = 12;
        ?>

        <table>                   
            
                @foreach($items as $fila)
                  
                    @if($i % $numero_columnas == 0)
                        <tr>
                    @endif

                    <!-- colspan="{ { $minimo_comun_multiplo_columnas / $numero_columnas }}" -->
                    <td width="33%">
                        <div style="border: solid 1px #ddd; border-radius: 4px; padding: 5px; text-align: center;">

                            <p>
                                <b>{{ substr( $fila->descripcion, 0, 20) }}</b>
                            </p>
                        
                            <?php
                                $codigo_barras = $fila->codigo_barras;
                                if( $fila->codigo_barras == '' )
                                {
                                    $codigo_barras = $fila->id;
                                }
                            ?>
                            <p>
                                <!-- Solo se envian los 12 primeros digitos, la function getBarcodePNG dibuja el codigo de barras con el digito de control al final -->
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( substr($codigo_barras,0,12), "EAN13") }}" alt="barcode" />
                            </p>
                            {{ $codigo_barras }}
                        </div>
                            
                    </td>

                    <?php
                        $i++;
                    ?>

                    @if($i % $numero_columnas == 0)
                        </tr>
                    @endif

                @endforeach
        </table>        
    </body>
</html>