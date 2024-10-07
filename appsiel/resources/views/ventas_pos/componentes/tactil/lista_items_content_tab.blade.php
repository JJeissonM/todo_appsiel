<div id="{{str_slug($categoria)}}" class="tab-pane fade in {{$active}}">
    <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(auto,150px)); gap:10px;">
        @if(count($productos)>0)
            @foreach($productos as $item)
                @include('ventas_pos.componentes.tactil.dibujar_item')
            @endforeach
        @else
            <h5>No hay productos en esta categoría</h5>
        @endif
    </div>
</div>