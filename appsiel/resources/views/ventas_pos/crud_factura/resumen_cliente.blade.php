<div id="div_resumen_cliente" style="font-size:13px;">
    
    <hr>

    <div class="btn-group">
        <!-- <a class="btn btn-primary btn-sm" href="{ { url('/') . '/vtas_clientes/create?id=13&id_modelo=138&id_transaccion' }}" target="_blank" title="Crear Nuevo"><i class="fa fa-plus"></i></a> -->
        <button class="btn btn-primary btn-sm" id="btn_create_cliente" title="Crear Nuevo"><i class="fa fa-plus"></i></button>
    </div>
    <br><br>
    <table class="table table-bordered table-striped">
        <tr>
            <td>
                {{ Form::bsText( 'cliente_descripcion_aux', $cliente->tercero->descripcion, 'Cliente', ['id'=>'cliente_descripcion_aux', 'required'=>'required', 'readonly'=>'readonly', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'numero_identificacion', $cliente->tercero->numero_identificacion, config("configuracion.tipo_identificador").'/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'telefono1', $cliente->tercero->telefono1, 'Teléfono', ['id'=>'telefono1', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'direccion1', $cliente->tercero->direccion1, 'Dirección de entrega', ['id'=>'direccion1', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'email', $cliente->tercero->email, 'Email', ['id'=>'email', 'class'=>'form-control'] ) }}
            </td>
        </tr>
    </table>
</div>