<div class="container-fluid">

    
	@if( (int)config('ventas_pos.imprimir_pedidos_en_cocina') )
        <div class="col">
            <br><br>
            <a class="btn btn-default btn-bg btn-info" href="{{ url( 'vtas_mesero_listado_pedidos_pendientes' . '?id=13' ) }}" title="Listado Pedidos Pendientes"><i class="fa fa-btn fa-list"></i> Listado de Pedidos Pendientes</a>
        </div>
    @endif	

    <h3>Toma de pedidos</h3>
    <hr>
    <?php 
        $cocinas = config('pedidos_restaurante.cocinas');
    ?>

    @foreach($cocinas as $index => $cocina)
        <div class="col-md-3 col-xs-6" style="padding: 10px;">
            <a href="{{url( 'vtas_pedidos_restaurante/create?id=13&id_modelo=320&id_transaccion=60' ) . '&grupo_inventarios_id=' . $cocina['grupo_inventarios_id'] . '&cocina_index=' . $index }}" class="btn btn-block btn-default">
                <br>
                <img style="width: 100px; height: 100px; border-radius:4px;" src="{{$cocina['url_imagen']}}">
                <p style="text-align: center; white-space: nowrap; overflow: hidden; white-space: initial;">{{ $cocina['label'] }}</p>
            </a>
        </div>
    @endforeach
</div>