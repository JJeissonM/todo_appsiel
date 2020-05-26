<?php 
    $empresa = App\Core\Empresa::find(1);
    $configuracion = App\web\Configuraciones::all()->first();
?>
<header style="background: {{ $configuracion->color_primario }};">
    <div class="top-link" style="background: {{ $configuracion->color_primario }};">
        <div class="container">
            <div class="top-link-inner">
                <div class="row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <div class="toplink-static">
                            Línea directa: &nbsp; <a href="https://api.whatsapp.com/send?phone=+57{{ $empresa->telefono1 }}"><img class="alignnone wp-image-2180 lazyloaded" src="https://sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon-150x150.png" data-src="https://sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon-150x150.png" alt="" width="47" height="37" data-srcset="https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?zoom=2&amp;resize=97%2C107&amp;ssl=1 194w, https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?zoom=3&amp;resize=97%2C107&amp;ssl=1 291w" sizes="(max-width: 97px) 100vw, 97px" srcset="https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?zoom=2&amp;resize=97%2C107&amp;ssl=1 194w, https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?zoom=3&amp;resize=97%2C107&amp;ssl=1 291w"><noscript><img class="alignnone wp-image-2180 lazyload" src="https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?resize=97%2C107&#038;ssl=1" alt="" width="97" height="107" srcset="https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?zoom=2&amp;resize=97%2C107&amp;ssl=1 194w, https://i2.wp.com/sherpadigital.es/wp-content/uploads/2017/10/whatsapp-icon.png?zoom=3&amp;resize=97%2C107&amp;ssl=1 291w" sizes="(max-width: 97px) 100vw, 97px" data-recalc-dims="1" /></noscript> <span>{{ $empresa->telefono1 }}</span> </a>
                        </div>
                    </div>

                    <div class="col-md-9 col-sm-9 col-xs-12 toplink-static">
                        <p class="welcome-msg">Bienvenido a {{ $empresa->descripcion }} </p>
                        
                        <ul class="links">
                            <li class="first"><a
                                        href="{{route('tienda.micuenta')}}"
                                        title="Mi Cuenta">Mi Cuenta</a></li>
                            <li><a href="#"
                                   title="My Wishlist">Mi Lista</a></li>
                            <li><a href="#"
                                   title="My Cart" class="top-link-cart">Mi Carrito</a></li>
                            <li><a href="#"
                                   title="Checkout" class="top-link-checkout">Revisa</a></li>
                            @if(Auth::guest())
                                     <li class=" last"><a
                                        href="{{route('tienda.login')}}"
                                        title="Log In">Iniciar Sesión</a>
                                     </li>
                                <li class=" last"><a
                                            href="{{url('/web/create?id=10&id_modelo=218')}}"
                                            title="Registrarse">Registrarse</a>
                                </li>
                            @else
                                <li class=" last"><a
                                            href="{{url('/logout')}}"
                                            title="Registrarse">Cerrar sesión</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

                        <!-- 
    <div class="header">
        <div class="container">
            <div class="header-inner">
                <div class="row">
                    <div class="header-content clearfix">
                        <div class="top-logo col-xs-12 col-md-3 col-sm-12">
                            <a href="{ {url('/')}}" title="{ {$empresa->descripcion}}"
                               class="logo"><strong>{ {$empresa->descripcion}}</strong><img
                                        src="{ {asset( config('configuracion.url_instancia_cliente').'storage/app/logos_empresas/'.$empresa->imagen)}}"
                                        alt="Magento Commerce"></a>
                        </div>
                        <form class="col-xs-12 col-md-6 col-sm-12 serach" action="" method="GET">
                            <div class="box-search-bar clearfix">
                                <select class="btn" name="" id="">
                                    <option value="">Categorias</option>
                                </select>
                                <input type="text" class="input-text" autocomplete="off" id="search"
                                       placeholder="Search entire store here...">
                                <button type="submit" title="Search" class="btn"><i
                                            class="fa fa-search"></i></button>
                            </div>
                        </form>
                        <div class="col-xs-12 col-md-3 col-sm-12">
                            <ul class="nav-categorias ">
                                <li class="submenu nav-item">
                                    <div class="item-nav">
                                        <i class="fa fa-cart-plus" aria-hidden="true"></i>
                                        <p>Mi carrito</p>
                                    </div>
                                    <div id="carrito">
                                        <table id="lista-carrito" class="u-full-width">
                                            <thead>
                                            <tr>
                                                <th>Imagen</th>
                                                <th>Nombre</th>
                                                <th>Precio</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>

                                        <a href="#" id="vaciar-carrito" class="button u-full-width">Vaciar
                                            Carrito</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    -->

</header>
