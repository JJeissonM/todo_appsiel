<div id="div_resumen_cliente" style="font-size:13px;">
    <?php
        $cliente = isset($cliente) ? $cliente : null;
        $tercero_cliente = !is_null($cliente) ? $cliente->tercero : null;
        $cliente_descripcion = !is_null($tercero_cliente) ? $tercero_cliente->descripcion : '';
        $cliente_identificacion = !is_null($tercero_cliente) ? $tercero_cliente->numero_identificacion : '';
        $cliente_telefono = !is_null($tercero_cliente) ? $tercero_cliente->telefono1 : '';
        $cliente_direccion = !is_null($tercero_cliente) ? $tercero_cliente->direccion1 : '';
        $cliente_email = !is_null($tercero_cliente) ? $tercero_cliente->email : '';
    ?>
    
    <hr>

    <div class="btn-group">
        <!-- <a class="btn btn-primary btn-sm" href="{ { url('/') . '/vtas_clientes/create?id=13&id_modelo=138&id_transaccion' }}" target="_blank" title="Crear Nuevo"><i class="fa fa-plus"></i></a> -->
        <button class="btn btn-primary btn-sm" id="btn_create_cliente" title="Crear Nuevo"><i class="fa fa-plus"></i></button>
    </div>
    <br><br>
    <table class="table table-bordered table-striped">
        <tr>
            <td>
                {{ Form::bsText( 'cliente_descripcion_aux', $cliente_descripcion, 'Cliente', ['id'=>'cliente_descripcion_aux', 'required'=>'required', 'readonly'=>'readonly', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'numero_identificacion', $cliente_identificacion, config("configuracion.tipo_identificador").'/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'telefono1', $cliente_telefono, 'Teléfono', ['id'=>'telefono1', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'direccion1', $cliente_direccion, 'Dirección de entrega', ['id'=>'direccion1', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'email', $cliente_email, 'Email', ['id'=>'email', 'class'=>'form-control'] ) }}
            </td>
        </tr>
    </table>
</div>
