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
                <h4>.:: En ésta Sección: About Us ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú About Us</h4>
                <div class="col-md-12">
                    @if($aboutus != null)
                        <div class="contenido">
                            @if($aboutus->disposicion == 'DEFAULT')
                            <img src="{{url($aboutus->imagen)}}" alt="" class="imagen">
                            @endif
                            <div class="descripcion">
                                <h5 class="titulo">{{$aboutus->titulo}}</h5>
                                <p>{{str_limit($aboutus->descripcion,30)}}</p>
                            </div>
                        </div>
                        <div class="add d-flex justify-content-end">
                            <a href="{{url('aboutus/create').'/'.$widget.$variables_url}}"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Editar</a>
                        </div>
                    @else
                        <div class="add d-flex justify-content-end">
                            <a href="{{url('aboutus/create').'/'.$widget.$variables_url}}"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Agregar</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($aboutus != null)
                    @if($aboutus->disposicion == 'DEFAULT')
                        {!! Form::aboutus($aboutus)!!}
                    @else
                        {!! Form::aboutuspremiun($aboutus) !!}
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
