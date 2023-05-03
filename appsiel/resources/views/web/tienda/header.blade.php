<?php 
    $empresa = App\Core\Empresa::find(1);
    $configuracion = App\web\Configuraciones::all()->first();
?>

<header id="navegacion-tienda">
    <div class="top-link" style="background: var(--color-primario,#42A3DC); font-size:20px">
        <div class="container" style="padding: 0 ">
            <div class="top-link-inner">
                <div class="header-tienda">
                    <div class="toplink-static d-flex justify-content-center" style="/*width: auto;*/ height: 60px;">
                        <div style="position: absolute; z-index: 10;" >
                            <a href="{{ config('pagina_web.main_page_tienda_online') }}">
                                <img src="{{asset( config('configuracion.url_instancia_cliente').'storage/app/logos_empresas/'.$empresa->imagen)}}" style="z-index: 11000; height: 60px; width: 60px; min-width:60px"> 
                            </a>                                  
                        </div>                                                     
                    </div>
                        

                    <ul class="links">
                        
                        <li>
                            <span class="welcome-msg" style="color: white; white-space: nowrap">
                                {{ $empresa->direccion1 }}
                            </span>
                        </li>

                        <li> &nbsp; </li>
                        <li> &nbsp; </li>
                        <li> &nbsp; </li>
                        
                        <li>
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp;
                            <a href="{{route("tienda.comprar")}}" title="My Cart" class="top-link-cart">Ver pedido</a>
                        </li>
                        
                        @if(Auth::guest())
                            <li class=" last">
                                <i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp; 
                                <a href="{{route('tienda.login')}}" title="Iniciar sesión">Iniciar Sesión</a>
                            </li>
                            <li class=" last">
                                <i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp;
                                <a
                                        href="{{route('tienda.nuevacuenta')}}"
                                        title="Registrarse"
                                        onclick="registrarse( event )">Registrarse</a>
                            </li>
                        @else
                            <li class="first" style="order: 1">
                                <i class="fa fa-user-circle" aria-hidden="true"></i>&nbsp;
                                <a href="{{route('tienda.micuenta')}}" title="Mi Cuenta">Mi Cuenta</a>
                            </li>
                            <li class=" last">
                                <i class="fa fa-sign-out" aria-hidden="true"></i>&nbsp;
                                <a href="{{url('/logout')}}" title="Cerra sesión">Cerrar Sesión</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>