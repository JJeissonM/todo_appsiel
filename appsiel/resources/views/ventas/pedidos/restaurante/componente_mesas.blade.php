
<div class="row">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>MESA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="btn-group componente_mesas">
                        @foreach( $mesas AS $una_mesa )
                            <?php 
                                /*$aditional_class = '';
                                if( $una_mesa->id == $cliente->id )
                                {
                                    $aditional_class = 'mesa_activa';
                                }*/
                            ?>
                            <button class="btn btn-default btn_mesa" id="mesa_{{ $una_mesa->id }}" data-mesa_id="{{ $una_mesa->id }}" data-mesa_descripcion="{{ $una_mesa->tercero->descripcion }}" data-cliente_id="{{$una_mesa->id}}" data-zona_id="{{$una_mesa->zona_id}}" data-clase_cliente_id="{{$una_mesa->clase_cliente_id}}" data-liquida_impuestos="{{$una_mesa->liquida_impuestos}}" data-core_tercero_id="{{$una_mesa->core_tercero_id}}" data-lista_precios_id="{{$una_mesa->lista_precios_id}}" data-lista_descuentos_id="{{$una_mesa->lista_descuentos_id}}" data-vendedor_id="{{$una_mesa->vendedor_id}}" data-inv_bodega_id="{{$una_mesa->inv_bodega_id}}" data-nombre_cliente="{{$una_mesa->tercero->descripcion}}" data-numero_identificacion="{{$una_mesa->tercero->numero_identificacion}}" data-direccion1="{{$una_mesa->tercero->direccion1}}" data-telefono1="{{$una_mesa->tercero->telefono1}}" data-dias_plazo="{{$una_mesa->dias_plazo}}" disabled="disabled">
                                {{$una_mesa->tercero->nombre1}}
                            </button>
                        @endforeach
                    </div>

                    <div id="lbl_mesa_seleccionada" style="color: #574696;padding:10px;"></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>