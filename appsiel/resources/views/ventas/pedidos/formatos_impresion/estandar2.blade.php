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
<div class="info">
<p>{{ $empresa->descripcion }}</p>
<p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
</div>


<hr>
<table class="info">
    <tr>
        <td width="12%">
            <b>Solicitante:</b>
        </td>
        <td width="58%">
            {{ $tercero->razon_social }}
        </td>
        <td colspan="2">
            <b style="font-size: 16px">Pedido Nª {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</b>
        </td>
    </tr>
    <tr>
        <td><b>{{ config("configuracion.tipo_identificador") }}: </b></td>
        <td>@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="4" >&nbsp;</td>
    </tr>
    <tr>
        <td>
            <b>Contacto:</b>
        </td>
        <td>
            {{ $contacto->descripcion }}
        </td>
        <td width="16%">
            <b>Fecha de Entrega:</b>
        </td>
        <td width="14%">
            <?php
                $fecha = date_create($doc_encabezado->fecha_entrega);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                        
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}
        </td>
    </tr>
    <tr>
        <td>
            <b>Teléfono: </b>
        </td>
        <td colspan="3">
            {{ $contacto->telefono1 }}
        </td>
    </tr>
    <tr>
        <td>
            <b>Mail: </b>
        </td>
        <td>
            <a href="mailto:{{ $contacto->email }}">{{ $contacto->email }}</a>
        </td>
        <td>
            <!--<b>Valido hasta:</b>-->
        </td>
        <td><!--
            <?php
                $fecha = date_create($doc_encabezado->fecha_vencimiento);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}-->
        </td>
    </tr>
</table>
<hr>
    
    <p class="info text-indent">
        <b>Detalle: &nbsp;&nbsp;</b> 
        <?php echo $doc_encabezado->descripcion ?>
    </p>
<hr>    
<br>


<table class="contenido">
    {{ Form::bsTableHeader(['Item','Producto','Cantidad']) }}
        <tbody>
            <?php 
            $i = 1;
            $total_cantidad = 0;
            $subtotal = 0;
            $total_impuestos = 0;
            $total_factura = 0;
            $array_tasas = [];
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td width="15%" class="text-center"> {{ $i }} </td>
                    <td width="70%" class="text-left"> {{ $linea->producto_descripcion }} </td>
                    <td width="15%" class="text-center"> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $i++;
                    $total_cantidad += $linea->cantidad;
                    $subtotal += (float)$linea->base_impuesto * (float)$linea->cantidad;
                    $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    $total_factura += $linea->precio_total;

                    // Si la tasa no está en el array, se agregan sus valores por primera vez
                    if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
                    {
                        // Clasificar el impuesto
                        $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA '.$linea->tasa_impuesto.'%';
                        if ( $linea->tasa_impuesto == 0)
                        {
                            $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA 0%';
                        }
                        // Guardar la tasa en el array
                        $array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


                        // Guardar el primer valor del impuesto y base en el array
                        $array_tasas[$linea->tasa_impuesto]['precio_total'] = (float)$linea->precio_total;
                        $array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float)$linea->base_impuesto * (float)$linea->cantidad;
                        $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float)$linea->valor_impuesto * (float)$linea->cantidad;

                    }else{
                        // Si ya está la tasa creada en el array
                        // Acumular los siguientes valores del valor base y valor de impuesto según el tipo
                        $precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
                        $array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float)$linea->precio_total;
                        $array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float)$linea->base_impuesto * (float)$linea->cantidad;
                        $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    }
                ?>
            @endforeach
        </tbody>
</table>

<br>
<div class="encabezado">
    <p style="text-align: right; font-size: 18px; padding-right: 30px">{{ $empresa->descripcion }}</p>
</div>
</body>
</html>