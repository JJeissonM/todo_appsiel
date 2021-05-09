<h3> Lista de pedidos </h3>
<hr>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        {{ Form::bsTableHeader( ['Fecha', 'Documento', 'Cliente', 'Acción'] ) }}
        <tbody>
            @foreach ($pedidos as $pedido)
                <tr>
                    <td> {{ $pedido->fecha }} </td>
                    <td class="text-center"> {{ $pedido->tipo_documento_app->prefijo }} {{ $pedido->consecutivo }} </td>
                    <td> {{ $pedido->tercero->descripcion }} </td>
                    <td>
                        <a class="btn btn-default btn-xs btn-detail" href="{{ url('pos_factura_crear_desde_pedido/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42&formato_impresion_id=pos') }}" title="Facturar" ><i class="fa fa-file"></i>&nbsp;</a>
                        &nbsp;&nbsp;&nbsp;
                        <a class="btn btn-default btn-xs btn-detail" href="{{ url('vtas_pedidos_imprimir/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42&formato_impresion_id=pos') }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a>
                        &nbsp;&nbsp;&nbsp;
                        <a class="btn btn-default btn-xs btn-detail" href="{{ url('vtas_pedidos/' . $pedido->id . '?id=20&id_modelo=175&id_transaccion=42') }}" title="Consultar" id="btn_print" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>