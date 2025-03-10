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
                <td style="width:20%;"> {{ $pedido->tercero->descripcion }} </td>
                <td> {{ $pedido->fecha }} </td>
                <td class="text-center" style="width:20%;"> {{ $pedido->tipo_documento_app->prefijo }} {{ $pedido->consecutivo }} </td>
                <td style="width:20%;"> {{ $pedido->vendedor->tercero->descripcion }} </td>
                <td> ${{ number_format($pedido->valor_total,0,',','.') }} </td>
                <td style="width:20%;">
                    &nbsp;

                    @if( (int)config('ventas_pos.imprimir_pedidos_en_cocina') )
                        <a class="btn btn-default btn-xs btn-detail btn_imprimir_en_cocina" href="{{ url( 'vtas_pedidos_imprimir/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42&formato_impresion_id=pos' ) }}" title="Imprimir en cocina" id="btn_print" data-lbl_consecutivo_doc_encabezado="{{ $pedido->consecutivo }}" data-lbl_fecha="{{ $pedido->fecha }}" data-lbl_cliente_descripcion="{{ $pedido->tercero_nombre_completo }}" data-lbl_descripcion_doc_encabezado="{{ $pedido->descripcion }}" data-lbl_total_factura="{{ '$ ' . number_format($pedido->valor_total,0,',','.') }}" data-nombre_vendedor="{{ $pedido->vendedor->tercero->descripcion }}" data-lineas_registros="{{ json_encode($pedido->lineas_registros_to_show()) }}"><i class="fa fa-btn fa-print"></i> Imprimir en cocina </a>
                        &nbsp;&nbsp;&nbsp;
                    @endif                
                    
                    @can( 'vtas_pos_anular_pedidos' )
                        &nbsp;&nbsp;&nbsp;

                        <button class="btn btn-danger btn-xs btn-detail btn_anular_pedido" data-href="{{ url( 'pos_anular_pedido/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42' ) }}" title="Anular Pedido" ><i class="fa fa-trash"></i>&nbsp;</button>
                    @endcan

                </td>
            </tr>
        @endforeach
    </table>
</div>