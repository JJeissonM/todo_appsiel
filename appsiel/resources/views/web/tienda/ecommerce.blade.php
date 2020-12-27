@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
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
                                <div class="col-left sidebar col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                    <div class="block block-layered-nav">
                                        <div class="block-title">
                                            <strong><span>Filtrar Por</span></strong>
                                        </div>
                                        <div class="block-content">
                                            <p class="block-subtitle">Shopping Options</p>
                                            <dl id="narrow-by-list">
                                                <dt class="odd" style="margin:20px 0;">Categorias</dt>
                                                <dd class="odd">
                                                    <ol>
                                                        @foreach($grupos as $key => $value)
                                                            <li>
                                                                <a class="ajaxLayer"
                                                                   href="{{route('tienda.filtrocategoria',$value[0]->id)}}">{{$key}}</a>
                                                                ({{$value->count()}})
                                                            </li>
                                                        @endforeach
                                                    </ol>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-main col-lg-9 col-md-9 col-sm-12 col-xs-12">
                                    <div class="page-title category-title">
                                        <h1>Nuestros Productos</h1>
                                    </div>
                                    <div class="category-products">
                                        <ul class="products-grid row first odd">
                                            @foreach($items as $item)
                                                <li class="col-sm-4 col-md-4 col-sms-12 col-smb-12 item first">
                                                    <div class="item-inner">
                                                        <div class="ma-box-content">
                                                            <div class="products clearfix">
                                                                <a href=""
                                                                   title="Fusce aliquam" class="product-image">
                                                                <span class="product-image">
                                                                    <img src="{{ asset( config('configuracion.url_instancia_cliente') . 'storage/app/inventarios/' . $item->imagen ) }} " loading="lazy"
                                                                         width="300" height="350" alt="{{$item->descripcion}}">
                                                                </span>
                                                                </a>
                                                            </div>
                                                            <h2 class="product-name"><a
                                                                        href="{{route('tienda.detalleproducto',$item->id)}}"
                                                                        title="Fusce aliquam">{{$item->descripcion}}</a></h2>
                                                            <div class="ratings">
                                                                <div class="rating-box">
                                                                    <div class="rating" style="width:67%"></div>
                                                                </div>
                                                            </div>
                                                            <div class="price-box">
                                                            <span class="regular-price" id="product-price-1">
                                                                <span class="price">${{$item->precio_venta}}</span></span>
                                                            </div>
                                                            <div class="actions">
                                                                <button type="button" class="button btn-cart agregar-carrito"                                                                        data-original-title="Add to Cart" rel="tooltip"><i
                                                                            class="fa fa-shopping-cart"></i><span>Comprar</span></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="col-md-12">
                                            {{$items->render()}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('web.tienda.footer')
@endsection

@section('script')
    <script src="{{asset('js/carrito/app.js')}}"></script>
    <script type="text/javascript">
    </script>
@endsection
