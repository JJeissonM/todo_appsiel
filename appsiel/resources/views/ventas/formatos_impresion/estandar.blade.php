@extends('transaccion.formatos_impresion.estandar')

@section('documento_datos_adicionales')
        <td colspan="2">
            @include('ventas.incluir.metodo_y_condicion_pago')
        </td>
@endsection

@section('documento_transaccion_prefijo_consecutivo')
@if( !is_null( $resolucion ) )
{{ $resolucion->prefijo }} {{ $doc_encabezado->documento_transaccion_consecutivo }}
@else
{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endif
@endsection

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

@section('lbl_tercero')
Cliente:
@endsection

@section('encabezado_datos_adicionales')
    @include('matriculas.facturas.datos_estudiante')
    <br>
    <b>Atendido por: &nbsp;&nbsp;</b> {{ $doc_encabezado->vendedor->tercero->descripcion }}

    @include('ventas.formatos_impresion.detalles_factura_medica')
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {!! $doc_encabezado->descripcion !!}
@endsection

@section('tabla_registros_1')
    <?php 
            $total_cantidad = 0;
            $subtotal = 0;
            $total_descuentos = 0;
            $total_impuestos = 0;
            $total_factura = 0;
            $array_tasas = [];

            $impuesto_iva = 0;//iva en firma

            foreach($doc_registros as $linea )
            {

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
                $total_descuentos += $linea->valor_total_descuento;

                $total_abonos = 0;
                foreach ($abonos as $linea_abono)
                {
                    $total_abonos += $linea_abono->abono;
                }
                if($linea->valor_impuesto > 0){
                    $impuesto_iva = $linea->tasa_impuesto;
                }
            }
        ?>
    @include('ventas.incluir.lineas_registros_imprimir',compact('total_cantidad','total_factura'))
    @include('ventas.incluir.factura_detalles_impuestos',compact('array_tasas'))
    @if( !is_null( $otroscampos ) )
        {!! $otroscampos->terminos_y_condiciones !!}
    @endif
@endsection

@section('tabla_registros_2')
    @include('ventas.incluir.factura_firma_totales')
@endsection

@section('tabla_registros_3')
@include('transaccion.registros_contables')
@include('transaccion.auditoria')
@endsection

