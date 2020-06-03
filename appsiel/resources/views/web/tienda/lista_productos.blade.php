@if( empty( $items->toArray()['data'] ) )

    <div class="alert alert-warning">
        <strong>Lo sentimos, no se encontraron resultados para "{{$texto}}"</strong> 
        <br>
        Intente utilizar otras palabras para la busqueda.
    </div>
        
@else
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
                                    href="#"
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
                            <button type="button" class="button btn-cart"
                                    data-original-title="Add to Cart" rel="tooltip"><i
                                        class="fa fa-shopping-cart"></i><span>Comprar</span></button>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
<!-- 
<div class="col-md-12">
    { { $items->render() }}
</div>
-->