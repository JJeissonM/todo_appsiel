@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            overflow-y: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            width: 30%;
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


    </style>

@endsection

@section('content')
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                {!! Form::model($album,['route'=>['galeria.updated',$album->id],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <div class="form-group">
                    <label>Titulo</label>
                    <input name="titulo" type="text" placeholder="Titulo del Álbum" required="required"
                           value="{{$album->titulo}}" class="form-control">
                </div>
                <div class="form-group">
                    <label>Descripción del Álbum</label>
                    {!! Form::textarea('descripcion',$album->descripcion,['class'=>'form-control col-md-12 col-xs-12','required']) !!}
                </div>
                <div class="form-group">
                    <label>Añadir Imagenes</label>
                    <input name="imagen[]" multiple type="file" placeholder="Agregar una imagen" class="form-control">
                </div>
                <div class="form-group">
                    <br/><br/><a href="{{url('seccion/'.$widget).$variables_url}}"
                                 class="btn btn-danger">Cancelar</a>
                    <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                    {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            <div class="widgets" id="widgets">
                <div class="col-md-12 d-flex flex-wrap">
                    @foreach($album->fotos as $imagen)
                        <div class="col-md-3">
                            <img onclick="mostrar('{{url($imagen->nombre)}}')" data-toggle="modal"
                                 data-target="#exampleModal" src="{{url($imagen->nombre)}}"
                                 alt="{{$imagen->nombre}}" style="width: 100%; height: 150px; object-fit: cover;">
                            <a href="{{ route('galeria.deleteimagen',$imagen->id).$variables_url}}" class="btn btn-danger btn-block"
                               data-toggle="tooltip" data-placement="top" title="Eliminar Imagen"><i
                                        class="fa fa-remove"></i> Eliminar Imagen</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista previa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img id="imagen" style="width: 560px; height: 560px; object-fit: cover;">
            </div>
        </div>
    </div>
</div>
@section('script')
    <script type="text/javascript">
        function mostrar(url) {
            $('#imagen').removeAttr('src');
            $('#imagen').attr('src', url);
        }
    </script>
@endsection
