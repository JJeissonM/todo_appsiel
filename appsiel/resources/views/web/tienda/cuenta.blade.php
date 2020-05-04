@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/cuenta.css')}}">
@endsection

@section('content')
<header>
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
                                        href="{{route('tienda.micuenta')}}"
                                        title="My Account">Mi Cuenta</a></li>
                            <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/wishlist/"
                                   title="My Wishlist">Mi Lista</a></li>
                            <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/checkout/cart/"
                                   title="My Cart" class="top-link-cart">Mi Carrito</a></li>
                            <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/checkout/onepage"
                                   title="Checkout" class="top-link-checkout">Revisa</a></li>
                            <li class=" last"><a
                                        href="/login.html"
                                        title="Log In">Iniciar Sesión</a></li>
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
                                        src="http://www.plazathemes.com/demo/ma_dicove/skin/frontend/ma_dicove/ma_dicove/images/logo.gif"
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
                                <li><a href="">Newarrivals</a></li>
                                <li><a href="">Clothing</a></li>
                                <li><a href="">footwear</a></li>
                                <li><a href="">jewellery</a></li>
                                <li><a href="">accessories</a></li>
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
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                    <li class="level0 nav-1 level-top first parent">
                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/new-arrivals.html"
                                           class="level-top">
                                            <span>New arrivals</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="nav-container visible-lg visible-md">
                        <div class="container-inner">
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
                    <div class="main-inner">
                        <div class="row">
                            <div class="col-left sidebar col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="block block-account">
                                    <div class="block-title">
                                        <strong><span><font style="vertical-align: inherit;">MI CUENTA</font></span></strong>
                                    </div>
                                    <div class="block-content">
                                        <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                                            <ul>
                                                <li><a id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Panel de cuenta</font></font></strong></a></li>
                                                <li><a id="nav-infor-tab" data-toggle="tab" href="#nav-infor" role="tab" aria-controls="nav-infor" aria-selected="true"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></a></li>
                                                <li><a id="nav-directorio-tab" data-toggle="tab"
                                                       href="#nav-directorio" role="tab" aria-controls="nav-directorio"
                                                       aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones</font></font></a></li>
                                                <li class="last"><a id="nav-ordenes-tab" data-toggle="tab" href="#nav-ordenes" role="tab" aria-controls="nav-ordenes" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Mis ordenes</font></font></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-main col-lg-9 col-md-9 col-sm-12 col-xs-12">
                                <div class="my-account">
                                    <div class="dashboard">
                                        <div class="tab-content py-3 px-3 px-sm-0" style="border: 0;" id="nav-tabContent">
                                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                                 aria-labelledby="nav-home-tab">
                                        <div class="page-title">
                                            <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit; text-align: left !important;">MI TABLERO</font></font></h1>
                                        </div>
                                        <div class="welcome-msg">
                                            <p class="hello"><strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Hola Jordan Cuadro!</font></font></strong></p>
                                            <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Desde el Panel de control de Mi cuenta, puede ver una instantánea de la actividad reciente de su cuenta y actualizar la información de su cuenta. </font><font style="vertical-align: inherit;">Seleccione un enlace a continuación para ver o editar información.</font></font></p>
                                        </div>
                                        <div class="box-account box-info">
                                            <div class="box-head">
                                                <h2><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></h2>
                                            </div>
                                            <div class="col2-set">
                                                <div class="col-1" style="max-width: 50%">
                                                    <div class="box">
                                                        <div class="box-title">
                                                            <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información del contacto</font></font></h3>
                                                            <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                        </div>
                                                        <div class="box-content">
                                                            <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                        Jordan Cuadro </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                        jordan_j9@hotmail.com </font></font><br>
                                                                <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/edit/changepass/1/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambiar contraseña</font></font></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-2" style="max-width: 50%">
                                                    <div class="box">
                                                        <div class="box-title">
                                                            <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Boletines informativos</font></font></h3>
                                                            <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/newsletter/manage/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                        </div>
                                                        <div class="box-content">
                                                            <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                        Actualmente no estás suscrito a ningún boletín.                                    </font></font></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2-set">
                                                <div class="box">
                                                    <div class="box-title">
                                                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Directorio</font></font></h3>
                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Administrar direcciones</font></font></a>
                                                    </div>
                                                    <div class="box-content">
                                                        <div class="col-1" style="max-width: 50%">
                                                            <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">direccion de FACTURACION por defecto</font></font></h4>
                                                            <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                        No ha establecido una dirección de facturación predeterminada. </font></font><br>
                                                                <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar dirección</font></font></a>
                                                            </address>
                                                        </div>
                                                        <div class="col-2" style="max-width: 50%">
                                                            <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección de entrega por defecto</font></font></h4>
                                                            <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                        No ha establecido una dirección de envío predeterminada. </font></font><br>
                                                                <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar dirección</font></font></a>
                                                            </address>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                            </div>
                                            <div class="tab-pane fade" id="nav-infor" role="tabpanel"
                                                 aria-labelledby="nav-infor-tab">
                                                <div class="page-title">
                                                    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">EDITAR INFORMACIÓN DE LA CUENTA</font></font></h1>
                                                </div>
                                                {!! Form::model(['route'=>'tienda.productoupdated','method'=>'POST','class'=>'form-horizontal','id'=>'form-validate','autocomplete'=>'off','files'=>'true'])!!}
                                                <div class="fieldset">
                                                    <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></h2>
                                                    <ul class="form-list">
                                                        <li class="fields">
                                                            <div class="customer-name">
                                                                <div class="field name-firstname">
                                                                    <label for="firstname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Nombre</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" id="firstname" name="firstname" value="Jordan" title="Nombre de pila" maxlength="255" class="input-text required-entry">
                                                                    </div>
                                                                </div>
                                                                <div class="field name-lastname">
                                                                    <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Apellido</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" id="lastname" name="lastname" value="Cuadro" title="Apellido" maxlength="255" class="input-text required-entry">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <label for="email" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Dirección de correo electrónico</font></font></label>
                                                            <div class="input-box">
                                                                <input type="text" name="email" id="email" value="jordan_j9@hotmail.com" title="Dirección de correo electrónico" class="input-text required-entry validate-email">
                                                            </div>
                                                        </li>
                                                        <li class="control">
                                                            <input type="checkbox" name="change_password" id="change_password" value="1" onclick="setPasswordForm(this.checked)" title="Cambia la contraseña" class="checkbox"><label for="change_password"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambia la contraseña</font></font></label>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="fieldset" style="display:none;" id="form-password">
                                                    <h2 class="legend">Cambia la Contraseña</h2>
                                                    <ul class="form-list">
                                                        <li>
                                                            <label for="current_password" class="required"><em>*</em>Contraseña Actual</label>
                                                            <div class="input-box">
                                                                <!-- This is a dummy hidden field to trick firefox from auto filling the password -->
                                                                <input type="text" class="input-text" style="display: none" name="dummy" id="dummy">
                                                                <input type="password" title="Contraseña Actual" class="input-text" name="current_password" id="current_password">
                                                            </div>
                                                        </li>
                                                        <li class="fields">
                                                            <div class="field">
                                                                <label for="password" class="required"><em>*</em>Nueva Contraseña</label>
                                                                <div class="input-box">
                                                                    <input type="password" title="Nueva Contraseña" class="input-text validate-password" name="password" id="password">
                                                                </div>
                                                            </div>
                                                            <div class="field">
                                                                <label for="confirmation" class="required"><em>*</em>Confirmar Nueva Contraseña</label>
                                                                <div class="input-box">
                                                                    <input type="password" title="Confirmar Nueva Contraseña" class="input-text validate-cpassword" name="confirmation" id="confirmation">
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="buttons-set">
                                                    <p class="required"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">* Campos requeridos</font></font></p>
                                                    <p class="back-link"><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/"><small><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">«</font></font></small><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Volver</font></font></a></p>
                                                    <button type="submit" title="Salvar" class="button"><span><span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Salvar</font></font></span></span></button>
                                                </div>
                                                {!! Form::close() !!}
                                            </div>
                                            <div class="tab-pane fade" id="nav-directorio" role="tabpanel"
                                                 aria-labelledby="nav-directorio-tab">
                                                <div class="page-title">
                                                    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">AGREGAR NUEVA DIRECCIÓN</font></font></h1>
                                                </div>
                                                {!! Form::model(['route'=>'tienda.productoupdated','method'=>'POST','class'=>'form-horizontal','id'=>'form-validate','autocomplete'=>'off','files'=>'true'])!!}
                                                <div class="fieldset">
                                                    <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información del contacto</font></font></h2>
                                                    <ul class="form-list">
                                                        <li class="fields">
                                                            <div class="customer-name">
                                                                <div class="field name-firstname">
                                                                    <label for="firstname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Nombre</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" id="firstname" name="firstname" value="Jordan" title="Nombre de pila" maxlength="255" class="input-text required-entry">
                                                                    </div>
                                                                </div>
                                                                <div class="field name-lastname">
                                                                    <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Apellido</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" id="lastname" name="lastname" value="Cuadro" title="Apellido" maxlength="255" class="input-text required-entry">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="wide">
                                                            <label for="company"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Empresa</font></font></label>
                                                            <div class="input-box">
                                                                <input type="text" name="company" id="company" title="Empresa" value="" class="input-text ">
                                                            </div>
                                                        </li>
                                                        <li class="fields">
                                                            <div class="field">
                                                                <label for="telephone" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Teléfono</font></font></label>
                                                                <div class="input-box">
                                                                    <input type="text" name="telephone" value="" title="Teléfono" class="input-text   required-entry" id="telephone">
                                                                </div>
                                                            </div>
                                                            <div class="field">
                                                                <label for="fax"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Fax</font></font></label>
                                                                <div class="input-box">
                                                                    <input type="text" name="fax" id="fax" title="Fax" value="" class="input-text ">
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="fieldset">
                                                    <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Habla a</font></font></h2>
                                                    <ul class="form-list">
                                                        <li class="wide">
                                                            <label for="street_1" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Dirección</font></font></label>
                                                            <div class="input-box">
                                                                <input type="text" name="street[]" value="" title="Dirección" id="street_1" class="input-text  required-entry">
                                                            </div>
                                                        </li>
                                                        <li class="wide" style="margin-top: 20px">
                                                            <div class="input-box">
                                                                <input type="text" name="street[]" value="" title="Dirección 2" id="street_2" class="input-text" placeholder="Dirección 2">
                                                            </div>
                                                        </li>
                                                        <li class="fields">
                                                            <div class="field">
                                                                <label for="country" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> País</font></font></label>
                                                                <div class="input-box">
                                                                    <select class="validate-select" title="País" id="pais" name="pais_id" onchange="getCiudades()">
                                                                        <option value="">--Selecciones una opción--</option>
                                                                        @foreach($paises as $pais)
                                                                            <option value="{{$pais->id}}"><font style="vertical-align: inherit;">{{$pais->descripcion}}</font></option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="field">
                                                                <label for="city" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Ciudad</font></font></label>
                                                                <div class="input-box">
                                                                    <select class="validate-select" title="Ciudad" name="ciudad" id="ciudad" required>

                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="fields">
                                                            <div class="field">
                                                                <label for="zip" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Código postal</font></font></label>
                                                                <div class="input-box">
                                                                    <input type="text" name="postcode" value="" title="Código postal" id="zip" class="input-text validate-zip-international  required-entry">
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <input type="hidden" name="default_billing" value="1">
                                                        </li>
                                                        <li>
                                                            <input type="hidden" name="default_shipping" value="1">
                                                        </li>
                                                    </ul>
                                                </div>
                                                    <div class="buttons-set">
                                                        <p class="required"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">* Campos requeridos</font></font></p>
                                                        <p class="back-link"><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/"><small><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">«</font></font></small><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Volver</font></font></a></p>
                                                        <button type="submit" title="Salvar" class="button"><span><span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Salvar</font></font></span></span></button>
                                                    </div>
                                                {!! Form::close() !!}
                                            </div>
                                            <div class="tab-pane fade" id="nav-ordenes" role="tabpanel"
                                                 aria-labelledby="nav-ordenes-tab">
                                                <div class="page-title">
                                                    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">MIS PEDIDOS</font></font></h1>
                                                </div>
                                                <div class="welcome-msg">
                                                    <p class="hello"><strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Hola Jordan Cuadro!</font></font></strong></p>
                                                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Desde el Panel de control de Mi cuenta, puede ver una instantánea de la actividad reciente de su cuenta y actualizar la información de su cuenta. </font><font style="vertical-align: inherit;">Seleccione un enlace a continuación para ver o editar información.</font></font></p>
                                                </div>
                                                <div class="box-account box-info">
                                                    <div class="box-head">
                                                        <h2><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></h2>
                                                    </div>
                                                    <div class="col2-set">
                                                        <div class="col-1" style="max-width: 50%">
                                                            <div class="box">
                                                                <div class="box-title">
                                                                    <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información del contacto</font></font></h3>
                                                                    <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                                </div>
                                                                <div class="box-content">
                                                                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                Jordan Cuadro </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                jordan_j9@hotmail.com </font></font><br>
                                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/edit/changepass/1/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambiar contraseña</font></font></a>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-2" style="max-width: 50%">
                                                            <div class="box">
                                                                <div class="box-title">
                                                                    <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Boletines informativos</font></font></h3>
                                                                    <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/newsletter/manage/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                                </div>
                                                                <div class="box-content">
                                                                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                Actualmente no estás suscrito a ningún boletín.                                    </font></font></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col2-set">
                                                        <div class="box">
                                                            <div class="box-title">
                                                                <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Directorio</font></font></h3>
                                                                <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Administrar direcciones</font></font></a>
                                                            </div>
                                                            <div class="box-content">
                                                                <div class="col-1" style="max-width: 50%">
                                                                    <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">direccion de FACTURACION por defecto</font></font></h4>
                                                                    <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                No ha establecido una dirección de facturación predeterminada. </font></font><br>
                                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar dirección</font></font></a>
                                                                    </address>
                                                                </div>
                                                                <div class="col-2" style="max-width: 50%">
                                                                    <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección de entrega por defecto</font></font></h4>
                                                                    <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                No ha establecido una dirección de envío predeterminada. </font></font><br>
                                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar dirección</font></font></a>
                                                                    </address>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
<footer>
    <div class="ma-footer-static-top">
        <div class="container">
            <div class="footer-static-top">
                <div class="row">
                    <div class="f-col f-col-1 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">NUESTROS SERVICIOS</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content">
                            <ul>
                                <li class="first"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/about-magento-demo-store">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Sobre nosotros</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Historial de pedidos</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/privacy-policy-cookie-restriction-mode">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Devoluciones</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer-service">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Servicio personalizado</font>
                                        </font>
                                    </a></li>
                                <li class="last"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalogsearch/term/popular">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Términos y Condiciones</font>
                                        </font>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="f-col f-col-2 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">nuestro apoyo</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content ">
                            <ul>
                                <li class="first"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalog/seo_sitemap/category">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Mapa del sitio</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/privacy-policy-cookie-restriction-mode">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Política de privacidad</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/index">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Su cuenta</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalogsearch/advanced">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Búsqueda Avanzada</font>
                                        </font>
                                    </a></li>
                                <li class="last"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Contáctenos</font>
                                        </font>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="f-col f-col-3 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">MI CUENTA</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content">
                            <ul>
                                <li class="first"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/index">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Mi cuenta</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Historial de pedidos</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Devoluciones</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Especiales</font>
                                        </font>
                                    </a></li>
                                <li class="last"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalog/seo_sitemap/category">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Mapa del sitio</font>
                                        </font>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="f-col f-col-4 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">información</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content">
                            <ul>
                                <li class="first"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/about-magento-demo-store">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Sobre nosotros</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Información</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/privacy-policy-cookie-restriction-mode">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Política de privacidad</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer-service">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Servicio personalizado</font>
                                        </font>
                                    </a></li>
                                <li class="last"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalogsearch/term/popular">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Términos y Condiciones</font>
                                        </font>
                                    </a></li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="ma-footer-static">
        <div class="container">
            <div class="footer-static">
                <div class="row">
                    <div class="f-col f-col-1 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">SIGA CON NOSOTROS</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content">
                            <ul class="link-follow">
                                <li class="first"><a class="facebook fa fa-facebook"
                                                     href="https://www.facebook.com/plazathemes">
                                    </a>
                                </li>

                                <li><a class="skype fab fa-instagram"
                                       href="https://twitter.com/plazathemes">
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="f-col f-col-2 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">NUESTRO APOYO</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content ">
                            <ul>
                                <li class="first"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalog/seo_sitemap/category">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Mapa del sitio</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/privacy-policy-cookie-restriction-mode">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Política de privacidad</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/index">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Su cuenta</font>
                                        </font>
                                    </a></li>
                                <li><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalogsearch/advanced">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Búsqueda Avanzada</font>
                                        </font>
                                    </a></li>
                                <li class="last"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Contáctenos</font>
                                        </font>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="f-col f-col-3 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">MI CUENTA</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content">
                            <ul>
                                <li class="first"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/index">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Mi cuenta</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Historial de pedidos</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Devoluciones</font>
                                        </font>
                                    </a></li>
                                <li><a href="http://www.plazathemes.com/demo/ma_dicove/index.php/contacts">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Especiales</font>
                                        </font>
                                    </a></li>
                                <li class="last"><a
                                            href="http://www.plazathemes.com/demo/ma_dicove/index.php/catalog/seo_sitemap/category">
                                        <font style="vertical-align: inherit;">
                                            <font style="vertical-align: inherit;">Mapa del sitio</font>
                                        </font>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="f-col f-col-4 col-sm-6 col-md-3 col-sms-6 col-smb-12">
                        <div class="footer-static-title">
                            <h3>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;">NUESTRO NEWSLETTER</font>
                                </font>
                            </h3>
                        </div>
                        <div class="footer-static-content">
                            <div class="block-subscribe">
                                <div class="subscribe-title follow-title">
                                    <h3>Sign up for newslletter</h3>
                                </div>
                                <form
                                        action="http://www.plazathemes.com/demo/ma_dicove/index.php/newsletter/subscriber/new/"
                                        method="post" id="newsletter-validate-detail">
                                    <div class="subscribe-content">
                                        <!--<div class="form-subscribe-header">
                    <label for="newsletter">Sign Up for Our Newsletter:</label>
                </div>-->
                                        <div class="input-box">
                                            <input type="text" name="email" id="newsletter"
                                                   title="Suscríbase a nuestro boletín"
                                                   class="input-text required-entry validate-email">
                                        </div>
                                        <div class="actions">
                                            <button type="submit" title="Suscribir" class="button"><span><span>
                                                            <font style="vertical-align: inherit;">
                                                                <font style="vertical-align: inherit;">Suscribir</font>
                                                            </font>
                                                        </span></span></button>
                                        </div>
                                    </div>
                                </form>
                                <script type="text/javascript">
                                    //<![CDATA[
                                    var newsletterSubscriberFormDetail = new VarienForm('newsletter-validate-detail');
                                    //]]>
                                </script>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</footer>
@endsection

@section('script')
<script src="{{asset('assets/tienda/js/categories.js')}}"></script>
<script type="text/javascript">
    function getCiudades() {
        var pais = $("#pais").val();
        if (pais == null) {
            alert('Debe indicar todos loa parametros para contuniuar');
        }
        $.ajax({
            type: 'GET',
            url: '{{url('')}}/' + 'tienda/' + pais + '/getciudades',
            data: {},
        }).done(function (msg) {
            $("#ciudad option").each(function () {
                $(this).remove();
            });
            if (msg != "null") {
                var m = JSON.parse(msg);
                $.each(m, function (index, item) {
                    $("#ciudad").append("<option value='" + item.id + "'>" + item.value + "</option>");
                });
            } else {
                alert('El pais seleccionado no tiene ciudades registradas');
            }
        });
    }
    //<![CDATA[
    var dataForm = new VarienForm('form-validate', true);
    function setPasswordForm(arg){
        if(arg){
            $('#form-password').removeAttr('style','display');
            $('#current_password').attr('class','input-text required-entry');
            // $('current_password').addClassName('required-entry');
            // $('password').addClassName('required-entry');
            $("#password").attr('class','input-text required-entry');
            $("#confirmation").attr('class','input-text required-entry');
            // $('confirmation').addClassName('required-entry');

        }else{
            $('#form-password').attr('style','display:none');
            // $('current_password').removeClassName('required-entry');
            $('#current_password').removeClass('required-entry');
            $('#password').removeClass('required-entry');
            // $('password').removeClassName('required-entry');
            $('#confirmation').removeClass('required-entry');
            // $('confirmation').removeClassName('required-entry');
        }
    }

    //]]>
</script>
@endsection

