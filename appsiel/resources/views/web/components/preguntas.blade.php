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
            <h4>.:: En ésta Sección: Preguntas Frecuentes ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Preguntas Frecuentes</h4>
            @if($pregunta == null)
            <div class="add d-flex justify-content-end col-md-12">
                <a data-toggle="modal" data-target="#crearseccion" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar Sección</a>
            </div>
            @else
            <div class="descripcion" style="text-align: center; margin-top: 20px;">
                <h5 class="titulo">{{$pregunta->titulo}}</h5>
                <p>{{str_limit($pregunta->descripcion,30)}}</p>
                <a href="{{url('preguntas/destroy').'/'.$pregunta->id.$variables_url}}" class="btn btn-lg" title="Eliminar Seccion"><i class="fa fa-window-close"></i></a>
            </div>
            <div class="col-md-12 add d-flex">
                <div class="col-md-6">
                    <a data-toggle="modal" data-target="#Crearpregunta" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar pregunta</a>
                </div>
                <div class="col-md-6 justify-content-end">
                    <a data-toggle="modal" data-target="#edit2" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Editar Sección </a>
                </div>
            </div>
            <div class="col-md-12">
                @if(count($pregunta->itempreguntas)>0)
                @foreach($pregunta->itempreguntas as $item)
                <div class="contenido">
                    <div class="descripcion">
                        <h5 class="titulo">{{$item->pregunta}}</h5>
                        <p>{{str_limit($item->respuesta,30)}}</p>
                    </div>
                    <a id="{{$item}}" onclick="editar(this.id)" data-toggle="modal" data-target="#Modaledit" class="btn" title="Editar Pregunta" style="color: #45aed6"><i class="fa fa-edit"></i></a>
                    <a href="{{url('preguntas/eliminar/itempregunta').'/'.$item->id.$variables_url}}" class="btn" title="Eliminar Pregunta"><i class="fa fa-eraser"></i></a>
                </div>
                @endforeach
                @endif
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets" style="position: relative;">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($pregunta != null)
            {!! Form::preguntas($pregunta)!!}
            @endif
        </div>
    </div>
</div>
@endsection

<div class="modal" id="crearseccion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'preguntas.guardar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Nombre de la pregunta" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required="required"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Color 1 Para el Degradado</label>
                        <input type="color" name="color1" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Color 2 Para el Degradado</label>
                        <input type="color" name="color2" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Imagen Para el Fondo Derecho</label>
                        <input type="file" name="imagen_fondo" class="form-control" rows="3">
                    </div>
                    <div class="form-group">
                        <label>¿El fondo es Imagen o Color?</label>
                        <select type="select" class="form-control" id="tipo_fondo" required name="tipo_fondo" onchange="cambiar()">
                            <option value="">-- Seleccione una opción --</option>
                            <option value="IMAGEN">IMAGEN</option>
                            <option value="COLOR">COLOR</option>
                        </select>
                    </div>
                    <div class="form-group" id="fondo_container">
                    </div>
                    <div class="form-group">
                        <br /><br />
                        <a class="btn btn-danger" id="crearseccion" style="color: white" onclick="cerrar(this.id)">
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

<div class="modal" id="edit2" tabindex="-1" role="dialog">
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
                    @if($pregunta != null)
                    {!! Form::model($pregunta,['route'=>['preguntas.updated',$pregunta],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo" value="{{$pregunta->titulo}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input name="descripcion" type="text" placeholder="Titulo" value="{{$pregunta->descripcion}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,$pregunta->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Color 1 Para el Degradado</label>
                        <input type="color" name="color1" value="{{$pregunta->color1}}" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Color 2 Para el Degradado</label>
                        <input type="color" name="color2" value="{{$pregunta->color2}}" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Imagen Para el Fondo Derecho</label>
                        <a target="_blank" href="{{asset($pregunta->imagen_fondo)}}">Ver Actual</a><br>
                        <input type="file" name="imagen_fondo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>¿El fondo es Imagen o Color?</label>
                        <select type="select" class="form-control" id="tipo_fondo2" name="tipo_fondo" onchange="cambiar2()">
                            @if($pregunta->tipo_fondo=='IMAGEN')
                            <option value="">-- Seleccione una opción --</option>
                            <option selected value="IMAGEN">IMAGEN</option>
                            <option value="COLOR">COLOR</option>
                            @else
                            <option value="">-- Seleccione una opción --</option>
                            <option value="IMAGEN">IMAGEN</option>
                            <option selected value="COLOR">COLOR</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group" id="fondo_container2">
                        @if($pregunta->tipo_fondo=='IMAGEN')
                        <label>Imagen de Fondo</label>
                        <a target="_blank" href="{{asset($pregunta->fondo)}}">Ver Actual</a><br>
                        <b>Repetir: {{$pregunta->repetir}}</b><br>
                        <b>Orientación Imagen: {{$pregunta->direccion}}</b>
                        @else
                        <label>Color de Fondo</label>
                        <div class="col-md-12" style="background-color: {{$pregunta->fondo}}; width: 100%; height: 20px;"></div>
                        @endif
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="edit2" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
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

<div class="modal" id="Crearpregunta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Pregunta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($pregunta != null)
                    {!! Form::open(['route'=>'preguntas.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="pregunta_id" value="{{$pregunta->id}}">
                    <div class="form-group">
                        <label>Pregunta</label>
                        <input name="pregunta" type="text" placeholder="Nombre de la pregunta" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Respuesta</label>
                        <textarea name="respuesta" class="form-control" rows="3" required="required"></textarea>
                    </div>
                    <div class="form-group">
                        <br /><br />
                        <a class="btn btn-danger" id="Crearpregunta" style="color: white" onclick="cerrar(this.id)">
                            Cancelar
                        </a>
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

<div class="modal" id="Modaledit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Pregunta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($pregunta != null)
                    {!! Form::open(['route'=>'preguntas.modificar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="itempregunta_id" id="itempregunta_id">
                    <div class="form-group">
                        <label>Nombre del Pregunta</label>
                        <input name="pregunta" id="pregunta" type="text" placeholder="Nombre de la Pregunta" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Respuesta</label>
                        <textarea name="respuesta" id="respuesta" class="form-control" rows="3" required="required"></textarea>
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modaledit" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
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
        $("#pregunta").attr('value', item.pregunta);
        $("#respuesta").val(item.respuesta);
        $("#respuesta").attr('value', item.respuesta);
        $("#itempregunta_id").attr('value', item.id);
    }

    function cambiar() {
        $("#fondo_container").html("");
        var f = $("#tipo_fondo").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + "Imagen de Fondo</label><input type='file' class='form-control' name='fondo' required>" +
                "<label>Repetir</label><select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>" +
                "<label>Orientación Imagen</label><select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option></select>";
        } else if (f == 'COLOR') {
            html = html + "Color de Fondo</label><input type='color' class='form-control' name='fondo' required>";
        } else {
            html = "";
        }
        $("#fondo_container").html(html);
    }

    function cambiar2() {
        $("#fondo_container2").html("");
        var f = $("#tipo_fondo2").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + "Imagen de Fondo</label><input type='file' class='form-control' name='fondo' required>" +
                "<label>Repetir</label><select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>" +
                "<label>Orientación Imagen</label><select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option></select>";
        } else if (f == 'COLOR') {
            html = html + "Color de Fondo</label><input type='color' class='form-control' name='fondo' required>";
        } else {
            html = "";
        }
        $("#fondo_container2").html(html);
    }
</script>
@endsection