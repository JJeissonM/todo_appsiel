<!DOCTYPE html>
<html>

<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
    <style type="text/css">
    
    </style>
</head>

<body>

    <table class="table ">
        <tr>
            <td style="/*border: solid 1px #574696;*/ border: none;" width="60%">
                <div class="headempresa">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </div>

            </td>
            <td style="/*border: solid 1px #574696; */">
                <div class="headdoc">
                    <b style="font-size: 1.2em; text-align: center; display: block;">
                        {{ $doc_encabezado->documento_transaccion_descripcion }}
                        <br />
                        <b>No.</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                    </b>
                    <table style="margin-top: 10px;">
                        <tr>
                            <td><b>Para:</b></td>
                            <td>{{ $doc_encabezado->tercero_nombre_completo }}</td>
                        </tr>
                        <tr>
                            <td><b>NIT: &nbsp;&nbsp;</b></td>
                            <td> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td><b>Fecha:</b></td>
                            <td>{{ $doc_encabezado->fecha }}</td>
                        </tr>
                    </table>

                </div>
            </td>
        </tr>
    </table>

    @if($doc_encabezado->estado == 'Anulado')
    <br><br>
    <div style="background-color: #ddd; width: 100%;">
        <strong>Documento Anulado</strong>
    </div>
    <br><br>
    @endif
<div class="subhead">
    <table class="table">
        <tr>
            <td >
                <b>Dirección:</b> {{ $doc_encabezado->tercero->direccion1 }},
                {{ $doc_encabezado->tercero->ciudad->descripcion }} -
                {{ $doc_encabezado->tercero->ciudad->departamento->descripcion }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>Teléfono:</b> {{ $doc_encabezado->tercero->telefono1 }}
                <br>

                
                {{ $empresa->descripcion }}, empresa creada con el objetivo de prestar servicios de alta calidad a
                nuestros clientes, presenta a ustedes la cotización de los siguientes artículos:
            </td>
        </tr>
    </table>
</div>
    

    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span
                    style="text-align: right; font-weight: bold;"> Productos cotizados </span> </td>
        </tr>
    </table>

    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Item','Producto','Cantidad','Vr. unitario','IVA','Total Bruto','Total']) }}
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
                <td> {{ $i }} </td>
                <?php 
                        $descripcion_item = $linea->producto_descripcion . ' (' . $linea->unidad_medida1 . ')';

                        if( $linea->unidad_medida2 != '' )
                        {
                            $descripcion_item = $linea->producto_descripcion . ' (' . $linea->unidad_medida1 . ') - Talla: ' . $linea->unidad_medida2;
                        }
                    ?>
                <td width="250px"> {{ $descripcion_item }} </td>
                <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                <td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 0, ',', '.') }}
                </td>
                <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                <td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) * $linea->cantidad, 0, ',', '.') }}
                </td>
                <td> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
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
<br>
@if( $doc_encabezado->descripcion != '' )
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> 
    <br>
    {!! $doc_encabezado->descripcion !!}
@endif
</body>

</html>