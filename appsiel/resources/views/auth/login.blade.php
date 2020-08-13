@extends('layouts.login')

@section('content')
<div class="login-form">
    <form role="form" method="POST" action="{{ url('/login') }}">
        {{ csrf_token() }}
          
          <?php
            if( app()->environment() != 'demo' )
            {
              $mensaje = '';
              $mensaje2 = '';
              $email = old('email');
              $contrasenia = '';
            }else{
              $mensaje = '<div class="alert alert-warning">
                            <strong>¡Advertencia!</strong> Los datos de la plataforma demo serán borrados periodicamente.
                          </div>';
              $mensaje2 = '<div style="color: red; width: 100%; text-align: center;">Presione el botón para ingresar. <i class="fa fa-arrow-up"></i><div>';
              $email = 'demo@appsiel.com.co';
              $contrasenia = 'demo123*';
            }
          ?>
          

        <!-- 
        -->

        <div class="avatar">
            <img src="{{asset('assets/img/logo_appsiel.png')}}" alt="Logo">
        </div>
        <h2 class="text-center">Bienvenido</h2>
        
        {!! $mensaje !!}

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
              <input id="email" type="text" class="form-control" name="email" placeholder="Usuario" value="{{ $email }}" required="required">
            </div>
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <div class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
              <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña" required="required" value="{{ $contrasenia }}">
            </div>
            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg btn-block">Ingresar</button>
            {!! $mensaje2 !!}
        </div>
    </form>
</div>

<div class="footer">
    <hr>
  <p>Desarrollado por: <a href="https://appsiel.com.co" target="_blank">APPSIEL S.A.S.</a></p>
</div>
@endsection
