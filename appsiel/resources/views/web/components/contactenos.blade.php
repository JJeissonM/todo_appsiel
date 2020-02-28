@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            overflow: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            overflow-x: hidden;
            width: 30%;
            height: 558px;
            margin-right: 0;
        }

        .list-group-item {
            background-color: transparent;
            font-size: 16px;
        }

        .list-group-item:hover {
            background-color: #3d6983;
            color: white;
            cursor: pointer;
        }

        .widgets {
            width: 70%;
        }

        .widgets img {
            width: 100%;
            object-fit: cover;
            height: 72.5vh;
            max-width: 100%;
        }

        .widgets .card-body {
            position: relative;
        }

        .activo {

        }

        .contenido {
            display: flex;
            padding: 5px;
            margin-top: 10px;
            border: 1px solid #3d6983;
            border-radius: 5px;
        }

        .contenido img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .descripcion {
            padding: 5px;
        }

        .descripcion h5 {
            color: black;
            font-size: 16px;
        }

        .add {
            margin-top: 20px;
        }

        .add a {
            color: #1c85c4;
        }

    </style>

@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
                <h4>.:: En ésta Sección: Contáctenos ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Contáctenos</h4>
                <div class="col-md-12">
                    @if($contactenos != null)
                        <div class="contenido">
                            <div class="descripcion">
                                <h5 class="titulo">{{$contactenos->empresa}}</h5>
                                <p><strong>Tel:</strong> {{str_limit($contactenos->telefono,30)}}</p>
                                <p><strong>Dir:</strong> {{str_limit($contactenos->direccion,30)}}</p>
                                <p><strong>Ciudad:</strong> {{str_limit($contactenos->ciudad,30)}}</p>
                                <p><strong>Email:</strong> {{str_limit($contactenos->correo,30)}}</p>
                            </div>
                        </div>
                        <div class="col-md-6 justify-content-end">
                            <a data-toggle="modal" data-target="#Modaledit"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold; margin-top: 10px;"> Editar Formulario </a>
                        </div>
                    @else
                        <div class="add d-flex justify-content-end col-md-12">
                            <a data-toggle="modal" data-target="#exampleModal"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Agregar</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($contactenos != null)
                    {!! Form::contactenos($contactenos)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Formulario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'contactenos.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Nombre de la Empresa</label>
                        <input name="empresa" type="text" placeholder="Nombre de la empresa o persona"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Telefono</label>
                        <input name="telefono" type="number" placeholder="Telefono de contacto" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Ciudad</label>
                        <input name="ciudad" type="text" placeholder="Ciudad" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input name="correo" type="email" placeholder="Correo electronico de contacto"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input name="direccion" type="text" placeholder="Dirección de contacto" class="form-control">
                    </div>
                    <div class="form-group">
                        <br/><br/>
                        <a class="btn btn-danger" id="exampleModal" style="color: white" onclick="cerrar(this.id)">
                            Cancelar
                        </a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modaledit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Formulario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($contactenos != null)
                        {!! Form::model($contactenos,['route'=>['servicios.updated',$contactenos],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <input type="hidden" name="servicio" value="{{$contactenos->id}}">
                        <div class="form-group">
                            <label>Nombre de la Empresa</label>
                            <input name="empresa" type="text" placeholder="Nombre de la empresa o persona"
                                   value="{{$contactenos->empresa}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Telefono</label>
                            <input name="telefono" type="number" placeholder="Telefono de contacto"
                                   value="{{$contactenos->telefono}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Ciudad</label>
                            <input name="ciudad" type="text" placeholder="Ciudad" value="{{$contactenos->ciudad}}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Correo</label>
                            <input name="correo" type="email" placeholder="Correo electronico de contacto"
                                   value="{{$contactenos->correo}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <input name="direccion" type="text" placeholder="Dirección de contacto"
                                   value="{{$contactenos->direccion}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <br/><br/><a class="btn btn-danger" id="Modaledit" style="color: white"
                                         onclick="cerrar(this.id)">Cancelar</a>
                            <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                            {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                        </div>
                        {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@section('script')
    <script type="text/javascript">
        function cerrar(id) {
            $("#" + id).modal('hide');
            $("#" + id).removeClass('modal-open');
            $('.' + id).remove();
        }
    </script>
@endsection
