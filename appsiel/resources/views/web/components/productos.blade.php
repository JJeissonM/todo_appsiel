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
        height: 100vh;
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
        height: 100vh;
        overflow-y: scroll;
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

    .activo {}

    .contenido {
        display: flex;
        padding: 5px;
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

    .btn-link {
        cursor: pointer;
    }

    .panel {
        background-color: #fff;
        border: 1px solid transparent;
        border-radius: 4px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        padding: 10px;
        margin-top: 5px;
        cursor: pointer;
        width: 100%;
    }

    .panel-title>a {
        padding: 10px;
        color: #000;
    }

    .panel-group .panel {
        margin-bottom: 0;
        border-radius: 4px;
    }

    .panel-default {
        border-color: #eee;
    }

    .article-ls {
        border: 1px solid;
        border-color: #3d6983;
        width: 100%;
        border-radius: 10px;
        -webkit-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
        -moz-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
        box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
    }

    .article-ls:focus {
        border-color: #9400d3;
    }
</style>

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Productos ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Productos</h4>
            <div class="col-md-12">
                <div id="accordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Configuración de la Sección
                                </button>
                            </h5>
                        </div>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    @if($pedido!=null)
                                    <!-- EDITAR -->
                                    {!! Form::model($pedido,['route'=>['pedidosweb.update',$pedido],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Título</label>
                                        <input type="text" class="form-control" value="{{$pedido->titulo}}" required name="titulo">
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" class="form-control" value="{{$pedido->descripcion}}" name="descripcion">
                                    </div>
                                    <div class="form-group">
                                        {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                    @else
                                    <!-- CREAR -->
                                    {!! Form::open(['route'=>'pedidosweb.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Título</label>
                                        <input type="text" class="form-control" required name="titulo">
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" class="form-control" name="descripcion">
                                    </div>
                                    <div class="form-group">
                                        {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($pedido != null)
            {!! Form::productos($items,$pedido)!!}
            @else
            <p style="color: red;"> <i class="fa fa-warning"></i> La sección no ha sido configurada!</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')

<script type="text/javascript">
    $(function() {

    });

    function submit() {
        $("#form-article").submit();
    }

    function eliminar(id) {
        $("#form-archivo").submit();
    }
</script>

@endsection