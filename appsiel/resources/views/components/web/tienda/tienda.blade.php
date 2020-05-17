<link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
<link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">

<header>
    <!-- -->
    <div class="top-link">
        <div class="container">
            <div class="top-link-inner">
                <div class="row">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <div class="toplink-static">LINEA DIRECTA:<span>(+800) 123 456 7890</span></div>
                    </div>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <p class="welcome-msg">Bienvenido a Avipoulet </p>
                        <ul class="links">
                            <li class="first"><a
                                        href="{{ route( 'tienda.micuenta') }}"
                                        title="My Account">Mi Cuenta</a></li>
                            <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/wishlist/"
                                   title="My Wishlist">Mi Lista</a></li>
                            <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/checkout/cart/"
                                   title="My Cart" class="top-link-cart">Mi Carrito</a></li>
                            <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/checkout/onepage"
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

    <div class="header">
        <div class="container">
            <div class="header-inner">
                <div class="row">
                    <div class="header-content clearfix">
                        <div class="top-logo col-xs-12 col-md-3 col-sm-12">
                            <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/" title="Magento Commerce"
                               class="logo"><strong>Magento Commerce</strong><img
                                        src="http://avipoulet.com/img/logos/1584208639logo-png2png"
                                        alt="Magento Commerce"></a>
                        </div>
                        <form class="col-xs-12 col-md-6 col-sm-12 serach" action="{{route('tienda.busqueda')}}" method="GET">
                            <div class="box-search-bar clearfix">
                                <select class="btn" name="categoria" id="categoria_id">
                                    <option value="0">Categorias</option>
                                    @foreach($grupos as $key => $value)
                                        <option value="{{$value[0]->id}}">{{strtolower($key)}}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="input-text" autocomplete="off" id="search" name="search" required
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
    <div class="ma-menu clearfix">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-3 col-sm-12 mega-menu">
                    <div class="navleft-container visible-lg visible-md">
                        <div class="megamenu-title">
                            <h2>Categorias <em class="fa fa-caret-down"></em></h2>
                        </div>
                        <div id="pt_vmegamenu" class="pt_vmegamenu" style="overflow: visible; display: none;">
                            <ul class="pt_nav">
                                @foreach($grupos as $key => $value)
                                    <li><a href="{{route('tienda.filtrocategoria',$value[0]->id)}}">{{$key}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="top-search col-xs-12 col-md-9 col-sm-12 custom-menu">
                    <div class="ma-nav-mobile-container visible-xs">
                        <div class="navbar">
                            <div id="navbar-inner" class="navbar-inner navbar-inactive">
                                <div class="menu-mobile">
                                    <span class="brand navbar-brand">Categories</span>
                                    <a class="btn btn-navbar navbar-toggle">
                                        <i class="fas fa-bars"></i>
                                    </a>
                                </div>
                                <ul id="ma-mobilemenu" class="mobilemenu nav-collapse collapse">
                                    @foreach($grupos as $key => $value)
                                        <li class="level0 nav-1 level-top first parent">
                                            <a href="{{route('tienda.filtrocategoria',$value[0]->id)}}"
                                               class="level-top">
                                                <span>{{$key}}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="nav-container visible-lg visible-md">
                        <div class="container-inner">
                            <!-- 
                            <div id="pt_custommenu" class="pt_custommenu">
                                <div id="pt_menu_home" class="pt_menu act">
                                    <div class="parentMenu">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/">
                                            <span>Inicio</span>
                                        </a>
                                    </div>
                                </div>
                                <div id="pt_menu_link" class="pt_menu">
                                    <div class="parentMenu">
                                        <ul>
                                            <li><a
                                                        href="http://www.plazathemes.com/demo/ma_dicove/index.php/bestsellerproductlist/">Productos</a>
                                            </li>
                                            <li><a
                                                        href="http://www.plazathemes.com/demo/ma_dicove/index.php/newproductlist">Quienes
                                                    Somos</a></li>
                                            <li><a
                                                        href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts/">Blog
                                                    Us</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="clearBoth"></div>
                            </div>
                        -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<main>
    <div class="main-container col2-left-layout">
        <div class="container">
            <div class="container-inner">
                <div class="main">
                    <!-- Category Image-->
                    <!--<p class="category-image"><img
                                src="http://www.plazathemes.com/demo/ma_dicove/media/catalog/category/category.jpg"
                                alt="New arrivals" title="New arrivals"></p>    -->
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
<script src="{{asset('assets/tienda/js/categories.js')}}"></script>
