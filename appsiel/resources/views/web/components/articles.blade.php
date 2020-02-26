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
</style>

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Artículos ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Artículos</h4>
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
                                    @if($articles!=null)
                                    <!-- EDITAR -->
                                    {!! Form::model($articles,['route'=>['articles.update',$articles],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Formato</label>
                                        <select class="form-control" name="formato">
                                            @if($articles->formato=='LISTA')
                                            <option selected value="LISTA">ARTÍCULOS EN FORMATO DE LISTA</option>
                                            <option value="BLOG">ARTÍCULOS EN FORMATO DE BLOG</option>
                                            @else
                                            <option value="LISTA">ARTÍCULOS EN FORMATO DE LISTA</option>
                                            <option selected value="BLOG">ARTÍCULOS EN FORMATO DE BLOG</option>
                                            @endif
                                        </select>
                                    </div>
                                    <!---->
                                    <div class="form-group">
                                        <label>Órden</label>
                                        <select class="form-control" name="orden">
                                            @if($articles->orden=='ASC')
                                            <option selected value="ASC">MOSTRAR ANTIGUOS PRIMERO</option>
                                            <option value="DESC">MOSTRAR LOS MAS RECIENTES PRIMERO</option>
                                            @else
                                            <option value="ASC">MOSTRAR ANTIGUOS PRIMERO</option>
                                            <option selected value="DESC">MOSTRAR LOS MAS RECIENTES PRIMERO</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                    @else
                                    <!-- CREAR -->
                                    {!! Form::open(['route'=>'article.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Formato</label>
                                        <select class="form-control" name="formato">
                                            <option value="LISTA">ARTÍCULOS EN FORMATO DE LISTA</option>
                                            <option value="BLOG">ARTÍCULOS EN FORMATO DE BLOG</option>
                                        </select>
                                    </div>
                                    <!---->
                                    <div class="form-group">
                                        <label>Órden</label>
                                        <select class="form-control" name="orden">
                                            <option value="ASC">MOSTRAR ANTIGUOS PRIMERO</option>
                                            <option value="DESC">MOSTRAR LOS MAS RECIENTES PRIMERO</option>
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
                                    Crear Nuevo Artículo
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    @if($articles != null)
                                    <!-- Formulario crear -->
                                    <div class="col-md-12" style="padding: 15px;">
                                        <button onclick="editor()" data-toggle="modal" data-target="#exampleModal" class="btn btn-primary waves-effect btn-block btn-sm">Abrir Editor</button>
                                    </div>
                                    @else
                                    <p style="color: red;"> <i class="fa fa-warning"></i> Antes de añadir artículos debe configurar la sección.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Listado de Artículos en ésta Sección (Editar)
                                </button>
                            </h5>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    @if($articles != null)
                                    @if(count($articles->articles)>0)
                                    @foreach($articles->articles as $a)
                                    <div class="contenido">
                                        <div class="descripcion">
                                            <h5 class="titulo">titulo</h5>
                                            <p>descripcion</p>
                                            <a href="{{url('aboutus/create').'/'.$widget.$variables_url}}"> Editar</a>
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <p style="color: red;"> <i class="fa fa-warning"></i> No hay artículos publicados en ésta sección...</p>
                                    @endif
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
            @if($articles != null)
            {!! Form::articles($articles)!!}
            @endif
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Crear Artículo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div> 
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Recipient:</label>
                        <input type="text" class="form-control" id="recipient-name">
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Message:</label>
                        <textarea name="editor" class="form-control editor" id="message-text"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

<script type="text/javascript">
    $(function() {

    });

    function editor() {
        CKEDITOR.replace('editor', {
            height: 200,
            // By default, some basic text styles buttons are removed in the Standard preset.
            // The code below resets the default config.removeButtons setting.
            removeButtons: ''
        });
    }
</script>

@endsection