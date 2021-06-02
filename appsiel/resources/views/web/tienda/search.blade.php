<?php
$empresa = App\Core\Empresa::find(1);
$configuracion = App\web\Configuraciones::all()->first();
?>

<div class="header font-tienda"style="border-bottom: 1px solid whitesmoke; background-color: white">
    <div class="container">
        <div class="header-inner">
            <div class="row">
                <div class="header-content clearfix">
                    <div class="col-md-3"></div>
                    <form class="col-xs-12 col-md-6 col-sm-12 search" action="{{route('tienda.busqueda')}}" method="GET" onsubmit="buscar_descripcion(event)" id="form_consulta">
                        <div class="box-search-bar clearfix">
                            <input type="text" class="input-text" autocomplete="off" id="search" name="search" required placeholder="Buscar por producto... ">  
                            <button type="submit"  title="Search" style="background-color: var(--color-primario);"  class="btn"><i class="fa fa-search" style="background-color: var(--color-primario);"></i></button>
                        </div>
                    </form>
                    <div class="col-xs-12 col-md-3 col-sm-12 my-5">
                        <ul class="nav-categorias ">
                            <li class="submenu nav-item">
                                <div class="item-nav">
                                    <i class="fa fa-shopping-cart" style="color: var(--color-primario);" aria-hidden="true"></i>
                                    <p style="color: var(--color-primario);">Mi carrito</p>
                                    <span class="item"></span>
                                </div>
                                <div id="carrito">
                                    <table id="lista-carrito" class="u-full-width">
                                        <thead>
                                        <tr>
                                            <th>Imagen</th>
                                            <th>Nombre</th>
                                            <th>Cant.</th>
                                            <th>Precio</th>                                            
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                        <a href="#" onclick="window.location.href='{{route("tienda.comprar")}}'"  id="comprar" class="btn button btn-block" style="background-color: var(--color-primario); border: none ;font-size: 16px; color: white">Comprar</a>
                                        <a href="#" id="vaciar-carrito" class="button btn-block">Vaciar
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
