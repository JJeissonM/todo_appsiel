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
    <div style="position: absolute; left: 40mm; top: 67mm;">
        {{ $doc_encabezado->tercero_nombre_completo }} @if( !is_null($doc_encabezado->documento_ventas_padre()) )
       / CONTACTO: {{ $doc_encabezado->documento_ventas_padre()->contacto_cliente->tercero->descripcion }}        
    @endif
    </div>

    <div style="position: absolute; left: 40mm; top: 73mm;">
        {{ $doc_encabezado->numero_identificacion}}
    </div>

    <div style="position: absolute; left: 40mm; top: 79mm;">
        {{ $empresa->direccion1 }}
    </div>

    <div style="position: absolute; left: 40mm; top: 85mm;">
        {{ $ciudad->descripcion }} 
    </div>

    <div style="position: absolute; right: 75mm; top: 41mm;">
        <?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}
    </div>

    <div style="position: absolute; left: 40mm; top: 94mm;">        
        CONSTANCIA ENTREGA MATERIAL
    </div>

    <div style="position: absolute; left: 15mm; top: 112mm; width: 193mm">

    <table width="100%">
        <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
        @foreach($doc_registros as $linea )
        <tr>
            <td style="text-align: center;" width="15%"> {{ $linea->producto_id }} </td>
            <td width="65%"> {{ $linea->producto_descripcion }} </td>
            <td style="text-align: center;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
        </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $numero++;
                ?>
        @endforeach
    </table>
        

    </div>

    <div style="position: absolute; left: 187mm; top: 248mm;">        
        {{ abs($total_cantidad) }}
    </div>


    
</body>
</html>