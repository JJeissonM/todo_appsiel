<link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/products.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/toastr.min.css')}}">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:ital,wght@0,300;0,700;1,300&display=swap');
    .font-oswald{
        font-family: 'Open Sans Condensed', sans-serif;
    }
    #categoria_filtrada > span > img{
        display: none;        
    }
    a.close {
        font-size: 2.2rem;
        float: none;
    }
    .font-tienda{
        @if($pedido != null)
                @if( !is_null($pedido->configuracionfuente ) )
                    font-family: {{ $pedido->configuracionfuente->fuente->font }};
                @endif            
        @else
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
        @endif        
    }
    
</style>

<!--@ include('web.tienda.carousel')-->
@include('web.tienda.search')

<main style="background: white;" class="font-tienda">    
    <div class="main-container col2-left-layout">
        <div class="container">
            <div class="container-inner">
                <div class="main">
                    <div class="main-inner">
                        <div class="">
                            <div class="col-main col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="page-title category-title">
                                    <h1 class="font-tienda text-center">Nuestros Productos</h1>
                                    <div id="categoria_filtrada" style="margin-bottom: 2rem; color: #64686d"></div>
                                </div>
                                <div class="category-products">
                                    <div id="lista_productos">
                                        <ul class="products-grid row first odd align-content-start">                                            
                                            @foreach($items as $item)
                                                <li class=" item first" style="list-style: none; padding: 0 0 0 0; position: relative;">
                                                    @if( $item->descuento != 0)
                                                    <div style="position: absolute; z-index: 20; overflow: visible; top: -20px; right: -10px;">
                                                        <i class="fa fa-certificate" aria-hidden="true" style="position:relative;color: red; font-size: 80px;"></i>
                                                    </div>
                                                    <div style="font-size:24px; position: absolute;z-index:21; top: 0px; right: 2px;transform: rotate(-20deg); color: white">-{{ $item->descuento }}%</div> 
                                                    @endif
                                                    <div class="item-inner">
                                                        <div class="ma-box-content" data-id="{{$item->id}}">
                                                            <input id="tasa_impuesto" type="hidden" value="{{$item->tasa_impuesto}}">
                                                            <input id="precio_venta" type="hidden" value="{{$item->precio_venta}}">
                                                            <div class="products clearfix">
                                                                <a href="{{route('tienda.detalleproducto',$item->id)}}"
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
                                                                             width="200" height="200" alt="{{$item->descripcion}}" onerror="imgError(this)" style="object-fit: contain;">
                                                                            
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <h2 class="font-oswald product-name text-center mx-2" onclick="window.location.href='{{route('tienda.detalleproducto',$item->id)}}'">
                                                                <a href="{{route('tienda.detalleproducto',$item->id)}}" title="{{$item->descripcion}}">{{$item->descripcion}}</a>
                                                            </h2>
                                                            <div class="price-box text-center">
                                                                @if( $item->descuento == 0)
                                                                    <span class="regular-price" id="product-price-1">
                                                                        <span class="price">${{ number_format( $item->precio_venta,0,',','.' ) }} x {{ $item->unidad_medida1 }}</span>
                                                                    </span>
                                                                @else
                                                                    <span class="regular-price" id="product-price-1">
                                                                        <span class="price"> <del>${{ number_format( $item->precio_venta,0,',','.' ) }} x {{ $item->unidad_medida1 }}</del></span>
                                                                       
                                                                        <br>
                                                                        <span class="price">${{ number_format( $item->precio_venta - $item->valor_descuento,0,',','.' ) }} x {{ $item->unidad_medida1 }}</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="actions agregar-carrito">
                                                                <button type="button" class="btn-cart btn-primary form-control" style="background-color: var(--color-primario); border: none ;font-size: 16px" >  Añadir &nbsp;<i class="fa fa-cart-plus" ></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
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

<script src="{{asset('assets/tienda/js/app.js')}}"></script>

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

                    $('#categoria_filtrada').html( 'Filtrado por: <span style="border-radius: 4px; border: 2px solid var(--color-secundario); padding: 3px;"> ' + enlace.innerHTML + ' <a href="javascript:location.reload()" class="close fa fa-close" aria-label="close"></a></span>' ); 

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

            console.log( event );

            $.get( url, datos )
                .done(function( data ) {

                    $('#lista_productos').html( data );
                    $('#lista_productos').fadeIn( 1000 );

                    $('#categoria_filtrada').html( 'Filtado por: <span style="border-radius: 4px; border: 2px solid var(--color-secundario); padding: 3px;"> ' + datos.substr(7) + ' <a href="javascript:location.reload()" class="close fa fa-close" aria-label="close"></a></span>' ); 

                })
                .error(function(){
                    $('#lista_productos').fadeIn( 500 );

                    $('#lista_productos').html( '<p style="color:red;">Categoría no pudo ser cargada. Por favor, intente nuevamente.</p>' );
                });
    }
</script>