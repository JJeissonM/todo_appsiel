<link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/toastr.min.css')}}">


<!--@ include('web.tienda.carousel')-->
@include('web.tienda.search')

<main style="background: white;">
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
                                        <p class="block-subtitle">Opciones de compra</p>
                                        <dl id="narrow-by-list">
                                            <dt class="odd" style="margin:20px 0;">Categorias</dt>
                                            <div id="categoria_filtrada"></div>
                                            <dd class="odd">
                                                <ol>
                                                    @foreach($grupos as $key => $value)
                                                        <li>
                                                            <a class="ajaxLayer"
                                                               onclick="filtrar_categoria('{{ $value[0]->id }}', this)" > {{$key}} ({{$value->count()}})</a>
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
                                    <div id="lista_productos">
                                        <ul class="products-grid row first odd">
                                            @foreach($items as $item)
                                                <li class="col-sm-4 col-md-4 col-sms-12 col-smb-12 item first">
                                                    <div class="item-inner">
                                                        <div class="ma-box-content" data-id="{{$item->id}}">
                                                            <input id="tasa_impuesto" type="hidden" value="{{$item->tasa_impuesto}}">
                                                            <input id="precio_venta" type="hidden" value="{{$item->precio_venta}}">
                                                            <div class="products clearfix">
                                                                <a href="#"
                                                                   title="{{$item->descripcion}}" class="product-image">
                                                                    <div class="product-image"> 
                                                                        <?php 
                                                                            $url_imagen_producto = '#';
                                                                            if ( $item->imagen != '' )
                                                                            {
                                                                                $url_imagen_producto = asset( config('configuracion.url_instancia_cliente') . 'storage/app/inventarios/' . $item->imagen );
                                                                            }
                                                                        ?>
                                                                        <img src="{{ $url_imagen_producto }}" loading="lazy"
                                                                             width="350" height="300" alt="{{$item->descripcion}}" onerror="imgError(this)">
                                                                        </div>
                                                                </a>
                                                            </div>
                                                            <h2 class="product-name" onclick="window.location.href='{{route('tienda.detalleproducto',$item->id)}}'">
                                                                <a href="{{route('tienda.detalleproducto',$item->id)}}" title="{{$item->descripcion}}">{{$item->descripcion}}</a>
                                                            </h2>
                                                            <div class="ratings">
                                                                <div class="rating-box">
                                                                    <div class="rating" style="width:67%"></div>
                                                                </div>
                                                            </div>
                                                            <div class="price-box">
                                                                <span class="regular-price" id="product-price-1">
                                                                    <span class="price">${{ number_format( $item->precio_venta,0,',','.' ) }} x {{ $item->unidad_medida1 }}</span></span>
                                                            </div>
                                                            <div class="actions agregar-carrito">
                                                                <button type="button" class="btn-cart">
                                                                    <i class="fa fa-shopping-cart"></i>
                                                                    Comprar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
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
</main>

<script src="{{asset('js/carrito/app.js')}}"></script>

<script type="text/javascript">

        function imgError(image) {
            image.onerror = "";
            image.src = "{{asset('assets/img/noimage.jpg')}}";
            return true;
        }

        function filtrar_categoria( categoria_id, enlace )
        {   
            $('#lista_productos').fadeOut( 1000 );
            
            var url = "{{ url('ecommerce/public/filtro/categoria/') }}" + "/" + categoria_id;

            //console.log( enlace );

            $.get( url )
                .done(function( data ) {

                    $('#lista_productos').html( data );
                    $('#lista_productos').fadeIn( 1000 );

                    $('#categoria_filtrada').html( '<div style="border: 1px solid; border-radius: 4px; padding: 3px;"> Filtado por: <a href="javascript:location.reload()" class="close" aria-label="close">&times;</a> <br>' + enlace.innerHTML + ' </div><hr>' );

                })
                .error(function(){
                    $('#lista_productos').fadeIn( 500 );

                    $('#lista_productos').html( '<p style="color:red;">Categoría no pudo ser cargada. Por favor, intente nuevamente.</p>' );
                });
        }

        function buscar_descripcion( event )
        {
            event.preventDefault();

            $('#lista_productos').fadeOut( 1000 );

            //var url = "{{ url('ecommerce/public/busqueda/') }}";
            var form_consulta = $('#form_consulta');
            var url = form_consulta.attr('action');
            var datos = form_consulta.serialize();

            //console.log( enlace );

            $.get( url, datos )
                .done(function( data ) {

                    $('#lista_productos').html( data );
                    $('#lista_productos').fadeIn( 1000 );

                    $('#categoria_filtrada').html( '<div style="border: 1px solid; border-radius: 4px; padding: 3px;"> Filtado por: <a href="javascript:location.reload()" class="close" aria-label="close">&times;</a> <br>' + enlace.innerHTML + ' </div><hr>' );

                })
                .error(function(){
                    $('#lista_productos').fadeIn( 500 );

                    $('#lista_productos').html( '<p style="color:red;">Categoría no pudo ser cargada. Por favor, intente nuevamente.</p>' );
                });
    }
</script>

