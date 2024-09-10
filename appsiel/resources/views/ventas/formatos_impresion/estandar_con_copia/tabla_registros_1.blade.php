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

    $subtotal += $total_factura + $total_descuentos - $total_impuestos;
?>

@include('ventas.incluir.lineas_registros_imprimir',compact('total_cantidad','total_factura'))

@if( !is_null( $otroscampos ) )
    {!! $otroscampos->terminos_y_condiciones !!}
@endif

<table class="table table-bordered">
    <tr>
        <td width="50%"> 
            @include('ventas.incluir.factura_detalles_impuestos',compact('array_tasas'))
        </td>
        <td width="30%">
            @include('ventas.formatos_impresion.estandar_con_copia.resumen_totales', compact('subtotal', 'total_descuentos', 'impuesto_iva', 'total_impuestos', 'total_factura'))
        </td>
        <td>
            <div style="position: relative;">
                <div style="text-align: center; width:100%; padding: 0px 5px;">
                    <br><br><br>
                    __________________
                    <br>
                    <b> Firma <br> del aceptante </b> 
                </div>
            </div>
        </td>
    </tr>
</table>


