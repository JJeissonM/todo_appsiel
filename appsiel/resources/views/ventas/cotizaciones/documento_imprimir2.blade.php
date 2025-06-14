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
<p>{{ $empresa->razon_social }}</p>
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
            <b style="font-size: 16px">Cotización Nro. {{ sprintf("%04d", $doc_encabezado->documento_transaccion_consecutivo) }}</b>
        </td>
    </tr>
    @if( (int)$doc_encabezado->numero_identificacion == 0 )
        <tr><td colspan="2">&nbsp;</td></tr>
    @else
        <tr>
            <td><b>{{ config("configuracion.tipo_identificador") }}:</b></td>
            <td>@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</td>
            <td colspan="2"></td>
        </tr>
    @endif
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
        <td width="15%" >
            <b>Fecha:</b>
        </td>
        <td width="15%">
            <?php
                $fecha = date_create($doc_encabezado->fecha);
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
        <td >
            <b>Valido hasta:</b>
        </td>
        <td>
            <?php
                $fecha = date_create($doc_encabezado->fecha_vencimiento);
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
            ?>
            {{ $fecha_final }}
        </td>
    </tr>
</table>
<hr>
    <br>
    <b>Oferta Técnica: &nbsp;&nbsp;</b> 
    <br>
    <p class="info text-indent">
        <?php echo $doc_encabezado->descripcion ?>
    </p>
    
<br>
<hr>
<b>Oferta Económica: &nbsp;&nbsp;</b> 
<table class="contenido">
    @if(config('ventas.detallar_iva_cotizaciones'))
        {{ Form::bsTableHeader(['Item','Producto','Cant.','Vr. unit.','IVA','DTO','Total']) }}
    @else
        {{ Form::bsTableHeader(['Item','Producto','Cant.','Vr. unit.','DTO','Total']) }}
    @endif
    <tbody>
        <?php 
        $i = 1;
        $total_cantidad = 0;
        $subtotal = 0;
        $total_impuestos = 0;
        $total_descuentos = 0;
        $total_factura = 0;
        $array_tasas = [];

        $impuesto_iva = 0;//iva en firma

        $cantidad_items = 0;
        ?>
        @foreach($doc_registros as $linea )
        <tr>
            <td class="text-center"> {{ $i }} </td>
            <?php 
                    $descripcion_item = $linea->producto_descripcion . ' (' . $linea->item->get_unidad_medida1() . ')';

                    if( $linea->unidad_medida2 != '' )
                    {
                        $descripcion_item = $linea->producto_descripcion . ' (' . $linea->item->get_unidad_medida1() . ') - Talla: ' . $linea->unidad_medida2;
                    }
                ?>
            <td class="text-left" width="250px"> {{ $descripcion_item }} </td>
            <td class="text-center"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
            <td class="text-right"> {{ '$  '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 2, ',', '.') }}
            </td>
            @if(config('ventas.detallar_iva_cotizaciones'))
                <td class="text-right"> {{ '$  '.number_format( $linea->valor_impuesto, 2, ',', '.') }} </td>
            @endif            
            <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->valor_total_descuento, 2, ',', '.') }} </td>
            <td class="text-right"> {{ '$  '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
        </tr>
        <?php
                $i++;
                $total_cantidad += $linea->cantidad;
                $subtotal += (float)($linea->precio_unitario - $linea->valor_impuesto) * (float)$linea->cantidad;
                $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                $total_factura += $linea->precio_total;
                $total_descuentos += $linea->valor_total_descuento;

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
                $cantidad_items++;

                if($linea->valor_impuesto > 0){
                    $impuesto_iva = $linea->tasa_impuesto;
                }
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold;">
            <td colspan="2"> Cantidad de items: {{ $cantidad_items }} </td>
            <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
            @if(config('ventas.detallar_iva_cotizaciones'))
                <td colspan="4">&nbsp;</td>
            @else
                <td colspan="3">&nbsp;</td>
            @endif
        </tr>
    </tfoot>
</table>

@include('ventas.incluir.factura_firma_totales')

<table class="info">
    <tr>
        <td width="30%"><b>Cotizó:</b></td>
        <td>{{ $doc_encabezado->vendedor->tercero->descripcion }}</td>
    </tr>

    @if(config('ventas.detallar_condicion_ventas'))
        <tr>
            <td width="30%"><b>Condición de Venta:</b></td>
            <td>
                {{ $doc_encabezado->texto_condicion_venta() }}
            </td>
        </tr>
    @endif

    
    <tr>
        <td width="30%"><b>Plazo de entrega:</b></td>
        <td>
            @if( !is_null( $doc_encabezado->plazo_entrega ) )
                {{ $doc_encabezado->plazo_entrega->valor }}
            @endif
        </td>
    </tr>
</table>
<hr>
<div class="text-indent">
    @if( !is_null( $otroscampos ) )
        {!! $otroscampos->terminos_y_condiciones !!}
    @endif
<br>
<div class="encabezado">
    <p style="text-align: right; font-size: 18px; padding-right: 30px">{{ $empresa->descripcion }}</p>
</div>
</body>
</html>