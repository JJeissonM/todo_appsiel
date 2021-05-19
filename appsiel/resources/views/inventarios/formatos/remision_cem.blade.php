<?php
    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div style="position: absolute; left: 40mm; top: 64mm;">
        {{ $doc_encabezado->tercero_nombre_completo }} / @if( !is_null($doc_encabezado->contacto_cliente) )
        <b>CONTACTO: </b> {{ $doc_encabezado->contacto_cliente->tercero->descripcion }}
    @endif
    </div>

    <div style="position: absolute; left: 40mm; top: 70mm;">
        {{ $doc_encabezado->numero_identificacion}}
    </div>

    <div style="position: absolute; left: 40mm; top: 76mm;">
        {{ $empresa->direccion1 }}
    </div>

    <div style="position: absolute; left: 40mm; top: 82mm;">
        {{ $ciudad->descripcion }} 
    </div>

    <div style="position: absolute; left: 144mm; top: 88mm;">
        
    </div>

    <div style="position: absolute; left: 40mm; top: 94mm;">        
        {{ $doc_encabezado->descripcion }}
    </div>

    <div style="position: absolute; left: 5mm; top: 104mm; width: 193mm">

    <table width="100%">
        <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
        @foreach($doc_registros as $linea )
        <tr>
            <td style="text-align: center;" width="15%"> {{ $linea->producto_id }} </td>
            <td width="70%"> {{ $linea->producto_descripcion }} </td>
            <td style="text-align: right;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
        </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $numero++;
                ?>
        @endforeach
    </table>
        

    </div>

    <div style="position: absolute; left: 171mm; top: 253mm;">        
        {{ $total_cantidad }}
    </div>


    
</body>
</html>