<?php
$empresa = App\Core\Empresa::find(1);
$configuracion = App\web\Configuraciones::all()->first();
?>

<div class="header" style="border-bottom: 1px solid black;">
    <div class="container">
        <div class="header-inner">
            <div class="row">
                <div class="header-content clearfix">
                    <div class="top-logo col-xs-12 col-md-3 col-sm-12">
                        <a href="{{url('/')}}" title="{{$empresa->descripcion}}"
                           class="logo"><strong>{{$empresa->descripcion}}</strong><img
                                    src="{{asset( config('configuracion.url_instancia_cliente').'storage/app/logos_empresas/'.$empresa->imagen)}}"
                                    alt="Tienda Online {{$empresa->descripcion}}"></a>
                    </div>
                    <form class="col-xs-12 col-md-6 col-sm-12 search" action="{{route('tienda.busqueda')}}" method="GET" onsubmit="buscar_descripcion(event)" id="form_consulta">
                        <div class="box-search-bar clearfix">
                            <input type="text" class="input-text" autocomplete="off" id="search" name="search" required
                                   placeholder="Buscar por producto, categoría... ">
                            <button type="submit"  title="Search" style="background-color: {{ $configuracion->color_primario }};"  class="btn"><i
                                        class="fa fa-search" style="background-color: {{ $configuracion->color_primario }};"></i></button>
                        </div>
                    </form>
                    <div class="col-xs-12 col-md-3 col-sm-12">
                        &nbsp;
                        <!-- 
                        <ul class="nav-categorias ">
                            <li class="submenu nav-item">
                                <div class="item-nav">
                                    <i class="fa fa-cart-plus" style="color: { { $configuracion->color_primario }};" aria-hidden="true"></i>
                                    <p style="color: { { $configuracion->color_primario }};">Mi carrito</p>
                                    <span class="item"></span>
                                </div>
                                <div id="carrito">
                                    <table id="lista-carrito" class="u-full-width">
                                        <thead>
                                        <tr>
                                            <th>Imagen</th>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    <a href="#" onclick="window.location.href='{ {route("tienda.comprar")}}'" style="color:white;background-color:red;" id="comprar" class="button u-full-width">Comprar</a>
                                    <a href="#" id="vaciar-carrito" class="button u-full-width">Vaciar
                                        Carrito</a>

                                </div>
                            </li>
                        </ul>
                    -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
