<div id="div_resumen_cliente" style="font-size:13px;">
    
    <hr>

    <div class="btn-group">
        <a class="btn btn-primary btn-sm" href="{{ url('/') . '/vtas_clientes/create?id=13&id_modelo=138&id_transaccion' }}" target="_blank" title="Crear Nuevo"><i class="fa fa-plus"></i></a>
    </div>
    <br><br>
    <table class="table table-bordered table-striped">
        <tr>
            <td>
                <label class="control-label col-sm-3 col-md-3" for="cliente_input">Cliente:</label>              
                <div class="col-sm-9 col-md-9">
                    <input class="form-control" id="cliente_input" autocomplete="off" required="required" name="cliente_input" type="text" value="{{ $cliente->tercero->descripcion }}"><div id="clientes_suggestions"> </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'numero_identificacion', $cliente->tercero->numero_identificacion, config("configuracion.tipo_identificador").'/CC', ['id'=>'numero_identificacion', 'required'=>'required', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'telefono1', $cliente->tercero->telefono1, 'Teléfono', ['id'=>'telefono1', 'required'=>'required', 'class'=>'form-control'] ) }}
            </td>
        </tr>
        <tr>
            <td>
                {{ Form::bsText( 'direccion1', $cliente->tercero->direccion1, 'Dirección de entrega', ['id'=>'direccion1', 'required'=>'required', 'class'=>'form-control'] ) }}
            </td>
        </tr>
    </table>
</div>