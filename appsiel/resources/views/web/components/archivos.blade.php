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
            <h4>.:: En ésta Sección: Archivos ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Archivos</h4>
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
                                    @if($archivo!=null)
                                    <!-- EDITAR -->
                                    {!! Form::model($archivo,['route'=>['archivos.update',$archivo],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Título</label>
                                        <input type="text" class="form-control" value="{{$archivo->titulo}}" required name="titulo">
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" class="form-control" value="{{$archivo->descripcion}}" name="descripcion">
                                    </div>
                                    <div class="form-group">
                                        <label>Formato</label>
                                        <select class="form-control" name="formato">
                                            @if($archivo->formato=='LISTA')
                                            <option selected value="LISTA">ARCHIVOS EN FORMATO DE LISTA</option>
                                            <option value="BLOG">ARCHIVOS EN FORMATO DE BLOG</option>
                                            @else
                                            <option value="LISTA">ARCHIVOS EN FORMATO DE LISTA</option>
                                            <option selected value="BLOG">ARCHIVOS EN FORMATO DE BLOG</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                    @else
                                    <!-- CREAR -->
                                    {!! Form::open(['route'=>'archivos.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
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
                                        <label>Formato</label>
                                        <select class="form-control" name="formato">
                                            <option value="LISTA">ARCHIVOS EN FORMATO DE LISTA</option>
                                            <option value="BLOG">ARCHIVOS EN FORMATO DE BLOG</option>
                                        </select>
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
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Subir Archivos
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    @if($archivo != null)
                                    <!-- Formulario crear -->
                                    <div class="col-md-12" style="padding: 15px;">
                                        <button data-toggle="modal" data-target="#exampleModal" class="btn btn-primary waves-effect btn-block btn-sm">Abrir Formulario</button>
                                    </div>
                                    @else
                                    <p style="color: red;"> <i class="fa fa-warning"></i> Antes de añadir archivos debe configurar la sección.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Archivos en ésta Sección (Ver/Eliminar)
                                </button>
                            </h5>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12" style="padding: 15px;">
                                    <p style="color: #3d6983;">Para eliminar haga clic en el ícono <i class="fa fa-trash-o"></i></p>
                                    @if($archivo != null)
                                    @if(count($items)>0)
                                    @foreach($items as $a)
                                    <div class="col-md-12 article-ls" style="line-height: 5px; margin-bottom: 20px;">
                                        <div class="media service-box" style="margin: 10px !important; font-size: 14px;">
                                            {!! Form::open(['route'=>'archivos.delete','method'=>'POST','id'=>'form-archivo','class'=>'form-horizontal','files'=>'true'])!!}
                                            <input type="hidden" name="widget_id" value="{{$widget}}">
                                            <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                            <input type="hidden" name="id" value="{{$a->id}}">
                                            {!! Form::close() !!}
                                            <div onclick="eliminar()" class="pull-left" data-toggle="tooltip" data-placement="top" title="Eliminar Archivo">
                                                <i style="cursor: pointer;" class="fa fa-trash-o"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 style="font-size: 14px;" class="media-heading">{{$a->titulo}}</h6>
                                                <p>{{"Estado: ".$a->estado}}</p>
                                                <p><a href="{{asset('docs/'.$a->file)}}" target="_blank">Ver/Descargar Archivo</a></p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <p style="color: red;"> <i class="fa fa-warning"></i> No hay archivos publicados en ésta sección...</p>
                                    @endif
                                    @else
                                    <p style="color: red;"> <i class="fa fa-warning"></i> No hay archivos publicados en ésta sección...</p>
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
            @if($archivo != null)
            {!! Form::archivos($items,$archivo)!!}
            @else
            <p style="color: red;"> <i class="fa fa-warning"></i> La sección no ha sido configurada!</p>
            @endif
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Subir Archivo(s)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    {!! Form::open(['route'=>'archivos.archivostore','method'=>'POST','id'=>'form-article','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    @if($archivo!=null)
                    <input type="hidden" name="archivo_id" id="archivo_id" value="{{$archivo->id}}" />
                    @endif
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" class="form-control" required name="titulo">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input type="text" class="form-control" name="descripcion">
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">Estado</label>
                        <select class="form-control" name="estado">
                            <option value="VISIBLE">VISIBLE EN LA SECCIÓN</option>
                            <option value="OCULTO">OCULTO EN LA SECCIÓN</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Archivo</label>
                        {!! Form::file('archivo',['class'=>'form-control has-feedback-left','required'=>'required']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submit()">Guardar</button>
            </div>
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