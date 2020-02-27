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

        .widgets img {
            width: 100%;
            object-fit: cover;
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
                <h4>.:: En ésta Sección: Galeria ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Galeria</h4>
                @if($galeria != null)
                    <div class="col-md-12">
                        @foreach($galeria->albums as $album)
                            <div class="contenido">
                                <img src="{{url($album->fotos->first()->nombre)}}" alt="" class="imagen">
                                <div class="descripcion">
                                    <h5 class="titulo">{{$album->titulo}}</h5>
                                    <p>{{str_limit($album->descripcion,30)}}</p>
                                </div>
                                <a href="{{url('galeria/edit').'/'.$album->id.$variables_url}}" class="btn"
                                   title="Editar Álbum"><i class="fa fa-edit"></i></a>
                                <a href="{{url('galeria/destroy/album').'/'.$album->id.$variables_url}}" class="btn"
                                   title="Eliminar Álbum"><i class="fa fa-eraser"></i></a>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="col-md-12 add d-flex justify-content-end">
                    <a href="{{url('galeria/create').'/'.$widget.$variables_url}}" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar Nuevo Album</a>
                </div>
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($galeria != null)
                    {!! Form::galeria($galeria)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
