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
                <h4>.:: En ésta Sección: Login ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Login</h4>
                @if($login == null)
                    <div class="add d-flex justify-content-end col-md-12">
                        <a data-toggle="modal" data-target="#exampleModal"
                           class="btn btn-primary waves-effect btn-block btn-sm"
                           style="color: white; font-weight: bold;"> Configurar</a>
                    </div>
                @else
                    <div class="col-md-12">
                            <div class="contenido">
                                <img src="{{url($login->imagen)}}" alt="" class="imagen">
                                <div class="descripcion" style="margin-left: 20px">
                                    <h5 class="titulo">{{$login->titulo}}</h5>
                                </div>
                                <a id="{{$login}}" onclick="editar(this.id)" data-toggle="modal" data-target="#Modaledit"
                                   class="btn"
                                   title="Editar" style="color: #45aed6"><i
                                            class="fa fa-edit"></i></a>
                                <a href="{{url('clientes/destroy').'/'.$login->id.$variables_url}}" class="btn"
                                   title="Eliminar Cliente"><i class="fa fa-eraser"></i></a>
                            </div>
                    </div>
                @endif
            </div>
            <div class="widgets" id="widgets" style="position: relative;">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($login != null)
                    @if($login->disposicion == 'DEFAULT')
{{--                        {!! Form::login($login)!!}--}}
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'login.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo del encabezado"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Logo</label>
                        <input name="imagen" type="file" placeholder="Agregar una imagen" required="required"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Ruta</label>
                        <input name="ruta" type="text" placeholder="Ruta de enlace al login"
                               class="form-control">
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
                <h5 class="modal-title">Editar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($login != null)
                        {!! Form::model($login,['route'=>['login.updated',$login->id],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <input type="hidden" name="login_id" id="login_id">
                        <div class="form-group">
                            <label>Titulo</label>
                            <input name="titulo" id="titulo" type="text"
                                   placeholder="Titulo del encabezado"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Logo</label>
                            <input name="imagen" id="logo" type="file" placeholder="Agregar una imagen"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Ruta</label>
                            <input name="ruta" type="text" id="ruta" placeholder="Ruta de enlace al login"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <br/><br/><a class="btn btn-danger" id="Modaledit" style="color: white"
                                         onclick="cerrar(this.id)">Cancelar</a>
                            <button class="btn btn-info" type="reset">Limpiar Formulario</button>
                            {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                        </div>
                    @endif
                    {!! Form::close() !!}
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

        function editar(obj) {
            var item = JSON.parse(obj);
            $("#titulo").attr('value', item.titulo);
            $("#login_id").attr('value', item.id);
            $("#ruta").attr('value',item.ruta);
        }
    </script>
@endsection