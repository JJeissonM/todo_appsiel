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

    @include('calificaciones.boletines.formatos.banner_colegio_con_escudo')
    <hr>
    
    <table class="info">
        <tr>
            <td width="55%"><b style="font-size: 16px">{{ $empresa->descripcion }}</b></td>
            <td width="45%" colspan="">
                <b style="font-size: 16px">{{ $doc_encabezado->tipo_documento_app->descripcion }} N. {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</b>
            </td>
        </tr>
        <tr>
            <td>Dirección: {{ $empresa->direccion1 }}</td>
            <td colspan="">
                <p>{{ config("configuracion.tipo_identificador") }}: {{ $empresa->numero_identificacion }} - {{ $empresa->digito_verificacion }}</p>
            </td>
        </tr>
        <tr>
            <!--pendiente-->
            <!--Ingresos brutos y fehca inicio actividad modificables-->
            <td>Telefono: {{ $empresa->telefono1 }}</td>
            <td>Mail: {{ $empresa->email }}</td>
        </tr>
    </table>
    
    <hr>
    <table class="info">
        <tr>
            <td width="12%"><b>Cliente:</b></td>
            <td width="43%">{{ $doc_encabezado->tercero_nombre_completo }}</td>
            <td width="20%"><b>Fecha:</b></td>
            <td width="25%">
                <?php
                    $fecha = date_create($doc_encabezado->fecha);
                    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                    $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
                ?>
                {{ $fecha_final }}
            </td>
        </tr>
        <tr>
            <td><b>CC:</b></td>
            <td>{{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td><b>Domicilio:</b> </td>
            <td>{{ $doc_encabezado->direccion1 }}</td>
            <td>
                <b>Condicion de Venta:</b>
            </td>
            <td style="text-transform: capitalize">{{ $doc_encabezado->condicion_pago }}</td>
        </tr>
        <tr>
            <td><b>Ciudad:</b></td>
            <td>{{ $tercero->ciudad->descripcion }}</td>
            @if($docs_relacionados[0] != '')
                <td>
                    <b>Remito No.: </b>
                </td>
                <td>
                    {!! $docs_relacionados[0] !!}
                </td>
            @endif
        </tr>
    </table>
    
    @include('matriculas.facturas.datos_estudiante')

<hr>
<br>

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

        $impuesto_iva = 0;//iva en firma

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
            <td colspan="4">&nbsp;</td>
        </tr>
    </tfoot>
</table>


@if( !is_null( $otroscampos ) )
    {!! $otroscampos->terminos_y_condiciones !!}
@endif
    
@include('ventas.incluir.factura_firma_totales2')
<hr>
<br>

@if( $etiquetas['pie_pagina'] != '')
    <p style="text-align: center; font-style: italic; width:100%;">
        <b> {!! $etiquetas['pie_pagina'] !!} </b>
    </p>
@endif

<h3>Detalles</h3>
<div class="info text-indent">
    {{  $doc_encabezado->descripcion }}
</div>
    
@include('calificaciones.boletines.pie_pagina')

</body>
</html>