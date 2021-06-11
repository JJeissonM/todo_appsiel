<?php
    $ciudad = DB::table('core_ciudades')->where('id',$doc_encabezado->codigo_ciudad)->get()[0];
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
    <div style="position: absolute; left: 40mm; top: 69mm;">
        <b>{{ $doc_encabezado->tercero_nombre_completo }}</b>
    </div>

    <div style="position: absolute; left: 40mm; top: 74mm;">
        <b>{{ $doc_encabezado->numero_identificacion}}</b>
    </div>

    <div style="position: absolute; left: 40mm; top: 79mm;">
        <b>{{ $doc_encabezado->direccion1 }}</b>
    </div>

    <div style="position: absolute; left: 40mm; top: 84mm;">
        <b>{{ $ciudad->descripcion }} </b>
    </div>

    <div style="position: absolute; right: 55mm; top: 36.5mm;font-size: 14px">
        <b><?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}</b>
    </div>

    <div style="position: absolute; left: 40mm; top: 93.5mm;font-size: 14px">
        <b>CONSTANCIA ENTREGA MATERIAL</b>
    </div>

    <div style="position: absolute; left: 25mm; top: 112mm; width: 180mm">
        <table width="100%">
            <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
            @foreach($doc_registros as $linea )
            <tr>
                <td style="text-align: center;" width="10%"> {{ $linea->producto_id }} </td>
                <td width="70%"> {{ $linea->producto_descripcion }} </td>
                <td width="20%" style="text-align: center;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
            </tr>
            <?php 
                $total_cantidad += $linea->cantidad;
                $numero++;
            ?>
            @endforeach
            <tr>
                <td style="text-align: center;" width="10%"></td>
                <td width="70%">
                    <br>
                    <br>
                    <b>
                    @if( !is_null( $doc_encabezado->documento_ventas_padre() ) )
                        @if( !is_null( $doc_encabezado->documento_ventas_padre()->contacto_cliente ) )
                            CONTACTO: {{ $doc_encabezado->documento_ventas_padre()->contacto_cliente->tercero->descripcion }}<br>
                        @endif
                    @endif
                    @if(!is_null( $doc_encabezado->documento_ventas_padre()->documento_ventas_padre() ))
                        COTIZACIÓN NRO. {{ $doc_encabezado->documento_ventas_padre()->documento_ventas_padre()->consecutivo }}<br>
                        ORDEN DE COMPRAS: {{ $doc_encabezado->documento_ventas_padre()->documento_ventas_padre()->orden_compras }}
                    @endif
                    </b>
                </td>
                <td width="20%" style="text-align: center;"></td>
            </tr>
                    
        </table>     
    </div>

    <div style="position: absolute; left: 183mm; top: 254mm;">        
        {{ abs($total_cantidad) }}
    </div>


    
</body>
</html>