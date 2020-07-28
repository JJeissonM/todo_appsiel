@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
    <style>

        #img_producto {
             object-fit: cover;
             height: 350px;
        }

        .add_carrito {
            background-color:rgb(253, 153, 67);
            border-radius: 4px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        .cantidades {
            display: flex;
            justify-content: space-between;
        }

        .label {
            background-color: #0b97c4;
            color: white;
            font-weight: bold;
            padding: 5px;
            border-radius: 5px;
        }


    </style>
@endsection

@section('content')

    @include('web.tienda.header')

    <main>
        <div class="main-container col2-left-layout">
            <div class="container">
                <div class="container-inner">
                    <div class="main">
                        <div class="main-inner">
                            <div class="row">
                                <div class="col-left sidebar col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                    <img id="img_producto" src="https://images.alphacoders.com/241/241133.jpg"  alt="">
                                </div>
                                <div class="col-main col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                    <div class="page-title category-title">
                                        <h1>{{$inv_producto->descripcion}}</h1>
                                    </div>
                                    <div style="display: flex; justify-content: space-between">
                                         <div class="cantidades">
                                             <span class="label label-success"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                             <input type="number" style="width: 40px;">
                                             <span class="label label-danger"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                         </div>
                                        <button class="btn add_carrito" style="">Agregar al Carrito</button>
                                    </div>
                                </div>
                                <div class="col-main col-lg-12 col-md-12 col-sm-12 col-xs-12"  style="margin-top: 20px">
                                    <h2 class="title" style=" padding-bottom: 10px; font-size: 24px; border-bottom: 1px solid grey">Especificacíones</h2>
                                    <div style="width: 80%; display: flex; justify-content: space-between">
                                        @if($inv_producto->fichas->count() > 0)
                                            @foreach($inv_producto->fichas as $ficha)
                                                <div class="caracteristica" style="width: 50%">
                                                    <p style="margin-bottom: 5px;"><strong>{{$ficha->key}}</strong></p>
                                                    <span>{{$ficha->descripcion}}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <p>SIN ESPECIFICACIÓN </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    {!! Form::footer($footer,$redes,null,'small')  !!}
@endsection

@section('script')
    <script src="{{asset('js/carrito/app.js')}}"></script>
    <script type="text/javascript">
    </script>
@endsection
