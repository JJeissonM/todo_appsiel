<div class="btn-group pull-right componente_vendedores">
    @foreach( $vendedores AS $un_vendedor )
        @if( $un_vendedor->id == $vendedor->id )
            <button class="btn btn-default btn_vendedor vendedor_activo" data-vendedor_id="{{ $un_vendedor->id }}" data-vendedor_descripcion="{{ $un_vendedor->tercero->descripcion }}">{{$un_vendedor->tercero->nombre1}}</button>
        @else
            <button class="btn btn-default btn_vendedor" data-vendedor_id="{{ $un_vendedor->id }}" data-vendedor_descripcion="{{ $un_vendedor->tercero->descripcion }}">{{$un_vendedor->tercero->nombre1}}</button>
        @endif
    @endforeach
</div>