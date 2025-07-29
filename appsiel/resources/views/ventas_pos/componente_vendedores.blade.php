<div class="btn-group pull-right componente_vendedores">
    @foreach( $vendedores AS $un_vendedor )
        <?php 
            $lbl_vendedor_activo = '';
            if( $un_vendedor->id == $vendedor->id ) {
                $lbl_vendedor_activo = ' vendedor_activo';
            }
        ?>
        <button class="btn btn-default btn_vendedor {{$lbl_vendedor_activo}}" data-vendedor_id="{{ $un_vendedor->id }}" data-vendedor_descripcion="{{ $un_vendedor->tercero->descripcion }}">{{$un_vendedor->tercero->nombre1}}</button>
    @endforeach
</div>