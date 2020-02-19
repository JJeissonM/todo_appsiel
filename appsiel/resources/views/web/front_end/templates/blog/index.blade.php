<!DOCTYPE html>
<html lang="es">
  <head>
    <title>{{ $pagina->descripcion }}</title>

    @if($pagina->favicon != '')
      <link rel="shortcut icon" href="{{ '../'.$pagina->favicon}}" type="image/x-icon">
    @else
      <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if($pagina->codigo_google_analitics != '')
      <!-- Global site tag (gtag.js) - Google Analytics -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={{$pagina->codigo_google_analitics}}"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{$pagina->codigo_google_analitics}}');
      </script>
    @endif

    <!-- Hojas de estilo  -->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/pagina_web/'.$pagina->plantilla.'/estilos.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/pagina_web_sticky_social_bar.css') }}">
    <!-- Add icon library -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Scripts  -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>

  </head>
  <body id="myPage" data-spy="scroll" data-target=".navbar" data-offset="60">

    <div class="container-fluid contenido">

      @include('web.front_end.modulos.menu.index', ['clase_fixed' => 'navbar-fixed-top', 'mostar_logo' => true,'url_logo' => asset( config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/'.$pagina->logo), 'slogan' => $empresa->descripcion, 'clase_alineacion_texto' => 'navbar-right'])

      <br>

      <div class="container-fluid contenido_blog">

      {{ Form::bsMigaPan($miga_pan) }}
      <hr>
        @if( $galeria_imagenes )
          @include('web.front_end.modulos.galeria_imagenes.index')
        @else
          @include('web.front_end.templates.blog.articulos')
        @endif
      </div> 

      <br>


    <footer class="container-fluid pie_pagina">
      <hr>
      <div class="row">
        <div class="col-sm-3">
          Dirección: {{ $empresa->direccion1 }}<br/>
          Teléfono(s): {{ $empresa->telefono1 }}<br/>
          Email: {{ $empresa->email }}<br/>
        </div>

        <div class="col-sm-6">
          <p style="text-align: center;">© {{ date('Y') }} {{ $empresa->descripcion }}</p>
        </div>

        <div class="col-sm-3">
          <p style="text-align: center;">
            Plataforma de gestión académica
          </p>
          <p style="text-align: center;">
            <a href="{{ url('/inicio') }}">
              <img src="https://appsiel.com.co/assets/img/pagina_web/logo.png" height="77">
              <span style="color: cyan;">
                APPSIEL
              </span>
            </a>
          </p>
        </div>
      </div>

      <hr>
    </footer>
      <!-- <p style="text-align: center; font-style: italic; font-weight: bold; font-size: 0.9em;">
        Sitio desarrollado por el software <a href="https://appsiel.com.co" target="_blank">APPSIEL</a>
      </p>
    -->
    
    <script src="{{ asset('assets/pagina_web/funciones.js') }}"></script>
        
    <script src="{{ asset('assets/pagina_web/jquery.smooth-scroll.min.js') }}"></script>

    @yield('scripts')
    
    </div> <!-- End - Contenido -->
  </body>
</html>