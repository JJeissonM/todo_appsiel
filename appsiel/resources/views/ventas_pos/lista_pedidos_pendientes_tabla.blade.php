<style>

#myContentTable {
  border-collapse: collapse; /* Collapse borders */
  width: 100%; /* Full-width */
  border: 1px solid #ddd; /* Add a grey border */
  font-size: 18px; /* Increase font-size */
}

#myContentTable th, #myContentTable td {
  text-align: left; /* Left-align text */
  padding: 12px; /* Add padding */
}

#myContentTable tr {
  /* Add a bottom border to all table rows */
  border-bottom: 1px solid #ddd;
}

#myContentTable tr.header, #myContentTable tr:hover {
  /* Add a grey background color to the table header and on hover */
  background-color: #50B794;
  text-align: center;
}
</style>

<h3> Lista de pedidos pendientes</h3>
<hr>

<input id="mySearchInput" onkeyup="mySearchInputFunction()" type="text" placeholder="Buscar..." style="border: none; border-color: transparent; background-color: transparent; border: 1px solid rgb(100, 98, 202); width: 100%;font-size: 16px; height: 50px; margin: 1px; border-radius: 4px;">
<br/>

<div class="table-responsive">
    <table id="myContentTable">
        <tr class="header">
            <td style="width:20%;">Mesa</td>
            <td style="width:10%;">Fecha</td>
            <td style="width:20%;">Documento</td>
            <td style="width:20%;">Vendedor</td>
            <td style="width:10%;">Total</td>
            <td style="width:20%;">Acción</td>
        </tr>
        @foreach ($pedidos as $pedido)
            <tr>
                <td> {{ $pedido->tercero->descripcion }} </td>
                <td> {{ $pedido->fecha }} </td>
                <td class="text-center"> {{ $pedido->tipo_documento_app->prefijo }} {{ $pedido->consecutivo }} </td>
                <td> {{ $pedido->vendedor->tercero->descripcion }} </td>
                <td> ${{ number_format($pedido->valor_total,0,',','.') }} </td>
                <td>
                    <a class="btn btn-default btn-xs btn-detail" href="{{ url( 'pos_factura_crear_desde_pedido/' . $pedido->id . '?id=20&id_modelo=230&id_transaccion=47&pdv_id=' . $pdv_id . '&action=create_from_order' ) }}" title="Facturar" ><i class="fa fa-file"></i>&nbsp;</a>
                    &nbsp;&nbsp;&nbsp;
                    <a class="btn btn-default btn-xs btn-detail" href="{{ url( 'vtas_pedidos_imprimir/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42&formato_impresion_id=pos' ) }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a>
                    &nbsp;&nbsp;&nbsp;
                    <a class="btn btn-default btn-xs btn-detail" href="{{ url( 'vtas_pedidos/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42' ) }}" title="Consultar" id="btn_print" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>