@extends('transaccion.formatos_impresion.estandar')

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endsection

@section('documento_datos_adicionales')
    <br/>
    <b>Entrada/Devolución:</b> {{ $doc_encabezado->documento_remision_prefijo_consecutivo }}
@endsection

@section('lbl_tercero')
    Proveedor:
@endsection

@section('encabezado_datos_adicionales')
    <br>
    <b>Factura del proveedor: &nbsp;&nbsp;</b> {{ $doc_encabezado->doc_proveedor_prefijo }} - {{ $doc_encabezado->doc_proveedor_consecutivo }}
    <br/>
    <b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
    <br/>
    <b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}
    <br/>
    <b>Orden de compras: &nbsp;&nbsp;</b> {{ $doc_encabezado->orden_compras }}
    <br/>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('tabla_registros_1')

    <div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Productos comprados </div>

    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Cantidad','Precio','Dcto.','IVA','Total']) }}
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $subtotal = 0;
            $total_impuestos = 0;
            $total_factura = 0;
            $total_descuentos = 0;
            ?>
            @foreach($doc_registros as $linea )

                <?php 
                    $precio_original = $linea->precio_unitario + ( $linea->valor_total_descuento / $linea->cantidad );
                    $subtotal_linea = ( $linea->cantidad * $precio_original ) - $linea->valor_impuesto;
                ?>
                <tr>
                    <td> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->unidad_medida1 }} </td>
                    <td style="text-align: right;"> {{ '$ '.number_format( $precio_original, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->tasa_descuento, 0, ',', '.').'%' }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $subtotal += $subtotal_linea;
                    $total_impuestos += (float)$linea->valor_impuesto;
                    $total_descuentos += (float)$linea->valor_total_descuento;
                    $total_factura += $linea->precio_total;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td style="text-align: right;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td colspan="3">&nbsp;</td>
                <td style="text-align: right;"> {{ number_format($total_factura, 2, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
@endsection

@section('tabla_registros_2')
    <br>
    <table class="table table-bordered">
        <tr>
            <td> <span style="text-align: right; font-weight: bold;"> Subtotal: </span> ${{ number_format($subtotal, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Descuentos: </span> ${{ number_format($total_descuentos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Impuestos: </span> ${{ number_format($total_impuestos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Total factura: </span> ${{ number_format($total_factura, 0, ',', '.') }}</td>
        </tr>
    </table>
@endsection

@section('tabla_registros_3')
    @include('transaccion.registros_contables')
    @include('transaccion.auditoria')
@endsection