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
            color:black;
            font-size: 16px;
        }

        .add {
            margin-top : 20px;
        }

        .add a {
            color:#1c85c4;
        }

    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap" >
            <div id="wrapper">
                @if($slider != null)
                    @foreach($slider->items as $item)
                        <div class="contenido">
                            <img src="{{asset($item->imagen)}}" alt="" class="imagen">
                            <div class="descripcion">
                                <h5 class="titulo">{{$item->titulo}}</h5>
                                <p>{{str_limit($item->descripcion,20)}}</p>
                            </div>
                            <a href="" class="btn"><i class="fa fa-edit"></i></a>
                            <a href="" class="btn"><i class="fa fa-eraser"></i></a>
                        </div>
                    @endforeach
                @endif

                <div class="add d-flex justify-content-end">
                    <a href="{{url('slider').'/'.$widget.$variables_url}}"> Agregar Item</a>
                </div>
            </div>
            <div class="widgets" id="widgets">
                {!! Form::slider($slider) !!}
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
