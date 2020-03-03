<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ $pagina->descripcion }} </title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="{{asset( $pagina->favicon )}}"/>
    <!-- Font Awesome -->
    <link href="{{asset('assets/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <!-- Slick slider -->
    <link href="{{asset('assets/css/slick.css')}}" rel="stylesheet">
    <!-- Gallery Lightbox -->
    <link href="{{asset('assets/css/magnific-popup.css')}}" rel="stylesheet">
    <!-- Skills Circle CSS  -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/circlebars@1.0.3/dist/circle.css">

      <!-- Main Style -->
      <link href="{{asset('assets/style.css')}}" rel="stylesheet">

      <!-- Fonts -->

      <!-- Google Fonts Raleway -->
      <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,400i,500,500i,600,700" rel="stylesheet">
      <!-- Google Fonts Open sans -->
      <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,800" rel="stylesheet">

      <link href="{{asset('css/animate.min.css')}}" rel="stylesheet">
      <link href="{{asset('css/owl.carousel.css')}}" rel="stylesheet">
      <link href="{{asset('css/owl.transitions.css')}}" rel="stylesheet">
      <link href="{{asset('css/prettyPhoto.css')}}" rel="stylesheet">
      <link href="{{asset('css/main.css')}}" rel="stylesheet">
      <link href="{{asset('css/responsive.css')}}" rel="stylesheet">
      <!--[if lt IE 9]>
    <script src="{{asset('js/html5shiv.js')}}"></script>
    <script src="{{asset('js/respond.min.js')}}"></script>
    <![endif]-->
      <link rel="shortcut icon" href="{{asset('images/ico/favicon.ico')}}">
      <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{asset('images/ico/apple-touch-icon-144-precomposed.png')}}">
      <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{asset('images/ico/apple-touch-icon-114-precomposed.png')}}">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{asset('images/ico/apple-touch-icon-72-precomposed.png')}}">
      <link rel="apple-touch-icon-precomposed" href="{{asset('images/ico/apple-touch-icon-57-precomposed.png')}}">
      <style type="text/css">
            .article-ls {
                  border: 1px solid;
                  border-color: #3d6983;
                  width: 100%;
                  border-radius: 10px;
                  -webkit-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
                  -moz-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
                  box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
            }

            .article-ls:focus {
                  border-color: #9400d3;
            }

            .pagination {
                  display: inline-block;
                  padding-left: 0;
                  margin: 10px 0;
                  border-radius: 4px;
            }

            .pagination>li {
                  display: inline;
            }

            .pagination>li>a,
            .pagination>li>span {
                  position: relative;
                  float: left;
                  padding: 6px 12px;
                  margin-left: -1px;
                  line-height: 1.428571429;
                  color: #428bca;
                  text-decoration: none;
                  background-color: #fff;
                  border: 1px solid #ddd;
            }

            .pagination>li:first-child>a,
            .pagination>li:first-child>span {
                  margin-left: 0;
                  border-top-left-radius: 4px;
                  border-bottom-left-radius: 4px;
            }

            .pagination>li:last-child>a,
            .pagination>li:last-child>span {
                  border-top-right-radius: 4px;
                  border-bottom-right-radius: 4px;
            }

            .pagination>li>a:hover,
            .pagination>li>span:hover,
            .pagination>li>a:focus,
            .pagination>li>span:focus {
                  color: #2a6496;
                  background-color: #eee;
                  border-color: #ddd;
            }

            .pagination>.active>a,
            .pagination>.active>span,
            .pagination>.active>a:hover,
            .pagination>.active>span:hover,
            .pagination>.active>a:focus,
            .pagination>.active>span:focus {
                  z-index: 2;
                  color: #fff;
                  cursor: default;
                  background-color: #428bca;
                  border-color: #428bca;
            }

            .pagination>.disabled>span,
            .pagination>.disabled>span:hover,
            .pagination>.disabled>span:focus,
            .pagination>.disabled>a,
            .pagination>.disabled>a:hover,
            .pagination>.disabled>a:focus {
                  color: #999;
                  cursor: not-allowed;
                  background-color: #fff;
                  border-color: #ddd;
            }
      </style>
</head>

<body style="padding:0;">

<main>

    @foreach($view as $item)
        {!! $item !!}
    @endforeach

    <div class="footerarea" style="background-color: black;">
        <div class="container" >
            <div class="row">
                <div class="col-md-9 col-sm-12 d-flex flex-wrap">
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >ACCESO RÁPIDO</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                <li id="" class="" style="list-style: none; margin-top: 10px;" ><a style="color: white; font-size: 14px" href="">Grados Promoción 2019</a></li>
                                <li id="" class="" style="list-style: none; margin-top: 10px;"><a style="color: white;  font-size: 14px" href="">Zona Norte galardonada por el CPV</a></li>
                                <li id="" class="" style="list-style: none; margin-top: 10px;"><a style="color: white;  font-size: 14px" href="">Premiación La Gran Noche de los Mejores</a></li>
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >ÚLTIMOS EVENTOS</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                <li id="" class="" style="list-style: none;" ><a style="color: white; font-size: 14px" href="">Grados Promoción 2019</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Zona Norte galardonada por el CPV</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Premiación La Gran Noche de los Mejores</a></li>
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >CONTÁCTENOS</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                <li id="" class="" style="list-style: none;" ><a style="color: white; font-size: 14px" href="">Grados Promoción 2019</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Zona Norte galardonada por el CPV</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Premiación La Gran Noche de los Mejores</a></li>
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >HACEMOS PARTE DE:</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                <li id="" class="" style="list-style: none;" ><a style="color: white; font-size: 14px" href="">Grados Promoción 2019</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Zona Norte galardonada por el CPV</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Premiación La Gran Noche de los Mejores</a></li>
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >SÍGUENOS</h5>
                        <aside class="d-flex flex-wrap">
                            @foreach($redes as $red)
                                <a href="{{$red->enlace}}" target="_blank" style=" color: white; border-radius: 50%; font-size: 28px; margin-right: 20px;"><i class="fa fa-{{$red->icono}}"></i></a>
                            @endforeach
                        </aside>
                    </div><!--end .widget-column-2-->
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >SÍGUENOS</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                <li id="" class="" style="list-style: none;" ><a style="color: white; font-size: 14px" href="">Grados Promoción 2019</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Zona Norte galardonada por el CPV</a></li>
                                <li id="" class="" style="list-style: none;"><a style="color: white;  font-size: 14px" href="">Premiación La Gran Noche de los Mejores</a></li>
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                </div>
                <div class="contenido col-md-3 col-sm-12" style="margin: 20px 0;">
                    <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;" >ENCUENTRANOS</h5>
                    <aside class="">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7846.117142011713!2d-73.27527537090697!3d10.496049145296741!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e8ab8265d25f41b%3A0x9e1b4c8829b528ef!2sCorporaci%C3%B3n+Gimnasio+del+Norte!5e0!3m2!1ses-419!2sco!4v1560770872100!5m2!1ses-419!2sco" width="300" height="450" frameborder="0" style="border:0" allowfullscreen=""></iframe>
                    </aside>
                </div><!--end .widget-column-2-->
            </div>
        </div><!--end .container-->
    </div>

</main>

            <!-- End main content -->

            <!-- JavaScript -->
            <!-- jQuery first, then Popper.js, then Bootstrap JS -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
            <!-- Slick slider -->
            <script type="text/javascript" src="{{asset('assets/web/js/slick.min.js')}}"></script>
            <!-- Progress Bar -->
            <script src="https://unpkg.com/circlebars@1.0.3/dist/circle.js"></script>

            <!-- Gallery Lightbox -->
            <script type="text/javascript" src="{{asset('assets/web/js/jquery.magnific-popup.min.js')}}"></script>

            <!-- Ajax contact form  -->
            <script type="text/javascript" src="{{asset('assets/web/js/app.js')}}"></script>

            <script src="{{asset('js/jquery.js')}}"></script>
            <script src="{{asset('js/owl.carousel.min.js')}}"></script>
            <script src="{{asset('js/mousescroll.js')}}"></script>
            <script src="{{asset('js/smoothscroll.js')}}"></script>
            <script src="{{asset('js/jquery.prettyPhoto.js')}}"></script>
            <script src="{{asset('js/jquery.isotope.min.js')}}"></script>
            <script src="{{asset('js/jquery.inview.min.js')}}"></script>
            <script src="{{asset('js/wow.min.js')}}"></script>
            <script src="{{asset('js/main.js')}}"></script>

</body>

</html>