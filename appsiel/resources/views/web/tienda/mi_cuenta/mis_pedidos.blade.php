<?php
use App\Ventas\VtasDocEncabezado;

?>
<div class="page-title">
    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">MIS PEDIDOS</font></font></h1>
</div>

<div class="box-account box-info">
     <table>
         <thead class="bg-primary text-light">
              <th># Pedido</th>
              <th>Valor total</th>
              <th>Estado</th>
              <th>Ver detalles pedido</th>
              <th>Ver factura</th>
         </thead>
         <tbody>
            @foreach($doc_encabezados  as $key => $value)
            <?php
                $fact = null;
                $fact = VtasDocEncabezado::where('ventas_doc_relacionado_id',$value->id)->get()->first();
                //dd($fact);
            ?>
                <tr>
                    <td>PV-{{$value->consecutivo}}</td>
                    <td>${{$value->valor_total}}</td>
                    <td>{{$value->estado}}</td>
                    <td><a class="btn-lg btn-primary" target="_blank" href="{{url('/ecommerce/public/detallepedido').'/'.$value->id}}"><i class="fa fa-eye" aria-hidden="true"></i></a></td>
                    <td>
                        @if($fact != null)<a class="btn-lg btn-primary" target="_blank" href="{{url('imprimir_factura_web').'/'.$fact->id}}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        @endif
                    </td>
                </tr>
            @endforeach
         </tbody>
     </table>
</div>