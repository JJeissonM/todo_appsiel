<h4 style="width: 100%; text-align: center;"> Remisiones/Devoluciones pendientes por facturar </h4>

<table id="myTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th style="display: none;"> ID Encabezado Remisión </th>
            <th> Tercero </th>
            <th> Documento </th>
            <th> Fecha </th>
            <th> Detalle </th>
            <th> Valor Remisión </th>
            <th> </th>
            <th class="td_boton"> </th>
        </tr>
    </thead>
    <tbody>
        <?php
            $j = 1;
            $gran_total_valor_documento = 0;
            $gran_total_documento_mas_iva = 0;
        ?>
           @foreach( $remisiones as $linea)         
                <tr class="fila-{{$j}}" id="{{ $linea->id }}">
                    <td style="display: none;"> {{ $linea->id }} </td>
                    <td> {{ $linea->tercero_nombre_completo }} </td>
                    <td> {{ $linea->documento_transaccion_prefijo_consecutivo }} </td>
                    <td> {{ $linea->fecha }} </td>
                    <td> {{ $linea->descripcion }} </td>
                    <td> ${{ number_format($linea->total_documento, 2, ',', '.') }} </td>
                    <td> </td>
                    <td style="display: none;" class="td_boton">
                        <a href="#" class="btn btn-success btn-xs btn_agregar_documento" style="display: none;"><i class="fa fa-check"></i></a>
                    </td>                
                </tr>
                <?php
                    $j++;
                    if ($j==3) {
                        $j=1;
                    }
                    $gran_total_valor_documento += $linea->total_documento;
                    $gran_total_documento_mas_iva += $linea->total_documento_mas_iva;
                ?>
            @endforeach

    </tbody>
    <tfoot>
        <tr class="fila-{{$j}}" >
            <td style="display: none;"> &nbsp; </td>
            <td colspan="4"> &nbsp; </td>
            <td> ${{ number_format($gran_total_valor_documento, 2, ',', '.') }} </td>
            <td>  </td>
            <td style="display: none;" class="td_boton"> &nbsp; </td>
        </tr>        
    </tfoot>
</table>

<h4 style="width: 100%; text-align: center;"> Detalles de productos a facturar </h4>
<span style="color: red;"> « Los precios son traidos de la lista de precios del cliente.»</span>
<table class="table table-bordered" style="background-color: white;">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Costo en Remisión</th>
                <th>Precio Unit. (IVA incluido)</th>
                <th>IVA</th>
                <th>Cantidad</th>
                <th>Total</th>
        </thead>
        <tbody>
            <?php
                $gran_total_cantidad = 0;
                $gran_total_precio_total = 0;
            ?>
           @foreach( $todos_los_productos as $linea)
                <tr>
                    <td> {{ $linea['producto_descripcion'] }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea['costo_unitario'], 2, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea['precio_unitario'], 2, ',', '.') }} </td>
                    <td> {{ $linea['tasa_impuesto'] }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea['cantidad'], 2, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea['precio_total'], 2, ',', '.') }} </td>
                </tr>
                <?php
                    $gran_total_cantidad += $linea['cantidad'];
                    $gran_total_precio_total += $linea['precio_total'];
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="text-align: right;color: red; font-weight: bold;">
                <td colspan="4"> Total Factura </td>
                <td style="text-align: right;"> {{ number_format( $gran_total_cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> ${{ number_format( $gran_total_precio_total, 2, ',', '.') }} </td>
            </tr> 
        </tfoot>
    </table>