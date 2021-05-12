<?php
use App\Inventarios\InvBodega;
use App\Core\Tercero;
use App\Compras\ComprasDocEncabezado;

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

    $tercero = Tercero::find( $doc_encabezado->core_tercero_id );
    
?>
<!DOCTYPE html>
<html>
<head>

    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
<link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">  
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <style type="text/css">
    *{
       margin: 0;
       padding: 0;
       box-sizing: border-box;
       font-size: 12px;
       font-family: 'Open Sans', sans-serif;
    }
    
    .page-break{
        page-break-after: always;
    }

    html{
        margin: 30px 70px 20px 70px;
    }    
    .info, .table, .contenido, .encabezado{
        width: 100%;
        border-collapse: collapse;
        margin: .5rem 0;
    }
    .encabezado {
        background-color: #111e52;        
        color: white;
        margin-left: -40px;
        margin-right: -40px;
        padding: 10px;
    }
    .contenido >tr>td{ 
        border: 1px solid black !important;
        text-align: right;
        padding: 0 3px;
    }
    .text-center{
        text-align: center !important;
    }
    .text-left{
        text-align: left !important;
    }
    .text-indent, .text-indent > *{
        padding-left: 10px !important;
        text-align: justify !important;
    }
    .contenido th{
        color: black !important;
        border: 1px solid black !important;
        background-color: lightgray !important;
    }
    .totales{
        border: 1px solid black;
    }
    .totl-top{
        border-top: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
    .totl-mid{
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
    .totl-bottom{
        border-bottom: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
    .encabezado a{
        color: white;
    }
    a{
        text-decoration: none;
        color: black;
    }
    </style>
</head>
<body>
    <table class="encabezado" width="100%">
        <tr>
            <td width="70%" rowspan="2">
                <?php
                    $image = getimagesize($url);
                    $ancho = $image[0];
                    $alto = $image[1];
                 
                    $palto = (60*100)/$alto;
                    $ancho = $ancho*$palto/100;
                    echo '<img src="'.$url.'" width="'.$ancho.'" height="60" />';
               
                ?>	
                </td>
            <td>Teléfono: {{ $empresa->telefono1 }}</td>
        </tr>
        <tr>
            <td>Email: <a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a></td>
        </tr>
    </table>

    <table class="info">
        <tr>
            <td width="55%"><b style="font-size: 16px">{{ $empresa->descripcion }}</b></td>
            <td width="45%" colspan="">
                <b style="font-size: 16px; text-transform: capitalize">{{ $doc_encabezado->documento_transaccion_descripcion }} N. {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</b>
            </td>
        </tr>
        <tr>
            <td>Dirección: {{ $empresa->direccion1 }}</td>       
            <td>{{ config("configuracion.tipo_identificador") }}: @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</td>     
        </tr>
    </table>
    <hr>
    <table class="info">
        <tr>
            <td width="12%">
                <b>Proveedor:</b>
            </td>
            <td width="43%">
                {{ $tercero->razon_social }}
            </td>
            <?php 
                $reg_fatura_venta = App\Ventas\VtasDocEncabezado::where('remision_doc_encabezado_id',$datos_encabezado_doc['campo9'])->get()->first();
                $reg_fatura_compras = App\Compras\ComprasDocEncabezado::where('entrada_almacen_id',$datos_encabezado_doc['campo9'])->get()->first();
                $factura = '';

                if( !is_null($reg_fatura_venta) )
                {
                    $fatura_venta = App\Ventas\VtasDocEncabezado::get_registro_impresion( $reg_fatura_venta->id );
                    $factura = $fatura_venta;
                }  
                if( !is_null($reg_fatura_compras) )
                {
                    $fatura_compra = App\Compras\ComprasDocEncabezado::get_registro_impresion( $reg_fatura_compras->id );
                    $factura = $fatura_compra;
                }

            ?>

            @if (substr($factura->documento_transaccion_prefijo_consecutivo,0,2) == "FC")
                <td>
                    <b>Factura de Compras:</b>
                </td>
            @else
                <td>
                    <b>Orden de Compras:</b>
                </td>
            @endif
            
            <td>
                <?php                

                if( !is_null($reg_fatura_venta) )
                {
                    echo '<a href="'.url('ventas/'.$fatura_venta->id.'?id=13&id_modelo=139').'" target="_blank">'.$fatura_venta->documento_transaccion_prefijo_consecutivo.'</a>';
                }                

                if( !is_null($reg_fatura_compras) )
                {
                    echo '<a href="'.url('compras/'.$fatura_compra->id.'?id=9&id_modelo=147').'" target="_blank">'.$fatura_compra->documento_transaccion_prefijo_consecutivo.'</a>';
                }
            ?>
            </td>
        </tr>
        <tr>
            <td><b>Fecha: </b></td>
            <td><?php
                $fecha = date_create($doc_encabezado->fecha);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}</td>
        </tr>
    </table>
    <hr>
    <br>
    
    <table class="table table-bordered contenido" >
        <thead>
            <tr style="text-align: center; background-color: #ddd; font-weight: bolder;">
                <th>No.</td>
                <th>Producto</td>
                <th>Bodega</td>
                <th>Cant.</td>
                <th>Vr. Unit.</th>
                <th>Total</th>
            </tr>
        </thead>


        <?php
                $total_cantidad = 0;
                $numero = 1;
            ?>
        @foreach($doc_registros as $linea )
        <tr>
            <td style="text-align: center;"> {{ $numero }} </td>
            <td> {{ $linea->producto_descripcion }} </td>
            <td>{{ $linea->bodega_descripcion }}</td>
            <td style="text-align: center;"> {{ number_format( abs($linea->cantidad), 2, ',', '.') }} </td>
            <td style="text-align: right;">$ {{ number_format($linea->costo_unitario, 2, ',', '.') }} </td>
            <td style="text-align: right;">$ {{ number_format($linea->costo_total, 2, ',', '.') }} </td>
            
        </tr>
        <?php 
                    $total_cantidad += $linea->cantidad;
                    $numero++;
                ?>
        @endforeach
        <tr>
            <td colspan="3">&nbsp;</td>
            <td style="text-align: center;"> {{ number_format( abs($total_cantidad), 2, ',', '.') }} </td>
            <td colspan="2"></td>
        </tr>
    </table>

    <br><br><br><br>

    <table border="0" width="100%">
        <tr>
            <td width="50px"> &nbsp; </td>
            <td align="center" style="border-bottom: 1px solid;"> </td>
            <td align="center"> &nbsp; </td>
            <td align="center" style="border-bottom: 1px solid;"> </td>
            <td width="50px">&nbsp;</td>
        </tr>
        <tr>
            <td width="50px"> &nbsp; </td>
            <td align="center"> Elaboró: {{ explode('@',$doc_encabezado->creado_por)[0] }} </td>
            <td align="center"> &nbsp; </td>
            <td align="center"> Recibe </td>
            <td width="50px">&nbsp;</td>
        </tr>
    </table>
<br>
<b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}

</body>
</html>