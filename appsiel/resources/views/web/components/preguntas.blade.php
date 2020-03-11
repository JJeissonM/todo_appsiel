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
                <h4>.:: En ésta Sección: Preguntas Frecuentes ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Preguntas Frecuentes</h4>
                @if(count($preguntas) <= 0)
                    <div class="add d-flex justify-content-end col-md-12">
                        <a data-toggle="modal" data-target="#exampleModal"
                           class="btn btn-primary waves-effect btn-block btn-sm"
                           style="color: white; font-weight: bold;"> Agregar pregunta</a>
                    </div>
                @else
                    <div class="add d-flex justify-content-end col-md-12">
                        <a data-toggle="modal" data-target="#exampleModal"
                           class="btn btn-primary waves-effect btn-block btn-sm"
                           style="color: white; font-weight: bold;"> Agregar pregunta</a>
                    </div>
                    <div class="col-md-12">
                        @foreach($preguntas as $item)
                            <div class="contenido">
                                <div class="descripcion">
                                    <h5 class="titulo">{{$item->pregunta}}</h5>
                                    <p>{{str_limit($item->respuesta,30)}}</p>
                                </div>
                                <a id="{{$item}}" onclick="editar(this.id)" data-toggle="modal" data-target="#Modaledit"
                                   class="btn"
                                   title="Editar Pregunta" style="color: #45aed6"><i
                                            class="fa fa-edit"></i></a>
                                <a href="{{url('preguntas/destroy').'/'.$item->id.$variables_url}}" class="btn"
                                   title="Eliminar Pregunta"><i class="fa fa-eraser"></i></a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="widgets" id="widgets" style="position: relative;">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if(count($preguntas)>0)
                    {!! Form::preguntas($preguntas)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
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
                    {!! Form::open(['route'=>'preguntas.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Pregunta</label>
                        <input name="pregunta" type="text" placeholder="Nombre de la pregunta"
                               class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Respuesta</label>
                        <textarea name="respuesta" class="form-control" rows="3" required="required"></textarea>
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
                    @if(count($preguntas)>0)
                        {!! Form::open(['route'=>'preguntas.modificar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <input type="hidden" name="pregunta_id" id="pregunta_id">
                        <div class="form-group">
                            <label>Nombre del Pregunta</label>
                            <input name="pregunta" id="pregunta" type="text"
                                   placeholder="Nombre de la Pregunta"
                                   class="form-control" required="required">
                        </div>
                        <div class="form-group">
                            <label>Respuesta</label>
                            <textarea name="respuesta" id="respuesta" class="form-control" rows="3" required="required"></textarea>
                        </div>
                        <div class="form-group">
                            <br/><br/><a class="btn btn-danger" id="Modaledit" style="color: white"
                                         onclick="cerrar(this.id)">Cancelar</a>
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
            $("#pregunta_id").attr('value', item.id);
        }
    </script>
@endsection
