<?php

use App\Core\Tercero;

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

    $tercero = Tercero::find( $doc_encabezado->core_tercero_id );
    
?>
<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    @include( 'core.dis_formatos.plantillas.estiloestandar2', [ 'vista' => 'imprimir' ] )
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
        </tr>
    </table>
    <hr>

    @if($doc_encabezado->documento_transaccion_descripcion == "Órden de Compra")
    <table class="info">
        <tr>
            <td width="12%">
                <b>Proveedor:</b>
            </td>
            <td width="48%">
                {{ $tercero->razon_social }}
            </td>
            <td>
                <b>Fecha Recepción Mercaderría:</b>
            </td>
            <td>
                {{ $doc_encabezado->fecha_recepcion }}
            </td>
        </tr>
        <tr>
            <td><b>Domicilio:</b> </td>
            <td>{{ $doc_encabezado->direccion1 }}</td>
            <td><b>Condicion de Pago:</b></td>
            <td style="text-transform: capitalize">{{ $doc_encabezado->condicion_pago }}</td>
        </tr>
        <tr>
            <td><b>Localidad:</b></td>
            <td>{{ $tercero->ciudad->descripcion }}</td>
            <td width="35%"><b>Fecha Vencimiento:</b></td>
            <td width="20%">
                <?php
                    $fecha = date_create($doc_encabezado->fecha_vencimiento);
                    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                    $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
                ?>
                {{ $fecha_final }}
            </td>
        </tr>
    </table>
    @else
    <table class="info">
        <tr>
            <td width="12%">
                <b>Proveedor:</b>
            </td>
            <td width="48%">
                {{ $tercero->razon_social }}
            </td>
            <td>
                <b>Factura Proveedor:</b>
            </td>
            <td>
                {{ $doc_encabezado->doc_proveedor_consecutivo }}
            </td>
        </tr>
        <tr>
            <td><b>{{ config("configuracion.tipo_identificador") }}:</b></td>
            <td>@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</td>            
            <td><b>Condicion de Pago:</b></td>
            <td style="text-transform: capitalize">{{ $doc_encabezado->condicion_pago }}</td>
        </tr>
        <tr>
            <td><b>Domicilio:</b> </td>
            <td>{{ $doc_encabezado->direccion1 }}</td>
            <td><b>Fecha de Vencimiento:</b></td>
            <td><?php
                $fecha = date_create($doc_encabezado->fecha_vencimiento);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}</td>
        </tr>
        <tr>
            <td><b>Localidad:</b></td>
            <td>{{ $tercero->ciudad->descripcion }}</td>
            <td width="25%"><b>Entrada de Almacén:</b></td>
            <td width="30%">
                {{ $doc_encabezado->documento_remision_prefijo_consecutivo }}
            </td>
        </tr>
    </table>
    @endif
    <hr>

    
    @yield('tabla_registros_1')

    @yield('tabla_registros_2')

    @yield('tabla_registros_3')

    @include('core.firmas')

    <table style="width: 100%;">
        <!-- <tr>
            <td style="border: solid 1px black;"> <b> @ yield('lbl_firma') </b> <br><br><br><br> </td>
        </tr>
        -->
        @yield('firma_fila_adicional')
    </table>

    <br><br><br>

</body>

</html>