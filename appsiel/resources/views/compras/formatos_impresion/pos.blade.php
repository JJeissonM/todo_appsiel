@extends('transaccion.formatos_impresion.pos')

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
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
    <div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Productos compados </div>

    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Ítem','Precio','IVA','Cant.','Total']) }}
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
                    <td class="text-center"> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->item->get_value_to_show(true) }} </td>
                    <td class="text-right"> ${{ number_format( $precio_original, 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td class="text-center"> {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->item->get_unidad_medida1() }} </td>
                    <td class="text-right"> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                </tr>

                @if( $linea->valor_total_descuento != 0 )
                    <tr>
                        <td colspan="4" style="text-align: right;">(Dcto. línea</td>
                        <td class="text-right" colspan="2"> -${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }}) </td>
                    </tr>                    
                @endif
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $subtotal += $subtotal_linea;
                    $total_impuestos += (float)$linea->valor_impuesto;
                    $total_factura += $linea->precio_total;
                    $total_descuentos += $linea->valor_total_descuento;
                ?>
            @endforeach
        </tbody>
        <!-- <tfoot>
            <tr>
                <td colspan="4">&nbsp;</td>
                <td> { { number_format($total_cantidad, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>-->
    </table>
@endsection


@section('tabla_registros_2')
    <table class="table table-bordered">
        <tr>
            <td> <span style="text-align: right; font-weight: bold;"> Subtotal: </span> ${{ number_format($subtotal, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Dctos: </span> ${{ number_format($total_descuentos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Impuestos: </span> ${{ number_format($total_impuestos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Total: </span> ${{ number_format($total_factura, 0, ',', '.') }}</td>
        </tr>

        @if( !is_null($resolucion) ) 
            <tr>
                <td colspan="4">
                    Documento con tipo de solicitud {{ $resolucion->tipo_solicitud }} por la DIAN. Resolución No. {{ $resolucion->numero_resolucion }} del {{ $resolucion->fecha_expedicion }}. Prefijo {{ $resolucion->prefijo }} desde {{ $resolucion->numero_fact_inicial }} hasta {{ $resolucion->numero_fact_final }}
                </td>
            </tr>
        @endif
    </table>
    <br>

    @if(isset($medios_pago))
        <div style="font-style: normal; font-weight: 100;">
            {!! $medios_pago !!}
        </div>    
    @endif

    <br>
    <br>
    <table>
        <tr>
            <td width="15%"> </td>
            <td width="30%"> _____________________ </td>
            <td width="10%"> </td>
            <td width="30%"> _____________________ </td>
            <td width="15%"> </td>
        </tr>
        <tr>
            <td width="15%"> </td>
            <td width="30%"> Emisor: {{ explode('@',$doc_encabezado->creado_por)[0] }} </td>
            <td width="10%"> </td>
            <td width="30%"> Proveedor </td>
            <td width="15%"> </td>
        </tr>
    </table>
@endsection