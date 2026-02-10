<div class="container-fluid">

    <?php
        $user = Auth::user();
        $vendedor_usuario = App\Ventas\Vendedor::where('user_id', $user->id)->get()->first();
        $mostrar_cocinas = true;

        if ( $user->hasRole('Mesero') && is_null($vendedor_usuario) ) {
            $mostrar_cocinas = false;
        }
    ?>

    @if( !$mostrar_cocinas )
        <div class="alert alert-warning">
            El usuario no esta asociado a un vendedor (mesero). Consulte con el administrador.
        </div>
    @else
        @if( (int)config('ventas_pos.imprimir_pedidos_en_cocina') )
            <div class="col">
                <br><br>
                Revisar pedidos
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
    @endif
</div>
