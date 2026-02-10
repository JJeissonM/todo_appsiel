<?php
    $user = Auth::user();
    $es_mesero = $user->hasRole('Mesero');
?>

<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Atentido Por</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if( $es_mesero )
                        <input type="hidden" id="modo_mesero_logueado" value="1">
                        <div id="lbl_vendedor_mesero" style="color: #574696;padding:10px;">
                            {{ $vendedor ? $vendedor->tercero->descripcion : 'Mesero no asociado' }}
                        </div>
                    @else
                        <div class="btn-group componente_vendedores">
                            @foreach( $vendedores AS $un_vendedor )
                                <?php 
                                    $user_id = 0;
                                    if ($un_vendedor->usuario != null ) {
                                        $user_id = $un_vendedor->usuario->id;
                                    }
                                ?>
                                @if( $un_vendedor->id == $vendedor->id )
                                    <button class="btn btn-default btn_vendedor" id="vendedor_default" data-vendedor_id="{{ $un_vendedor->id }}" data-user_id="{{ $user_id }}" data-vendedor_descripcion="{{ $un_vendedor->tercero->descripcion }}">{{$un_vendedor->tercero->nombre1}}</button>
                                @else
                                    <button class="btn btn-default btn_vendedor" data-vendedor_id="{{ $un_vendedor->id }}" data-user_id="{{ $user_id }}" data-vendedor_descripcion="{{ $un_vendedor->tercero->descripcion }}">{{$un_vendedor->tercero->nombre1}}</button>
                                @endif
                            @endforeach
                        </div>
                    
                        @include('ventas.pedidos.modal_password_vendedor')
                        <div id="lbl_vendedor_mesero" style="color: #574696;padding:10px;"></div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>
