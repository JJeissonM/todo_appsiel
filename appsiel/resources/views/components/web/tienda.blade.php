<link rel="stylesheet" href="{{asset('css/normalize.css')}}">
<link rel="stylesheet" href="{{asset('css/skeleton.css')}}">
<link rel="stylesheet" href="{{asset('css/custom.css')}}">

<style>

    .nav-search {
        height: 72px;
        border-bottom: 1px solid #ffe800;
    }

    #search {
        width:100%;
        background-color: #F1F3F4;
        border: 1px solid #F1F3F4;
    }


    .nav-categorias {
        display: flex;
        justify-content: center;
    }

    .nav-categorias li{
        list-style: none;
        font-size: 14px;
    }

    .nav-categorias li a {
        color: black;
    }

    .item-nav {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }

    .item-nav i {
        width: 100%;
        font-size: 20px;
        text-align: center;
    }
    .item-nav p {
        font-size: 14px;
        margin-bottom: 0 !important;
    }

    #filtros {
        display: flex;
        justify-content: center;
        align-items: center;
        border-bottom: 1px solid #000;
    }

    #filtro-ordenar {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #filtro-ordenar span {
        font-weight: bold;
    }

    #filtro-ordenar select {
         border: none;
         color: gray;
         background-color: transparent;
         margin-bottom: 0!important;
    }

    #productos  {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .contenido-producto {
        width: 100%;
        padding: 10px;
    }

    @media (min-width: 468px) {
        .contenido-producto {
            width: 50%;
        }
    }
    
    @media (min-width: 768px) {
        .contenido-producto {
            width: 18%;
        }
    }


    .contenido-producto img,.contenido-producto p{
       margin-bottom: 0;
    }

    .contenido-producto img{
        max-width: 100%;
        margin: 0;
    }


    .precio span {
      color: red;
    }

    .button-opciones {
        display: none;
        justify-content: space-between;
        align-items: center;
        background-color: #F4F4F4;
        height: 40px;
        width: 100%;
        padding: 10px;
        border: 1px solid #F4F4F4;
        border-radius: 6px;
        box-shadow: 0px 2px 5px 1px gray;
    }

    #lista-cursos {
        background-color: white;
    }


</style>

<header id="header" class="header">
    <div class="container-fluid">
        <div class="row d-flex justify-content-arroun nav-search">
            <div class="col-md-3">
            </div>
            <div class="col-md-5">
                <input type="text" id="search"  placeholder="¿Qué buscas?">
            </div>
            <div class="col-md-4">
                <ul class="nav-categorias ">
                    <li class="nav-item">
                        <div class="item-nav">
                            <i class="fa fa-user-o" aria-hidden="true"></i>
                            <p>Mi cuenta</p>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="item-nav">
                            <i class="fa fa-truck" aria-hidden="true"></i>
                            <p>Mi pedido</p>
                        </div>
                    </li>
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

                            <a href="#" id="vaciar-carrito" class="button u-full-width">Vaciar Carrito</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container-fludi">
        <ul class="nav nav-categorias" >
            <li class="nav-item">
                <a class="nav-link " href="#">Mercado</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Tecnología</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Electrodomésticos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Hogar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Moda y accesorios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Salud y belleza</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Bebés,niños y jugueteria</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#">Deportes y Tiempo Libre</a>
            </li>
        </ul>
    </div>
</header>

<div id="lista-cursos">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 col-sm-12">
                <div class="card" style="width: 18rem;">
                    <img class="card-img-top" src="..." alt="Card image cap">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row" id="filtros">
                    <div class="col-md-8">
                        <span style="color: gray;">221 Resultados</span>
                    </div>
                    <div class="col-md-4" id="filtro-ordenar">
                        <span>Ordenar por:</span>
                        <select name="" id="">
                            <option value="">Relevancia</option>
                            <option value="">Más Vendidos</option>
                            <option value="">Descuento</option>
                            <option value="">Mayor precio primero</option>
                            <option value="">Menor precio primero</option>
                        </select>
                    </div>
                </div>
                <div class="row" id="productos">
                    <div class="contenido-producto">
                        <img src="{{asset('img/carrito/curso3.jpg')}}" loading="lazy"  class="imagen-curso u-full-width">
                        <div class="info-card">
                            <h4>Banana unidad</h4>
                            <p>Unidad a $400</p>
                            <p class="precio">$200  <span class="u-pull-right ">$15</span></p>
                            <a href="#" class="u-full-width button-primary button input agregar-carrito" data-id="3">!Lo quiero!</a>
                            <div class="button-opciones">
                                <a href=""  style="color: gray;"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <p>1 und</p>
                                <a href="" style="color: #FD9943;"><i class="fa fa-plus" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="contenido-producto">
                        <img src="{{asset('img/carrito/curso3.jpg')}}" loading="lazy"  class="imagen-curso u-full-width">
                        <div class="info-card">
                            <h4>Banana unidad</h4>
                            <p>Unidad a $400</p>
                            <p class="precio">$200  <span class="u-pull-right ">$15</span></p>
                            <a href="#" class="u-full-width button-primary button input agregar-carrito" data-id="3">!Lo quiero!</a>
                            <div class="button-opciones">
                                <a href=""  style="color: gray;"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <p>1 und</p>
                                <a href="" style="color: #FD9943;"><i class="fa fa-plus" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="contenido-producto">
                        <img src="{{asset('img/carrito/curso3.jpg')}}" loading="lazy"  class="imagen-curso u-full-width">
                        <div class="info-card">
                            <h4>Banana unidad</h4>
                            <p>Unidad a $400</p>
                            <p class="precio">$200  <span class="u-pull-right ">$15</span></p>
                            <a href="#" class="u-full-width button-primary button input agregar-carrito" data-id="3">!Lo quiero!</a>
                            <div class="button-opciones">
                                <a href=""  style="color: gray;"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <p>1 und</p>
                                <a href="" style="color: #FD9943;"><i class="fa fa-plus" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="contenido-producto">
                        <img src="{{asset('img/carrito/curso3.jpg')}}" loading="lazy"  class="imagen-curso u-full-width">
                        <div class="info-card">
                            <h4>Banana unidad</h4>
                            <p>Unidad a $400</p>
                            <p class="precio">$200  <span class="u-pull-right ">$15</span></p>
                            <a href="#" class="u-full-width button-primary button input agregar-carrito" data-id="3">!Lo quiero!</a>
                            <div class="button-opciones">
                                <a href=""  style="color: gray;"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <p>1 und</p>
                                <a href="" style="color: #FD9943;"><i class="fa fa-plus" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="contenido-producto">
                        <img src="{{asset('img/carrito/curso3.jpg')}}" loading="lazy"  class="imagen-curso u-full-width">
                        <div class="info-card">
                            <h4>Banana unidad</h4>
                            <p>Unidad a $400</p>
                            <p class="precio">$200  <span class="u-pull-right ">$15</span></p>
                            <a href="#" class="u-full-width button-primary button input agregar-carrito" data-id="3">!Lo quiero!</a>
                            <div class="button-opciones">
                                <a href=""  style="color: gray;"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <p>1 und</p>
                                <a href="" style="color: #FD9943;"><i class="fa fa-plus" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div> <!--.row-->
            </div>
        </div>
    </div>
</div>

<script src="{{asset('js/carrito/app.js')}}"></script>