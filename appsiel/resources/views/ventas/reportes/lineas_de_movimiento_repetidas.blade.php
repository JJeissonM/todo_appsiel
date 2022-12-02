<h3 style="width: 100%; text-align: center;">
    Auditoría en movimientos de ventas 
</h3>
    <h6 style="width: 100%; text-align: center;">
        Los siguientes registros tienen diferencias entre las Cantidades vendidas y 
        las Cantidades que salieron del inventario. 
        <br>Por favor, informe a soporte técnico.</h6> 
<hr>

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th style="display: none;">core_tipo_transaccion_id </th>
                <th style="display: none;">core_tipo_doc_app_id </th>
                <th style="display: none;">consecutivo </th>
                <th style="display: none;">remision_doc_encabezado_id </th>
                <th style="display: none;">inv_producto_id </th>
                <th> Fecha </th>
                <th> Doc. Ventas </th>
                <th> Producto </th>
                <th> Cant. Vendida </th>
                <th> Cant. Salida Inventarios </th>
                <th> Diferencia </th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumen_ventas as $linea)

                <?php
                    if ($linea['diferencia'] == 0) {
                        continue;
                    }            
                ?>
                <tr>
                    <td style="display: none;"> {{ $linea['core_tipo_transaccion_id'] }} </td>
                    <td style="display: none;"> {{ $linea['core_tipo_doc_app_id'] }} </td>
                    <td style="display: none;"> {{ $linea['consecutivo'] }} </td>
                    <td style="display: none;"> {{ $linea['remision_doc_encabezado_id'] }} </td>
                    <td style="display: none;"> {{ $linea['inv_producto_id'] }} </td>
                    <td> {{ $linea['fecha'] }} </td>
                    <td> {{ $linea['doc_ventas'] }} </td>
                    <td> {{ $linea['item'] }} </td>
                    <td> {{ number_format( $linea['cant_venta'], 2, ',', '.') }} </td>
                    <td> {{ number_format( $linea['cant_inventario'], 2, ',', '.') }} </td>
                    <td> {{ number_format( $linea['diferencia'], 2, ',', '.') }} </td>
                </tr>
                
            @endforeach
        </tbody>
    </table>
</div>