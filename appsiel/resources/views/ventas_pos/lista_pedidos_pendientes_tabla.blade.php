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

#myContentTable tr:hover {
  /* Add a grey background color to the table header and on hover */
  background-color: #50B794;
  text-align: center;
}

#myContentTable1 {
  border-collapse: collapse; /* Collapse borders */
  width: 100%; /* Full-width */
  border: 1px solid #ddd; /* Add a grey border */
  font-size: 18px; /* Increase font-size */
}

#myContentTable1 th, #myContentTable1 td {
  text-align: left; /* Left-align text */
  padding: 12px; /* Add padding */
}

#myContentTable1 tr.header {
  /* Add a grey background color to the table header and on hover */
  background-color: #50B794;
  text-align: center;
}
</style>

<h3> Lista de pedidos pendientes</h3>
<hr>

<!-- 
<p>
  <label class="checkbox-inline"><input type="checkbox" value="">Agrupar todos los pedidos de la misma Mesa/Cliente para facturar</label>
</p>

-->

<input id="mySearchInput" onkeyup="mySearchInputFunction()" type="text" placeholder="Buscar..." style="border: none; border-color: transparent; background-color: transparent; border: 1px solid rgb(100, 98, 202); width: 100%;font-size: 16px; height: 50px; margin: 1px; border-radius: 4px;">
<br/>

<div class="table-responsive">
    <table id="myContentTable1">
      <tr class="header">
        <td style="width:20%;">Mesa/Cliente</td>
        <td style="width:10%;">Fecha</td>
        <td style="width:20%;">Documento</td>
        <td style="width:20%;">Vendedor</td>
        <td style="width:10%;">Total</td>
        <td style="width:20%;">Acción</td>
      </tr>
    </table>
    <table id="myContentTable">
        @foreach ($pedidos as $pedido)
            <tr>
                <td> {{ $pedido->tercero->descripcion }} </td>
                <td> {{ $pedido->fecha }} </td>
                <td class="text-center"> {{ $pedido->tipo_documento_app->prefijo }} {{ $pedido->consecutivo }} </td>
                <td> {{ $pedido->vendedor->tercero->descripcion }} </td>
                <td> ${{ number_format($pedido->valor_total,0,',','.') }} </td>
                <td>
                  <button class="btn btn-warning btn-xs btn-detail cargar_pedido_para_facturar" data-href="{{ url( 'pos_cargar_pedido/' . $pedido->id . '?id=20&id_modelo=230&id_transaccion=47&pdv_id=' . $pdv_id . '&action=create_from_order' ) }}" title="Facturar" ><i class="fa fa-file"></i>&nbsp;</button>
                  &nbsp;&nbsp;&nbsp;

                  @if( !(int)config('ventas_pos.imprimir_pedidos_en_cocina') )
                    <a class="btn btn-default btn-xs btn-detail" href="{{ url( 'vtas_pedidos_imprimir/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42&formato_impresion_id=pos' ) }}" title="Imprimir" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a>
                    &nbsp;&nbsp;&nbsp;
                  @endif
                  
                  <a class="btn btn-default btn-xs btn-detail" href="{{ url( 'vtas_pedidos/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42' ) }}" title="Consultar" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>                  
                  
                  @can( 'vtas_pos_anular_pedidos' )
                    &nbsp;&nbsp;&nbsp;

                    <button class="btn btn-danger btn-xs btn-detail btn_anular_pedido" data-href="{{ url( 'pos_anular_pedido/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42&pdv_id=' . $pdv_id ) }}" title="Anular Pedido" ><i class="fa fa-trash"></i>&nbsp;</button>
                  @endcan

                </td>
            </tr>
        @endforeach
    </table>
</div>