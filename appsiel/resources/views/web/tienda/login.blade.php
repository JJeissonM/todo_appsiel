@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
    <link href="{{asset('assets/css/toastr.min.css')}}" rel="stylesheet">

    <style>
        .aten{
            padding: 15px 15px 20px 15px;
            border-left: 5px solid #F98200;
            background: #F5F5F5;
            margin-bottom: 30px;
            line-height: 26px!important;
        }
    </style>
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
                                    <br><br>                                   

                                    @if(Session::has('flash_message'))

                                        <p class="aten">
                                            {!! session('flash_message') !!}
                                        </p>

                                    @endif

                                    <div class="account-login">
                                        <div class="page-title">
                                            <h1>Inicia sesión o Crea una cuenta con nosotros</h1>
                                        </div>
                                        <form action="{{ url('/login') }}" method="post" id="login-form">
                                            {{ csrf_field() }}
                                                <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                                                    <div class="col-md-6 col-sm-12">
                                                        <div class="content">
                                                            <h2>Soy Nuevo</h2>
                                                            <p>Al crear una cuenta en nuestra tienda, puedes hacer el pago más rápido, enviar tus productos y muchos beneficios más.</p>
                                                            <div class="buttons-set">
                                                                <button type="button" title="Create an Account" class="button" onclick="window.location.href='{{route('tienda.nuevacuenta')}}';"><span><span>Crear una Cuenta</span></span></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12">
                                                        <div class="content">
                                                            <h2>Ya tengo una cuenta</h2>
                                                            <p>Si tiene una cuenta con nosotros, inicie sesión.</p>
                                                            <ul class="form-list">
                                                                <li>
                                                                    <label for="email" class="required"><em>*</em>Email</label>
                                                                    <div class="input-box">
                                                                        <input style="font-size: 16px;" placeholder="Correo Electronico" type="text" name="email"  value="{{old('email')}}" id="email" class="input-text required-entry validate-email" title="Email Address">
                                                                    </div>
                                                                    @if ($errors->has('email'))
                                                                    <span class="help-block text-danger">
                                                                        <strong>{{ $errors->first('email') }}</strong>
                                                                    </span>
                                                                    @endif
                                                                </li>
                                                                <li>
                                                                    <label for="pass" class="required"><em>*</em>Contraseña</label>
                                                                    <div class="input-box">
                                                                        <input style="font-size: 16px;" placeholder="Password" type="password" name="password"  class="input-text required-entry validate-password" id="pass" title="Password">
                                                                    </div>
                                                                    @if ($errors->has('password'))
                                                                    <span class="help-block text-danger">
                                                                        <strong>{{ $errors->first('password') }}</strong>
                                                                    </span>
                                                                    @endif
                                                                </li>
                                                            </ul>
                                                            <div class="buttons-set">
                                                                <!--<a href="" class="f-left">¿Olvidaste Tu Contraseña?</a>-->
                                                                <button style="width: 254px;" type="submit" class="button mt-3" title="Login" name="send" id="send2"><span><span>Iniciar Sesión</span></span></button>
                                                            </div>
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
    {!! Form::footer($footer,$redes,null,'small')  !!}
@endsection

@section('script')
    <script src="{{asset('assets/tienda/js/categories.js')}}"></script>
    <script src="{{asset('assets/js/toastr.min.js')}}"></script>
    <?php
        if(isset($_GET['reg'])){
            echo '<script type="text/javascript">';
            echo 'toastr["success"]("Se ha registrado satisfactoriamente")';
            echo '</script>';
        }    
    ?>

@endsection
