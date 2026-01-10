<?php    
    $total_cantidad = 0;
    $subtotal = 0;
    $total_impuestos = 0;
    $total_factura = 0;
    $array_tasas = [];

    $lbl_impuesto = config('ventas.etiqueta_impuesto_principal');
    $impuesto_impoconsumo_id = (int) config('contabilidad.impoconsumo_default_id');
    $tax_category_default = null;
    $tax_category_impoconsumo = null;

    foreach ($doc_registros as $linea) {
        if (!is_null($linea->impuesto)) {
            if ((int) $linea->impuesto_id === $impuesto_impoconsumo_id) {
                $tax_category_impoconsumo = 'INC';
                break;
            }

            if ($tax_category_default === null) {
                $tax_category_default = $linea->impuesto->tax_category;
            }
        }
    }

    $lbl_impuesto = $tax_category_impoconsumo ?? $tax_category_default ?? $lbl_impuesto;
?>
@foreach($doc_registros as $linea )
    <?php 
        $referencia = '';
        if($linea->referencia != '')
        {
            $referencia = ' - ' . $linea->referencia;
        }
        // Si la tasa no está en el array, se agregan sus valores por primera vez
        if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
        {
            // Clasificar el impuesto
            $array_tasas[$linea->tasa_impuesto]['tipo'] = $lbl_impuesto.'='.$linea->tasa_impuesto.'%';
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
    ?>
@endforeach

<div style="text-align: center; width: 100%; font-weight: bold; background: #ddd;">Impuestos</div>
<table style="width: 100%; font-size: 0.9em;" class="table table-bordered">
    <thead>
        <tr>
            <th>Tipo producto</th>
            <th>Vlr. Compra</th>
            <th>Base</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach( $array_tasas as $key => $value )
        <tr>
            <td> {{ $value['tipo'] }} </td>
            <td style="text-align: right"> ${{ number_format( $value['precio_total'], 0, ',', '.') }} </td>
            <?php 
                $base = $value['base_impuesto'];
            ?>
            <td style="text-align: right"> ${{ number_format( $base, 0, ',', '.') }} </td>
            <td style="text-align: right"> ${{ number_format( $value['valor_impuesto'], 0, ',', '.') }} </td>
        </tr>
        @endforeach
    </tbody>
</table>
