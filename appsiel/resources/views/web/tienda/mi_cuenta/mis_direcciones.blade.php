<div class="page-title">
    <h1>
        AGREGAR NUEVA DIRECCIÓN 
        <button id="btn_create_general" class=" pull-right" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Crear nuevo"><i class="fa fa-plus"></i></button>
    </h1>
    @if(Session::has('domi_message'))
    <div class="container-fluid">
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <em> {!! session('domi_message') !!}</em>
        </div>
    </div>
    @endif
</div>
<div class="col2-set addresses-list">  

    <div class="col-12 addresses-primary" style="max-width: 100%">
        <?php 
            $direcciones = $cliente->direcciones_entrega;
        ?>
        <h2 style="text-transform: uppercase;">
            Direcciones adicionales
            
        </h2>
        <ol>
            @if( empty( $direcciones->toArray() ) )
                <li class="item empty">
                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">No tiene entradas de direcciones adicionales en su libreta de direcciones.</font></font></p>
                </li>
            @else
                @foreach( $direcciones AS $direccion )
                    <li class="item rounded @if($direccion->por_defecto == 1) border border-primary @endif">
                        <div class="pull-right" style="font-size: 18px">
                            <form action="{{ url('vtas_direcciones_entrega') . '/' . $direccion->id }}" method="POST" class="form_actualizar">
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="url_id_modelo" value="300">
                                <input type="hidden" name="por_defecto" value="1">
                                @if($direccion->por_defecto == 1)
                                    <button class="btn_setdefault_direccion" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Activado por Defecto" disabled><i class="fa fa-toggle-on"></i>
                                    </button>
                                @else
                                    <button class="btn_setdefault_direccion" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Activar por Defecto"><i class="fa fa-toggle-off"></i>
                                    </button>    
                                @endif
                            </form> 
                            <button class="btn_edit_direccion" data-direccion_cliente_id="{{$direccion->id}}" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Modificar"><i class="fa fa-edit"></i></button>

                            <form action="{{ url('vtas_direcciones_entrega') . '/' . $direccion->id }}" method="POST" class="form_eliminar">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="url_id_modelo" value="300">
                                <button class="btn_delete_direccion" style="border: 0px solid; padding: 0px;margin: 0px;height: 0px;" title="Eliminar"><i class="fa fa-trash-o"></i></button>
                            </form>                            
                        </div>                            
                        <address>
                            <b>{{$direccion->nombre_contacto}}</b><br>
                            {{$direccion->direccion1}}, {{$direccion->barrio}}<br>
                            {{$direccion->ciudad->descripcion }}, {{ $direccion->ciudad->departamento->descripcion }}, {{$direccion->codigo_postal}}<br>
                            Tel.: {{$direccion->telefono1}}<br>
                            Por defecto: {{ $direccion->lbl_por_defecto() }}
                        </address>
                    </li>
                @endforeach
            @endif
        </ol>
    </div>
</div>

@include('components.design.ventana_modal', [ 'titulo' => '', 'texto_mensaje' => ''])