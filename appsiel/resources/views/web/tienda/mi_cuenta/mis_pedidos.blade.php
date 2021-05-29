<div class="page-title">
    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">MIS PEDIDOS</font></font></h1>
</div>

<div class="box-account box-info">
     <table>
         <thead>
              <th># pedido</th>
              <th>Fecha del Pedido</th>
              <th>Valor total</th>
              <th>Estado</th>
         </thead>
         <tbody>
            @foreach($doc_encabezados  as $key => $value)
                <tr>
                    <td><a target="_blank" href="{{url('/vtas_pedidos_imprimir').'/'.$value->id.'?id=13&id_modelo=175&id_transaccion=42&formato_impresion_id=1'}}">PV-{{$value->consecutivo}}</a></td>
                    <td>{{$value->fecha}}</td>
                    <td>${{$value->valor_total}}</td>
                    <td>{{$value->estado}}</td>
                </tr>
            @endforeach
         </tbody>
     </table>
</div>