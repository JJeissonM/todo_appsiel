<!DOCTYPE html>
<html lang="es">
  <head>

    <title>{{ $pagina->descripcion }}</title>
    
    <link rel="shortcut icon" href="{{ $pagina->get_url_favicon() }}" type="image/x-icon">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Estilos Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <!-- Add icon library -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- JavaScript Bootstrap And Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Merienda+One&display=swap" rel="stylesheet">

    <!-- ESTILOS DE MÓDULOS -->
    

    @if($pagina->codigo_google_analitics != '')
      <!-- Global site tag (gtag.js) - Google Analytics -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ $pagina->codigo_google_analitics }}"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $pagina->codigo_google_analitics }}');
      </script>
    @endif


    <!-- MEJORA: permitir agregar scripts desde el CRUD del modelo Pagina -->
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <style type="text/css">

      body{
        font-family: 'Merienda One', cursive;
      }

      .seccion_padre {
        /*border: dashed 1px red;
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        margin-bottom: 5px;
        */
      }

      .seccion_hija {
        /*border: solid 1px black;
        width: 100%;
        display: inline-block;
        padding: 10px;
        */
      }

      .modulo {
        width: 100%;
        border: solid 2px green;
        display: inline-block;
        padding: 10px;
        /**/
      }

      span.titulo_seccion {
        font-size: 1.5em;
        display: block;
      }

      span.titulo_modulo {
        font-size: 1.2em;
        display: block;
      }

      .search {
        width: 100%;
        margin-top: 50px;
        box-sizing: border-box;
        border: 2px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
        background-color: white;
        background-image: url( {{ asset( "assets/img/searching-2339723_1920.png" ) }} );
        background-size: 30px;
        background-position: 10px 6px; 
        background-repeat: no-repeat;
        padding: 12px 20px 12px 40px;
        -webkit-transition: width 0.4s ease-in-out;
        transition: width 0.4s ease-in-out;
      }



        .fa {
          /**/
          padding: 5px;
          width: 40px;
          text-align: center;
          text-decoration: none;
          border-radius: 10%;
          font-size: 24px;
        }

        .fa:hover {
            opacity: 0.7;
        }

        /*.fa-whatsapp {
          background: #3dbc28;
          color: white;
        }*/

        .fa-facebook {
          background: #3B5998;
          color: white;
        }

        .fa-youtube {
          background: #bb0000;
          color: white;
        }

        .fa-instagram {
          background: #9b39a6;
          color: white;
        }

        .img_categoria{
          display: table;
          margin-bottom: 10px;
        }


      .layer_overlay {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: rgba(0,0,0,.6);
        padding: 75px 0 0 0;
        margin: 0 15px 10px 15px;
        /*cursor: default;*/
        z-index: 110;
        color: white;
      }

      .layer_overlay:hover {
          background: rgba(0,0,0,.3);
      }
      
      .layer_overlay .td {
        /*padding: 30px 0 100px;
        vertical-align: middle;*/
        text-align: center;
    }

    .pie_pagina{
      font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
      font-size: 0.9em;
      background-color: #000;
      color: white;
    }

    .pie_pagina a{
      text-decoration: none;
      color: white;
    }

    .modal-footer{
      display: none;
    }
    </style>

  </head>
  <body id="myPage" data-spy="scroll" data-target=".navbar" data-offset="60">


    <div class="container-fluid">
      <div class="row">

          <div class="col-md-4">
            <img src="{{ asset( config('configuracion.url_instancia_cliente')."/storage/app/pagina_web/".$pagina->logo ) }}" class="img-responsive" style="margin-top: 25px;">
          </div>
          
          <div class="col-md-4">
            <input class="search" type="text" name="search" placeholder="Aquí puedes buscar lo que necesites...">
            <br>
            <a href="#"> <i class="fa fa-file-text-o"></i> Pedir cotización </a>
          </div>
          
          <div class="col-md-4">
            <i class="fa fa-map-marker" style="color:red"></i> Cr 15 20B 41 LC i-26 Interior Mercado Nuevo;  Valledupar, Cesar - Colombia
            <br>
            
            <button type="button" style="background-color: transparent; color: #3394FF; border: none;" id="como_llegar"  data-toggle="modal" data-target="#myModal" ><i class="fa fa-map-o"></i> ¿Cómo llegar? </button>
            @include('components.design.ventana_modal',['titulo'=>'Ubicación AVIPOULET','texto_mensaje'=>'','contenido_modal'=>'<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15693.78596773178!2d-73.2472329!3d10.4654247!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xd8722ec2f3c785b8!2sAVIPOULET!5e0!3m2!1ses-419!2sco!4v1569893235289!5m2!1ses-419!2sco" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>'])
            <br>

            <i class="fa fa-mobile"></i> +57 318 7899708 &nbsp;&nbsp; <i class="fa fa-phone"></i> +57 035 574 8405
            <br>

            <a href="https://api.whatsapp.com/send?phone=573187899708" target="_blank" class="fa fa-whatsapp" title="+57 318 7899708"> </a>
            <a href="https://facebook.com/" target="_blank" class="fa fa-facebook"> </a>
            <a href="https://instagram.com/" target="_blank" class="fa fa-instagram"> </a>

          </div>
      </div>
    </div>

      <hr>

      <?php
        $categorias = (object)[
                                (object)[ 'imagen' => 'chicken-soup-1346310_1920.jpg', 'descripcion' => 'Pollo', 'enlace' => '#'],
                                (object)[ 'imagen' => 'pig-2103502_1920.jpg', 'descripcion' => 'Cerdo', 'enlace' => '#'],
                                (object)[ 'imagen' => 'fish-2230852_1920.jpg', 'descripcion' => 'Pescado', 'enlace' => '#'],
                                (object)[ 'imagen' => 'raw-3606079_1920.jpg', 'descripcion' => 'Lácteos', 'enlace' => '#'],
                                (object)[ 'imagen' => 'cold-cuts-520728_1920.jpg', 'descripcion' => 'Carnes frías', 'enlace' => '#'],
                                (object)[ 'imagen' => 'la-fazenda.jpg', 'descripcion' => 'Productos La Fazenda', 'enlace' => '#'],
                                (object)[ 'imagen' => 'combos-comida.jpg', 'descripcion' => 'Combos de comida', 'enlace' => '#']
                                ];
      ?>

    <div class="container-fluid" style="background-color: #f7f7f9;">
      <h1 style="width: 100%; text-align: center;"> Descubre una experiencia agradable en cada producto</h1>

          <?php       
            $cant_cols=3;
            $i=0;
            //echo ( $i % $cant_cols );
          ?>
          @foreach ($categorias as $fila)

            @if($i % $cant_cols == 0)
               <!-- se ABRE una linea -->
               <div class="row">
            @endif
            
            <div class="col-md-{{(12/$cant_cols)}}">
              <a href="{{ $fila->enlace }}" class="img_categoria">
                <img src="{{ asset( "/assets/pagina_web/".$pagina->plantilla."/".$fila->imagen) }}" class="img-responsive">
                <div class="layer_overlay">
                  <div class="td">
                    <p style="font-size:34px;text-shadow:0 1px 10px rgba(0,0,0,.7)"> {{ $fila->descripcion }} </p>
                  </div>
                </div>
              </a>
            </div>

            <?php $i++; ?>

            @if($i % $cant_cols == 0)
              <!-- se CIERRA una linea -->
              </div>
            @endif
          @endforeach

    </div>
    </div>


    <div class="container-fluid" style="background-color: #f7f7f9;">
      <h1 style="width: 100%; text-align: center;"> <i class="fa fa-list-alt" style="font-size:36px;"></i> Recetas </h1>
    </div>
    @include('web.front_end.modulos.clientes.index2',['titulo' => '¿Necesitas inspiración en la cocina?' ])
    <div class="container-fluid" style="background-color: #f7f7f9;">
      <h4 style="width: 100%; text-align: center;"> <a href="#"> Ver todas... </a> </h4>
    </div>
    <br>

    <div class="container-fluid">      
      <div class="well">
          <div class="row">
            
            <div class="col-md-4">
              <img src="{{ asset( "assets/pagina_web/post-1168634_1920.png" ) }}" class="img-responsive thumbnail">  
            </div>

            <div class="col-md-8">
                <h2 style="text-align: center;">Suscríbete a nuestro boletín semanal</h2>
                <p>Recibe en tu correo TIPs, recetas, promociones, dietas, noticias saludables y toda la información que necesitas para mantenerte actualizado en las últimas novedades en salud y alimentación.</p>
                <form action="#" class="form-inline">
                    <div class="row">
                      <input type="text" placeholder="Nombre" name="name" required class="form-control col-sm-4">
                        <input type="text" placeholder="Correo electrónico" name="mail" required class="form-control col-sm-4">


                      <div class="checkbox col-sm-2">
                          <input type="checkbox" checked="checked" name="subscribe"> Acepto la <a href="#"> política de privacidad </a>
                      </div>

                      <input type="submit" value="Suscribirme" class="btn btn-success col-sm-2">
                    </div>
                </form>
            </div>

          </div>
            
      </div>
    </div>

    <footer class="container-fluid pie_pagina">
      <hr>
      <div class="row">
        <div class="col-sm-3">
          <p> <u>  Nuestra empresa </u> </p>
          <a href="#"> A cerca de </a>
          <br>
          <a href="#"> Equipo de trabajo </a>
          <br>
          <a href="#"> Trabaje con nosotros </a>
          <br>
          <a href="#"> AVIPOULET: Socialmente responsable </a>
          <br>
          <a href="#"> Contactenos </a>
        </div>

        <div class="col-sm-6">
          <p style="text-align: center;">© {{ date('Y') }} AVIPOULET DE LA COSTA </p>
        </div>

        <div class="col-sm-3">
          <p> <u>Documentos legales y Recursos</u> </p>
          <a href="#"> Política de tratamiento de datos </a>
          <br>
          <a href="#"> Políticas de calidad </a>
          <br>
          <a href="#"> Fichas técnicas </a>
          <br>
          <a href="#"> Nuestros proveedores </a>
        </div>
      </div>

      <hr>
    </footer>
  </body>
</html>