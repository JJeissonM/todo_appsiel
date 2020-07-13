@extends('transaccion.formatos_impresion.pos')

@section('encabezado_2')
    @if( $etiquetas['encabezado'] != '')
        <table style="width: 100%;">
            <tr>
                <td style="border: solid 1px #ddd; text-align: center; font-family: Courier New; font-style: italic;">
                    <b> {!! $etiquetas['encabezado'] !!} </b> 
                </td>
            </tr>
        </table>
    @endif
@endsection

@section('fila_datos_adicionales')
    @if( $doc_encabezado->condicion_pago == 'credito' )
        <tr>
            <td colspan="2">
                <b>Fecha vencimiento:</b> {{ $doc_encabezado->fecha_vencimiento }}
            </td>
        </tr>        
    @endif
@endsection

@section('documento_transaccion_prefijo_consecutivo')
    @if( !is_null( $resolucion ) )
        {{ $resolucion->prefijo }} {{ $doc_encabezado->documento_transaccion_consecutivo }}
    @else
        {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
    @endif
@endsection

@section('lbl_tercero')
    Cliente:
@endsection

@section('encabezado_datos_adicionales')
    <br>
    <b>Atendido por: &nbsp;&nbsp;</b> {{ $doc_encabezado->vendedor_nombre_completo }}
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

<?php 
    //dd( $doc_registros->sum('valor_total_descuento') );
?>

@section('tabla_registros_1')
    <table style="width: 100%;">
        {{ Form::bsTableHeader(['Producto','Cant. (Precio)','IVA','Total']) }}
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $subtotal = 0;
            $total_impuestos = 0;
            $total_factura = 0;
            $array_tasas = [];
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td> 
                        {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->unidad_medida1 }}  (${{ number_format( $linea->precio_unitario, 0, ',', '.') }})
                    </td>
                    <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.') }}% </td>
                    <td> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                </tr>

                @if( $linea->valor_total_descuento != 0 )
                    <tr>
                        <td colspan="3" style="text-align: right;">Dcto.</td>
                        <td> ( -${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} ) </td>
                    </tr>                    
                @endif
                <?php

                    // Si la tasa no está en el array, se agregan sus valores por primera vez
                    if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
                    {
                        // Clasificar el impuesto
                        $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA='.$linea->tasa_impuesto.'%';
                        if ( $linea->tasa_impuesto == 0)
                        {
                            $array_tasas[$linea->tasa_impuesto]['tipo'] = 'EX=0%';
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

                    $total_cantidad += $linea->cantidad;
                    $subtotal += (float)$linea->base_impuesto * (float)$linea->cantidad;
                    $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    $total_factura += $linea->precio_total;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td colspan="2" style="text-align: right;"> Total factura: </td>
                <td> ${{ number_format( $total_factura, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection

@section('tabla_registros_2')
    <table style="width: 100%;" class="table table-bordered">
        <thead>
            <tr>
                <th>Tipo producto</th>
                <th>Vlr. Compra</th>
                <th>Base IVA</th>
                <th>Vlr. IVA</th>
            </tr>            
        </thead>
        <tbody>
            @foreach( $array_tasas as $key => $value )
                <tr>
                    <td> {{ $value['tipo'] }} </td>
                    <td> ${{ number_format( $value['precio_total'], 0, ',', '.') }} </td>
                    <?php 
                        $base = $value['base_impuesto'];
                        /*if( $value['tasa'] == 0 )
                        {
                            $base = 0;
                        }*/
                    ?>
                    <td> ${{ number_format( $base, 0, ',', '.') }} </td>
                    <td> ${{ number_format( $value['valor_impuesto'], 0, ',', '.') }} </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4">
                    &nbsp;
                </td>
            </tr>
            @if( !is_null($resolucion) ) 
                <tr>
                    <td colspan="4">
                        Factura {{ $resolucion->tipo_solicitud }} por la DIAN. Resolución No. {{ $resolucion->numero_resolucion }} del {{ $resolucion->fecha_expedicion }}. Prefijo {{ $resolucion->prefijo }} desde {{ $resolucion->numero_fact_inicial }} hasta {{ $resolucion->numero_fact_final }}
                    </td>
                </tr>
            @endif
            <tr>
                <td colspan="4">
                    Esta factura se asimila en todos sus efectos a una Letra de Cambio según Art. 774 del Código de Comercio.
                </td>
            </tr>

        </tbody>
    </table>
@endsection

@section('lbl_firma')
    Firma del aceptante:
@endsection

@section('firma_fila_adicional')
    @if( $etiquetas['pie_pagina'] != '')
        <tr>
            <td style="border: solid 1px #ddd; text-align: center; font-style: italic;">
                <b> {!! $etiquetas['pie_pagina'] !!} </b>
            </td>
        </tr>
    @endif    
@endsection

</body>
</html>