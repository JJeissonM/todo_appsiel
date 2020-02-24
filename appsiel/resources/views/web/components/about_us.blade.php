@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            overflow: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            overflow-x:hidden;
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
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                @if($aboutus != null)
                    <div class="contenido">
                        <img src="{{url($aboutus->imagen)}}" alt="" class="imagen">
                        <div class="descripcion">
                            <h5 class="titulo">{{$aboutus->titulo}}</h5>
                            <p>{{str_limit($aboutus->descripcion,30)}}</p>
                        </div>
                    </div>
                    <div class="add d-flex justify-content-end">
                        <a href="{{url('aboutus/create').'/'.$widget.$variables_url}}"> Editar</a>
                    </div>
                @else
                    <div class="add d-flex justify-content-end">
                        <a href="{{url('aboutus/create').'/'.$widget.$variables_url}}"> Agregar</a>
                    </div>
                @endif
            </div>
            <div class="widgets" id="widgets">
                @if($aboutus != null)
                    {!! Form::aboutus($aboutus)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
