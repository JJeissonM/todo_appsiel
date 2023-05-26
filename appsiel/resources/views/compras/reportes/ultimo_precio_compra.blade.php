<?php
    use App\Compras\ComprasMovimiento;
?>
<h3 style="width: 100%; text-align: center;"> 
    Listado últimos precios de compras 
    <span style="background: yellow; color: red;">({!! $mensaje !!})</span> 
</h3>
<hr>

<div class="table-responsive">
    <table id="tbDatos" class="table table-striped">
        <thead>
            <tr>
                <th> Categoría </th>
                <th> Producto </th>
                <th> Cantidad </th>
                <th> Último Precio </th>
                <th> Total </th>
                <th> Fecha </th>
                <th> Documento </th>
                <th> CC/NIT - Proveedor </th>
            </tr>
        </thead>
        <tbody>
            @foreach( $listado as $item )
                <?php

                    $ultima_compra = ComprasMovimiento::get_ultimo_precio_producto($proveedor_id, $item->id, $item->inv_grupo_id);

                    if ( $ultima_compra->core_tipo_transaccion_id == null ) {
                        continue;
                    }

                    $precio_unitario = $ultima_compra->precio_unitario;
                    $precio_total = $ultima_compra->precio_total;
                    if ( !$iva_incluido )
                    {
                        $precio_unitario = $ultima_compra->base_impuesto / $ultima_compra->cantidad;
                        $precio_total = $ultima_compra->base_impuesto;
                    }
                ?>

                <tr>
                    <td> {{ $item->grupo_inventario->descripcion }} </td>
                    <td> 
                        {{ $item->get_value_to_show() }}
                        @if( $item->estado == 'Inactivo')
                            (Inactivo)
                        @endif
                    </td>
                    <td> ${{ number_format( $ultima_compra->cantidad, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $precio_unitario, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $precio_total, 2, ',', '.') }} </td>
                    <td> {{ $ultima_compra->fecha }} </td>
                    <td> {{ $ultima_compra->get_label_documento() }} </td>
                    <td> {{ $ultima_compra->proveedor->tercero->numero_identificacion }} - {{ $ultima_compra->proveedor->tercero->descripcion }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>