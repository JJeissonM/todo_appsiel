<div class="btn-group pull-right componente_vendedores">
    @foreach( $vendedores AS $vendedor )
        @if( $vendedor->id == $cliente->vendedor->id )
            <button class="btn btn-default btn_vendedor vendedor_activo" data-vendedor_id="{{ $vendedor->id }}" data-vendedor_descripcion="{{ $vendedor->tercero->descripcion }}">{{$vendedor->tercero->nombre1}}</button>
        @else
            <button class="btn btn-default btn_vendedor" data-vendedor_id="{{ $vendedor->id }}" data-vendedor_descripcion="{{ $vendedor->tercero->descripcion }}">{{$vendedor->tercero->nombre1}}</button>
        @endif
    @endforeach
</div>