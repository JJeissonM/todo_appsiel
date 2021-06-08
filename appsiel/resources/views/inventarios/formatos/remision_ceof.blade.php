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
        {{ $doc_encabezado->tercero_nombre_completo }} 
        @if( !is_null( $doc_encabezado->documento_ventas_padre() ) )
            @if( !is_null( $doc_encabezado->documento_ventas_padre()->contacto_cliente ) )
                / CONTACTO: {{ $doc_encabezado->documento_ventas_padre()->contacto_cliente->tercero->descripcion }}
            @endif
        @endif
    </div>

    <div style="position: absolute; left: 40mm; top: 74mm;">
        {{ $doc_encabezado->numero_identificacion}} - {{ $doc_encabezado->digito_verificacion }}
    </div>

    <div style="position: absolute; left: 40mm; top: 79mm;">
        {{ $doc_encabezado->direccion1 }}
    </div>

    <div style="position: absolute; left: 40mm; top: 84mm;">
        {{ $ciudad->descripcion }} 
    </div>

    <div style="position: absolute; right: 55mm; top: 36.5mm;">
        <b><?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}</b>
    </div>

    <div style="position: absolute; left: 40mm; top: 93.5mm;font-size: 14px">
        <b>CONSTANCIA DE ENTREGA DE OBRA FINALIZADA</b>
    </div>
    <div style="position: absolute; left: 25mm; top: 112mm; width: 180mm">
        <table width="100%">
            <tr>
                <td width="10%"></td>
                <td width="70%"><?php echo $doc_encabezado->descripcion ?></td>
                <td width="20%"></td>
            </tr>   
        </table>         
    </div>

   

  



    
</body>
</html>