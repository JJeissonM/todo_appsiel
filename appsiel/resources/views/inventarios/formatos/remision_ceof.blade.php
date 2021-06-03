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
    <div style="position: absolute; left: 40mm; top: 63mm;">
        {{ $doc_encabezado->tercero_nombre_completo }} @if( !is_null($doc_encabezado->documento_ventas_padre()) )
       / CONTACTO: {{ $doc_encabezado->documento_ventas_padre()->contacto_cliente->tercero->descripcion }}        
    @endif
    </div>

    <div style="position: absolute; left: 40mm; top: 68mm;">
        {{ $doc_encabezado->numero_identificacion}}
    </div>

    <div style="position: absolute; left: 40mm; top: 73mm;">
        {{ $empresa->direccion1 }}
    </div>

    <div style="position: absolute; left: 40mm; top: 80mm;">
        {{ $ciudad->descripcion }} 
    </div>

    <div style="position: absolute; right: 55mm; top: 38mm;">
        <?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}
    </div>

    <div style="position: absolute; left: 40mm; top: 95mm;">
        CONSTANCIA ENTREGA OBRA FINALIZADA
    </div>
    <div style="position: absolute; left: 47mm; top: 112mm; width: 180mm">
        <table width="100%">
            <tr>
                <td width="15%"></td>
                <td width="60%"><?php echo $doc_encabezado->descripcion ?></td>
                <td></td>
            </tr>   
        </table>         
    </div>

   

  



    
</body>
</html>