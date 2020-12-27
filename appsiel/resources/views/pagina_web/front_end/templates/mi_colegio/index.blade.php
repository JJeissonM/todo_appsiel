<!DOCTYPE html>
<html lang="es">
  <head>
    <title>{{ $pagina->descripcion }}</title>

    @if($pagina->favicon != '')
      <link rel="shortcut icon" href="{{$pagina->favicon}}" type="image/x-icon">
    @else
      <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="{{ $pagina->meta_description }}"/>
    <meta name="keywords" content="{{ $pagina->meta_keywords }}"/>

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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/pagina_web/estilos.css') }}">
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

      @include('pagina_web.front_end.modulos.banner.top_escudo_redes')

      @include('pagina_web.front_end.modulos.menu.index')

      @include('pagina_web.front_end.modulos.carousel.index',['id'=>0])

      <br>

      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-12 noticias">
            @include('pagina_web.front_end.modulos.articulos_destacados.index')
          </div>
        </div>
      </div> 

      <br>

      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-6 anuncios">
            @include('pagina_web.front_end.modulos.anuncios.index',['id'=>1])
          </div>

          <div class="col-sm-6">
            <iframe allowfullscreen="true" allowtransparency="true" frameborder="0" height="314" scrolling="no" src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Ffudacolsate%2Fvideos%2F2103269639741050%2F&amp;show_text=0&amp;width=560" style="border: none; overflow: hidden;" width="560"></iframe>
          </div>
        </div>
      </div> 
    

    <!-- Container (Contact Section) -->
    <div id="contact" class="container-fluid">
      <div class="row">
        <div class="col-sm-5">
          <div class="panel panel-info">
            <div class="panel-heading">
              Formulario de contacto
            </div>
            <div class="panel-body">
              <div id="resultado_consulta"></div>          
              @include('pagina_web.front_end.modulos.contactenos.index')
            </div>
            <div id="div_cargando">Cargando...</div>  
          </div>       
        </div>

        <div class="col-sm-7" id="ubicacion">
          <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15693.582831000309!2d-73.2524613!3d10.4694389!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x47beb88e503514a!2sColegio+Santa+Teresita!5e0!3m2!1ses-419!2sco!4v1556064931229!5m2!1ses-419!2sco" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
        </div>
      </div>
    </div>

    <footer class="container-fluid" style="background-color: #344570; color: white;">
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
    
    <script src="{{ asset('assets/pagina_web/funciones.js') }}"></script>
        
    <script src="{{ asset('assets/pagina_web/jquery.smooth-scroll.min.js') }}"></script>

    <script>
      $(document).ready( function () {
          // Click para generar la consulta
        $('#submit').click(function(event){
          event.preventDefault();
          $('#resultado_consulta').html( '' );

          //alert( $('#acepto_terminos').attr('checked') );


          if ( validar_requeridos() ) {
            $('#div_cargando').show();

            // Preparar datos de los controles para enviar formulario
            var form_contacto = $('#form_contacto');
            var url = form_contacto.attr('action');
            var datos = form_contacto.serialize();
            // Enviar formulario de ingreso de productos vía POST
            $.post(url,datos,function(respuesta){
              $('#div_cargando').hide();
              $('#resultado_consulta').html(respuesta);
              $('#nombre').val('');
              $('#email').val('');
              $('#telefono').val('');
              $('#ciudad').val('');
              $('#comentarios').val('');
            });
          }else{
            alert("Debe ingresar todos los datos.");
          }
        });

        function validar_requeridos(){
          if( $('#nombre').val()=='' || $('#email').val()=='' || $('#telefono').val()=='' || $('#ciudad').val()=='' || $('#comentarios').val()=='' )
          {
            var valida = false;
          }else{
            var valida = true;
          }
          return valida;
        }

      });
    </script>

    </div> <!-- End - Contenido -->
  </body>
</html>