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
<div class="info">
<p>{{ $empresa->descripcion }}</p>
<p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
</div>


<hr>
<table class="info">
    <tr>
        <td>
            <b>Solicitante:</b>
        </td>
        <td>
            {{ $tercero->razon_social }}
        </td>
        <td colspan="2">
            <b style="font-size: 16px">Cotización Nª {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</b>
        </td>
    </tr>
    <tr>
        <td><b>{{ config("configuracion.tipo_identificador") }} :</b></td>
        <td>{{ $doc_encabezado->numero_identificacion - $empresa->digito_verificacion }}</td>
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
        <td>
            <b>Fecha:</b>
        </td>
        <td>
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
        <td>
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
    {{ Form::bsTableHeader(['Item','Producto','Cant.','Vr. unit.','IVA','DTO','Total']) }}
    <tbody>
        <?php 
        $i = 1;
        $total_cantidad = 0;
        $subtotal = 0;
        $total_impuestos = 0;
        $total_descuentos = 0;
        $total_factura = 0;
        $array_tasas = [];

        $cantidad_items = 0;
        ?>
        @foreach($doc_registros as $linea )
        <tr>
            <td class="text-center"> {{ $i }} </td>
            <?php 
                    $descripcion_item = $linea->producto_descripcion . ' (' . $linea->unidad_medida1 . ')';

                    if( $linea->unidad_medida2 != '' )
                    {
                        $descripcion_item = $linea->producto_descripcion . ' (' . $linea->unidad_medida1 . ') - Talla: ' . $linea->unidad_medida2;
                    }
                ?>
            <td class="text-left" width="250px"> {{ $descripcion_item }} </td>
            <td class="text-center"> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
            <td> {{ '$  '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 2, ',', '.') }}
            </td>
            <td class="text-center"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
            <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->valor_total_descuento, 2, ',', '.') }} </td>
            <td> {{ '$  '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
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
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold;">
            <td colspan="2"> Cantidad de items: {{ $cantidad_items }} </td>
            <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
            <td colspan="4">&nbsp;</td>
        </tr>
    </tfoot>
</table>

@include('ventas.incluir.factura_firma_totales')

<table class="info">
    <tr>
        <td width="30%"><b>Cotizó:</b></td>
        <td>{{ $doc_encabezado->vendedor_nombre_completo }}</td>
    </tr>
    <tr>
        <td width="30%"><b>Condición de Venta:</b></td>
        <td style="text-transform: capitalize">
            {{ $doc_encabezado->texto_condicion_venta() }}
        </td>
    </tr>
    <tr>
        <td width="30%"><b>Plazo de entrega:</b></td>
        <td>A convenir</td>
    </tr>
</table>
<hr>
<h1>Condiciones:</h1>
<div class="text-indent"><?php echo $otroscampos->terminos_y_condiciones ?></div>
<br>
<div class="encabezado">
    <p style="text-align: right; font-size: 18px; padding-right: 30px">{{ $empresa->descripcion }}</p>
</div>
</body>
</html>

<!--<p><strong><u>CONDICIONES COMERCIALES:</u></strong></p>
<p>La aceptaci&oacute;n del presente presupuesto puede realizarse v&iacute;a email para coordinar los trabajos a ejecutar.</p>

<p><strong><u>ALCANCE DE LA PROPUESTA:</u></strong></p>

<ol>
	<li>El presente presupuesto es v&aacute;lido para horas normales y en horario extendido de 17 a 21 hs para d&iacute;as de semana en caso que el cliente propusiese comienzo en d&iacute;a distinto, o que por motivos ajenos a Seizo los trabajos o traslado se tuvieran que efectuar en horarios o d&iacute;as especiales; eso podr&aacute; provocar cambios en el presente presupuesto y Seizo se reserva el derecho de exigir adicionales.</li>
	<li>En caso que fuera necesario aplicar alg&uacute;n repuesto no incluido en el presupuesto y el cliente no cuente con los mismos, esto ser&aacute; cotizado como adicional.</li>
	<li>Limpieza general de los equipos no est&aacute; incluida.</li>
	<li>Si por indisponibilidad del equipo por parte del comitente se provocaran tiempos de espera que atenten contra la realizaci&oacute;n de los trabajos previstos dentro del plazo establecido, esto ser&aacute; notificado al cliente y se acordar&aacute; con &eacute;l en funci&oacute;n del nivel de avance a alcanzar:
<ul>
	<li>La terminaci&oacute;n de los trabajos posibles dentro del plazo previsto, y el acuerdo de una nueva visita.</li>
	<li>La prolongaci&oacute;n del cronograma hasta alcanzar la culminaci&oacute;n de los trabajos, si otros compromisos para los t&eacute;cnicos Seizo no lo impidieran y esto fuese posible para el cliente. En cualquiera de los casos, el cargo por d&iacute;as adicionales y dem&aacute;s gastos asociados (alojamientos, comidas, etc.) ser&aacute;n tomados como adicional y cotizados aparte.</li>
</ul>
    <li>Si por motivos de fuerza mayor (medidas de fuerza, problemas energ&eacute;ticos, causas naturales, etc.) no se pudieran iniciar o continuar los trabajos, esto ser&aacute; notificado al cliente y se acordar&aacute; con &eacute;l de igual forma que el punto anterior.</li>
    <li>El presente presupuesto contempla documentaci&oacute;n de ingreso est&aacute;ndar (ART, Seguro de Vida, EPP, estudios m&eacute;dicos anuales regulares).</li>
</li>
</ol>-->
