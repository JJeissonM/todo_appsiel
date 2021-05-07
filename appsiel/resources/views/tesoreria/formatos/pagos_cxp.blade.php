<!DOCTYPE html>
<html>
<head>
    <title>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</title>
    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
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
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <td style="border: solid 1px #ddd; margin-top: -40px;" width="70%">
                        @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir' ] )
                </td>
                <td style="border: solid 1px #ddd; padding-top: -20px;">

                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>

                    <table>
                        <tr>
                            <td><b>Documento:</b></td> <td> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }} </td>
                        </tr>
                        <tr>
                            <td><b>Fecha:</b></td> <td> {{ $doc_encabezado->fecha }} </td>
                        </tr>
                    </table>
                    
                </td>
            </tr>
        </table>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <td style="border: solid 1px #ddd;">
                    <b>Proveedor:</b> {{ $doc_encabezado->tercero_nombre_completo }}
                    <br/>
                    <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
                    <br/>
                    <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
                    <br/>
                    <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
                </td>
                <td style="border: solid 1px #ddd;">
                    <b>Factura del proveedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->doc_proveedor_prefijo }} - {{ $doc_encabezado->doc_proveedor_consecutivo }}
                    <br/>
                    <b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
                    <br/>
                    <b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}
                    <br/>
                    <b>Orden de compras: &nbsp;&nbsp;</b> {{ $doc_encabezado->orden_compras }}
                </td>
            </tr>
            <tr>        
                <td colspan="2" style="border: solid 1px #ddd;">
                    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
                </td>
            </tr>
        </table>
    </div>

    <br>

    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Productos comprados </span> </td>
            </tr>
        </table>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            {{ Form::bsTableHeader(['Cód.','Producto','Precio','IVA','Cantidad','Total']) }}
            <tbody>
                <?php 
                
                $total_cantidad = 0;
                $subtotal = 0;
                $total_impuestos = 0;
                $total_factura = 0;
                ?>
                @foreach($doc_registros as $linea )
                    <tr>
                        <td> {{ $linea->producto_id }} </td>
                        <td> {{ $linea->producto_descripcion }} </td>
                        <td> {{ '$ '.number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                        <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                        <td> {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->unidad_medida1 }} </td>
                        <td> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
                    </tr>
                    <?php 
                        $total_cantidad += $linea->cantidad;
                        $subtotal += (float)$linea->base_impuesto;
                        $total_impuestos += (float)$linea->valor_impuesto;
                        $total_factura += $linea->precio_total;
                    ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">&nbsp;</td>
                    <td> {{ number_format($total_cantidad, 0, ',', '.') }} </td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
        
    <div class="table-responsive">
        <table class="table table-bordered">
            <tr>
                <td> <span style="text-align: right; font-weight: bold;"> Subtotal: </span> $ {{ number_format($subtotal, 0, ',', '.') }}</td>
                <td> <span style="text-align: right; font-weight: bold;"> Impuestos: </span> $ {{ number_format($total_impuestos, 0, ',', '.') }}</td>
                <td> <span style="text-align: right; font-weight: bold;"> Total factura: </span> $ {{ number_format($total_factura, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    
    <div class="table-responsive">    
        <table class="table table-bordered">
            <tr>
                <td style="text-align: center; background-color: #ddd;"> <span style="text-align: right; font-weight: bold;"> Registros contables </span> </td>
            </tr>
        </table>
    </div>
    
    <div class="table-responsive">        
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cuenta</th>
                    <th>Débito</th>
                    <th>Crédito</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_valor_debito = 0;
                    $total_valor_credito = 0;
                @endphp
                @foreach( $registros_contabilidad as $fila )
                    <tr>
                        <td> {{ $fila['cuenta_codigo'] }}</td>
                        <td> {{ $fila['cuenta_descripcion'] }}</td>
                        <td> {{ number_format(  $fila['valor_debito'], 0, ',', '.') }}</td>
                        <td> {{ number_format(  $fila['valor_credito'] * -1, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $total_valor_debito += $fila['valor_debito'];
                        $total_valor_credito += $fila['valor_credito'] * -1;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>            
                    <tr>
                        <td colspan="2"> &nbsp; </td>
                        <td> {{ number_format( $total_valor_debito, 0, ',', '.') }}</td>
                        <td> {{ number_format( $total_valor_credito, 0, ',', '.') }}</td>
                    </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>