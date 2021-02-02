@extends('web.templates.main')

@section('style')
<style>
    .card-body {
        padding: 0 !important;
        overflow: hidden;
        width: 100%;
        height: 100%;
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
        overflow-y: scroll;
        width: 70%;
        height: 100vh;
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
        justify-content: space-between;
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
            <h4>.:: En ésta Sección: Parallax ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Parallax</h4>
            @if($parallax != null)
            <div class="descripcion" style="text-align: center; margin-top: 20px;">
                <h5 class="titulo">{{$parallax->titulo}}</h5>
                <p>{{$parallax->descripcion}}</p>
            </div>
            <div class="col-md-12 add d-flex">
                <div class="col-md-6">
                    <a href="{{url('parallax/destroy').'/'.$parallax->id.$variables_url}}" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Eliminar Parallax</a>
                </div>
                <div class="col-md-6 justify-content-end">
                    <a data-toggle="modal" data-target="#Modaledit" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Editar Sección </a>
                </div>
            </div>
            @else
            <div class="add d-flex justify-content-end col-md-12">
                <a data-toggle="modal" data-target="#exampleModal" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold; cursor: pointer;"> Agregar Sección </a>
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($parallax != null)
            {!! Form::parallax($parallax)!!}
            @endif
        </div>
    </div>
</div>
@endsection

<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'parallax.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="titulo" type="text" placeholder="Titulo" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="descripcion" type="text" placeholder="Descripción" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Color del Texto</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span>
                        <input name="textcolor" type="color" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>¿Color o Imágen de Fondo?</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span>
                        <select class="form-control" id="color" name="modo" required onchange="cambiarColor()">
                            <option value="fondo">-- Selecciona una opción --</option>
                            <option value="COLOR">COLOR DE FONDO</option>
                            <option value="IMAGEN">IMÁGEN DE FONDO</option>
                        </select>
                    </div>
                    <div class="form-group" id="fondo">

                    </div>
                    <div class="form-group">
                        <label>Contenido HTML</label>
                        <span data-toggle="tooltip" title="Establece el contenido de la sección."> <i class="fa fa-question-circle"></i></span>
                        <textarea name="content_html" required class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="exampleModal" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
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
                <h5 class="modal-title">Editar Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($parallax != null)
                    {!! Form::model($parallax,['route'=>['parallax.updated',$parallax],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="parallax" value="{{$parallax->id}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="titulo" value="{{$parallax->titulo}}" type="text" placeholder="Titulo" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="descripcion" value="{{$parallax->descripcion}}" type="text" placeholder="Descripción" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Color del Texto</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span>
                        <input name="textcolor" type="color" value="{{$parallax->textcolor}}" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>¿Color o Imágen de Fondo? 
                            <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span>
                            (Actual: {{$parallax->modo." - ".$parallax->fondo}})</label>                        
                        <select class="form-control" id="color2" name="modo" required onchange="cambiarColor2()">
                            <option value="fondo">-- Selecciona una opción --</option>
                            <option value="COLOR">COLOR DE FONDO</option>
                            <option value="IMAGEN">IMÁGEN DE FONDO</option>
                        </select>
                    </div>
                    <div class="form-group" id="fondo2">

                    </div>
                    <div class="form-group">
                        <label>Contenido HTML</label>
                        <span data-toggle="tooltip" title="Establece el contenido de la sección."> <i class="fa fa-question-circle"></i></span>
                        <textarea name="content_html" required class="form-control">{{$parallax->content_html}}</textarea>
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modaledit" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
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
$('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    function cerrar(id) {
        $("#" + id).modal('hide');
        $("#" + id).removeClass('modal-open');
        $('.' + id).remove();
    }

    function cambiarColor() {
        var c = $("#color").val();
        switch (c) {
            case 'COLOR':
                $("#fondo").html("<label>Color de Fondo<span data-toggle='tooltip' title='Establece el color de fondo de la sección.'> <i class='fa fa-question-circle'></i></span></label><input name='fondo' type='color' required class='form-control'>");
                break;
            case 'IMAGEN':
                $("#fondo").html("<label>Imágen de Fondo<span data-toggle='tooltip' title='Establece la imagen de fondo de la sección.''> <i class='fa fa-question-circle'></i></span></label><input name='fondo' type='file' required class='form-control'>");
                break;
            case 'fondo':
                $("#fondo").html("");
                break;
        }
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    }

    function cambiarColor2() {
        var c = $("#color2").val();
        switch (c) {
            case 'COLOR':
                $("#fondo2").html("<label>Color de Fondo<span data-toggle='tooltip' title='Establece el color de fondo de la sección.'> <i class='fa fa-question-circle'></i></span></label><input name='fondo' type='color' required class='form-control'>");
                break;
            case 'IMAGEN':
                $("#fondo2").html("<label>Imágen de Fondo<span data-toggle='tooltip' title='Establece la imagen de fondo de la sección.'> <i class='fa fa-question-circle'></i></span></label><input name='fondo' type='file' required class='form-control'>");
                break;
            case 'fondo':
                $("#fondo2").html("");
                break;
        }
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    }
    
</script>
@endsection