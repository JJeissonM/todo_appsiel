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
            <h4>.:: En ésta Sección: Modal ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Modal</h4>
            <div class="col-md-12">
                @if($modal != null)
                <div class="col-md-12">
                    {!! Form::model($modal, ['route' => ['modal.update', $modal->id],'method'=>'PUT','files' => 'true']) !!}
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label for="title">Titulo</label>
                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input type="text" id="title" class="form-control" name="title" value="{{$modal->title}}">
                    </div>
                    <div class="form-group">
                        <label for="">Descripción</label>
                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                        <textarea name="body" class="form-control" cols="30" rows="5">{{$modal->body}}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="body">Enlace</label>
                        <span data-toggle="tooltip" title="Establece el enlace del boton inferior."> <i class="fa fa-question-circle"></i></span>
                        <input type="text" id="body" class="form-control" name="enlace" value="{{$modal->enlace}}">
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,$modal->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="recurso">Imagen de Fondo (800x550 px)</label>
                        <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input type="file" id="recurso" class="form-control" name="path" value="">
                    </div>
                    <button class="btn btn-primary waves-effect btn-block btn-sm">Guardar</button>
                    {!! Form::close() !!}
                </div>
                @else
                <div class="col-md-12">
                    <form action="{{route('modal.store')}}" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <div class="form-group">
                            <label for="title">Titulo</label>
                            <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                            <input type="text" id="title" class="form-control" name="title">
                        </div>
                        <div class="form-group">
                            <label for="">Descripción</label>
                            <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                            <textarea name="body" class="form-control" cols="30" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="body">Enlace</label>
                            <span data-toggle="tooltip" title="Establece el enlace del boton inferior."> <i class="fa fa-question-circle"></i></span>
                            <input type="text" id="body" class="form-control" name="enlace">
                        </div>
                        <div class="form-group">
                            <label for="">Fuente Para el Componente</label>
                            <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                            @if($fonts!=null)
                            {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                            @endif
                        </div>                        
                        <div class="form-group">
                            <label for="recurso">Imagen de Fondo (800x550 px)</label>
                            <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span>
                            <input type="file" id="recurso" class="form-control" name="path">
                        </div>
                        <button class="btn btn-primary waves-effect btn-block btn-sm">Guardar</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($modal != null)
            <div class="d-flex items-center justify-content-center">
                <div class="card" style="width: 80%;">
                    <img class="card-img-top" src="{{asset($modal->path != ''?$modal->path : 'assets/img/learning_background.jpg')}}" alt="Card image cap" style="height: 250px">
                    <div class="card-body" style="margin: 10px;">
                        <div style="margin-top: 10px; font-weight: bold;">{{$modal->title}}</div>
                        <p class="card-text">{{$modal->body}}</p>
                        <a href="{{$modal->enlace}}" class="btn btn-info">Call to Action</a>
                    </div>
                </div>
            </div>
            @else
            <div class="d-flex items-center justify-content-center">
                <div class="card" style="width: 80%;">
                    <img class="card-img-top" src="{{asset('assets/img/learning_background.jpg')}}" alt="Card image cap" style="height: 250px">
                    <div class="card-body" style="margin: 10px;">
                        <div style="margin-top: 10px; font-weight: bold;">Lorem ipsum dolor sit amet.</div>
                        <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus cum dolores enim facere ipsam nesciunt nihil nisi nobis quisquam sapiente.</p>
                        <a href="" class="btn btn-info">Call to Action</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript">    
    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'right',
        html: true
    });
</script>
@endsection