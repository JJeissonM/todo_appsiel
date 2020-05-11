@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
@endsection

@section('content')
    @include('web.tienda.header')
    <main>
        <div class="main-container col2-left-layout">
            <div class="container">
                <div class="container-inner">
                    <div class="main">
                        <!-- Category Image-->
                        <div class="main-inner">
                            <div class="row">
                                <div class="col-main">
                                    <div class="account-login">
                                        <div class="page-title">
                                            <h1>Inicia sesión o Crea una cuenta con nostros</h1>
                                        </div>
                                        <form action="" method="post" id="login-form">
                                            <input name="form_key" type="hidden" value="fj3WGsswFmnStgbG">
                                            <div class="col2-set">
                                                <div class="new-users">
                                                    <div class="content">
                                                        <h2>Clientes Nuevos</h2>
                                                        <p>Al crear una cuenta en nuestra tienda, podrá pasar por el proceso de pago más rápido, almacenar múltiples direcciones de envío, ver y rastrear sus pedidos en su cuenta y más.</p>
                                                    </div>
                                                </div>
                                                <div class="registered-users">
                                                    <div class="content">
                                                        <h2>Clientes Registrados</h2>
                                                        <p>Si tiene una cuenta con nosotros, inicie sesión.</p>
                                                        <ul class="form-list">
                                                            <li>
                                                                <label for="email" class="required"><em>*</em>Email</label>
                                                                <div class="input-box">
                                                                    <input type="text" name="login[username]" value="" id="email" class="input-text required-entry validate-email" title="Email Address">
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <label for="pass" class="required"><em>*</em>Contraseña</label>
                                                                <div class="input-box">
                                                                    <input type="password" name="login[password]" class="input-text required-entry validate-password" id="pass" title="Password">
                                                                </div>
                                                            </li>
                                                        </ul>
                                                        <p class="required">* Campos Requeridos</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col2-set">
                                                <div class="new-users">
                                                    <div class="buttons-set">
                                                        <button type="button" title="Create an Account" class="button" onclick="window.location='{{route('tienda.cuenta')}}';"><span><span>Crear una Cuenta</span></span></button>
                                                    </div>
                                                </div>
                                                <div class="registered-users">
                                                    <div class="buttons-set">
                                                        <a href="" class="f-left">¿Olvidaste Tu Contraseña?</a>
                                                        <button type="submit" class="button" title="Login" name="send" id="send2"><span><span>Iniciar Sesión</span></span></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('web.tienda.footer')
@endsection

@section('script')
    <script src="{{asset('assets/tienda/js/categories.js')}}"></script>
    <script type="text/javascript">
    </script>
@endsection
