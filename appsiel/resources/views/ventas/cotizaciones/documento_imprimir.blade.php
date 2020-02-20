<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .page-break {
            page-break-after: always;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table.table-bordered, .table-bordered>tbody>tr>td{
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <table class="table table-bordered">
        <tr>
            <td style="border: solid 1px #ddd; margin-top: -40px;">
                    @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
            </td>
            <td style="border: solid 1px #ddd; padding-top: -20px;">

                <b style="font-size: 1.6em; text-align: right; display: block;">
                    {{ $doc_encabezado->documento_transaccion_descripcion }}
                    <br/>
                    <b>No.</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                </b>
                <br/>
                <b>Para:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                <br/>
                <b>NIT: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
                <br/>
                <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                
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

    <table class="table table-bordered">
        <tr>
            <td style="border: solid 1px #ddd;">
                @if( $doc_encabezado->descripcion != '' )
                    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
                    <br>
                @endif
                {{ $empresa->descripcion }}, empresa creada con el objetivo de prestar servicios de alta calidad a nuestros clientes, presenta a ustedes la cotización de los siguientes artículos:
            </td>
        </tr>
    </table>

    <br>

    <table class="table table-bordered">
        <tr>
            <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Productos cotizados </span> </td>
        </tr>
    </table>
    
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Item','Producto','Cantidad','Vr. unitario','IVA','Total Bruto','Total']) }}
        </thead>
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
                    <td> {{ $i }} </td>
                    <td width="250px"> {{ $linea->producto_descripcion }} </td>
                    <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 0, ',', '.') }} </td>
                    <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) * $linea->cantidad, 0, ',', '.') }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
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

    <table class="table table-bordered">
        <tr>
            <td width="75%"> <b> &nbsp; </b> <br> </td>
            <td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;"> $ {{ number_format($subtotal, 2, ',', '.') }} </td>
        </tr>

        @foreach( $array_tasas as $key => $value )
            <tr>
                <td width="75%"> <b> &nbsp; </b> <br> </td>
                <td style="text-align: right; font-weight: bold;"> {{ $value['tipo'] }} </td>
                <td style="text-align: right; font-weight: bold;"> ${{ number_format( $value['valor_impuesto'], 0, ',', '.') }} </td>
            </tr>
        @endforeach
        <tr>
            <td width="75%"> <b> &nbsp; </b> <br> </td>
            <td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
    </table>

</body>
</html>