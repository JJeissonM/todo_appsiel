<?php

use App\Core\Tercero;

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];

    $tercero = Tercero::find( $doc_encabezado->core_tercero_id );
    $imprimir = false;
?>
<!DOCTYPE html>
<html>

<head> 
    <title>{{ $orden_de_trabajo->tipo_documento_app->prefijo . ' ' . $orden_de_trabajo->consecutivo }}</title>  
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
            <b style="font-size: 16px">Orden de Trabajo N° {{ $orden_de_trabajo->tipo_documento_app->prefijo . ' ' . $orden_de_trabajo->consecutivo }}</b>
        </td>
    </tr>
    <tr>
        <td>Dirección: {{ $empresa->direccion1 }}</td>        
    </tr>
    <tr>
        <td>
            <p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
        </td>
    </tr>
</table>
<hr>
<table class="info">
    <tr>
        <td width="40mm"><b>Fecha: </b></td>
        <td>{{ $orden_de_trabajo->fecha }}</td>
        <td><b>Tercero:</b></td>
        <td>{{ $orden_de_trabajo->tercero->descripcion }}</td>
    </tr>
    <tr>
        <td><b>Proyecto: </b></td>
        <td>{{ $orden_de_trabajo->documento_nomina->descripcion }}</td>
        <td><b>Ubicación desarollo actividad : &nbsp;&nbsp;</b></td>
        <td> {{ $orden_de_trabajo->ubicacion_desarrollo_actividad }}</td>
    </tr>
</table>
<hr>
<br>


<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Empleados Orden de Trabajo</div>
@include('nomina.ordenes_de_trabajo.show_empleados')

<br>
<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Items Orden de Trabajo</div>
@include('nomina.ordenes_de_trabajo.show_items', ['id'=>$orden_de_trabajo->id])


