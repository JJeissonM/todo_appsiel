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
                            <span style="line-height: 40px; color: white;">
                                Línea directa: &nbsp; <a href="https://api.whatsapp.com/send?phone=+57{{ $empresa->telefono1 }}" target="_blank"><i style="font-size: 16px; color: green;" class="fa fa-whatsapp" aria-hidden="true"></i> {{ $empresa->telefono1 }}</a>
                            </span>
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
                                        title="Iniciar sesión">Iniciar Sesión</a>
                                     </li>
                                <li class=" last"><a
                                            href="{{url('/web/create?id=10&id_modelo=218')}}"
                                            title="Registrarse"
                                            onclick="registrarse( event )">Registrarse</a>
                                </li>
                                <li class=" last"><button
                                            title="Registrarse"
                                            onclick="registrarse( event )">Registrarse 2</button>
                                </li>
                            @else
                                <li class=" last"><a
                                            href="{{url('/logout')}}"
                                            title="Cerra sesión">Cerrar Sesión</a>
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
                            <a href="{{url('/')}}" title="{{$empresa->descripcion}}"
                               class="logo"><strong>{{$empresa->descripcion}}</strong><img
                                        src="{{asset( config('configuracion.url_instancia_cliente').'storage/app/logos_empresas/'.$empresa->imagen)}}"
                                        alt="Tienda Online"></a>
                        </div>
                        <form class="col-xs-12 col-md-6 col-sm-12 search" action="{{route('tienda.busqueda')}}" method="GET" onsubmit="buscar_descripcion(event)" id="form_consulta">
                            <div class="box-search-bar clearfix" style="color: black !important;">
                                <select class="btn" name="categoria" id="categoria_id" onchange="filtrar_categoria(value, this.options[this.selectedIndex] )">
                                    <option value="0">Categorias</option>
                                    @foreach($grupos as $key => $value)
                                        <option value="{{$value[0]->id}}">{{strtolower($key)}}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="input-text" autocomplete="off" id="search" name="search" required
                                       placeholder="Buscar por producto, categoría... ">
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
                <!--     -->

</header>