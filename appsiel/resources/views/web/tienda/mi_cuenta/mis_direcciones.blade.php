<div class="page-title">
    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">AGREGAR NUEVA DIRECCIÓN</font></font></h1>
</div>
<div class="col2-set addresses-list">
    
    <div class="col-1 addresses-primary" style="max-width: 100%">
        <h2 style="text-transform: uppercase;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección por defecto</font></font></h2>
        <ol>           
            <li class="item">
                <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                            {{$cliente->nombre_completo}}
                        </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                            {{$cliente->direccion1}}
                        </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                            {{$cliente->barrio}}
                        </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                            {{$cliente->ciudad}}, {{$cliente->departamento}}, {{$cliente->codigo_postal}}
                        </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                            {{$cliente->pais}}
                        </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                            Tel.: {{$cliente->telefono1}}
                        </font></font>
                </address>
            </li>
        </ol>
    </div>

    <div class="col-2 addresses-primary" style="max-width: 100%">
        <?php 
            $direcciones = $cliente->direcciones_entrega;
        ?>
        <h2 style="text-transform: uppercase;">
            <font style="vertical-align: inherit;">
                <font style="vertical-align: inherit;">Direcciones adicionales</font>
            </font>
            <button id="btn_create_general" class="pull-right" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Crear nuevo"><i class="fa fa-plus"></i></button>
        </h2>
        <ol>
            @if( empty( $direcciones->toArray() ) )
                <li class="item empty">
                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">No tiene entradas de direcciones adicionales en su libreta de direcciones.</font></font></p>
                </li>
            @else
                @foreach( $direcciones AS $direccion )
                    <li class="item">
                        <div class="pull-right">
                            <button class="btn_edit_direccion" data-direccion_cliente_id="{{$direccion->id}}" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Modificar"><i class="fa fa-edit"></i></button>

                            <form action="{{ url('vtas_direcciones_entrega') . '/' . $direccion->id }}" method="POST" class="form_eliminar">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="url_id_modelo" value="300">
                                <button class="btn_delete_direccion" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                            </form>

                            
                        </div>
                            
                        <address>
                            <font style="vertical-align: inherit;">
                                <font style="vertical-align: inherit;">
                                    {{$direccion->nombre_contacto}}
                                </font>
                            </font><br>
                            <font style="vertical-align: inherit;">
                                <font style="vertical-align: inherit;">
                                    {{$direccion->direccion1}}
                                </font>
                            </font><br>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">
                                    {{$direccion->barrio}}
                                </font>
                            </font><br>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">
                                    {{$direccion->ciudad->descripcion }}, {{ $direccion->ciudad->departamento->descripcion }}, {{$direccion->codigo_postal}}
                                </font>
                            </font><br>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">
                                    {{$cliente->pais}}
                                </font>
                            </font><br>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">
                                    Tel.: {{$direccion->telefono1}}
                                </font>
                            </font>
                        </address>
                    </li>
                @endforeach
            @endif
        </ol>
    </div>
</div>

@include('components.design.ventana_modal', [ 'titulo' => '', 'texto_mensaje' => ''])