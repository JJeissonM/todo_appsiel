<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="keywords" content="{{ $pagina->meta_keywords }}">

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

    <!--Main Style-->
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

    <link rel="apple-touch-icon-precomposed" sizes="144x144"
          href="{{asset('images/ico/apple-touch-icon-144-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
          href="{{asset('images/ico/apple-touch-icon-114-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="{{asset('images/ico/apple-touch-icon-72-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" href="{{asset('images/ico/apple-touch-icon-57-precomposed.png')}}">

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


    @foreach($links as $key => $value)
        {!! $value !!}
    @endforeach

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

        .pagination > li {
            display: inline;
        }

        .pagination > li > a,
        .pagination > li > span {
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

        .pagination > li:first-child > a,
        .pagination > li:first-child > span {
            margin-left: 0;
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }

        .pagination > li:last-child > a,
        .pagination > li:last-child > span {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .pagination > li > a:hover,
        .pagination > li > span:hover,
        .pagination > li > a:focus,
        .pagination > li > span:focus {
            color: #2a6496;
            background-color: #eee;
            border-color: #ddd;
        }

        .pagination > .active > a,
        .pagination > .active > span,
        .pagination > .active > a:hover,
        .pagination > .active > span:hover,
        .pagination > .active > a:focus,
        .pagination > .active > span:focus {
            z-index: 2;
            color: #fff;
            cursor: default;
            background-color: #428bca;
            border-color: #428bca;
        }

        .pagination > .disabled > span,
        .pagination > .disabled > span:hover,
        .pagination > .disabled > span:focus,
        .pagination > .disabled > a,
        .pagination > .disabled > a:hover,
        .pagination > .disabled > a:focus {
            color: #999;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #ddd;
        }

        .section-header .section-title:after {
            background-color: {{$configuracion->color_primario}}  !important;
        }

        .btn.btn-primary {
            background: {{$configuracion->color_primario}}  !important;
            border-color: {{$configuracion->color_terciario}}  !important;
        }

        .media.service-box .pull-left > i {
            color: {{$configuracion->color_primario}}  !important;
        }

        .media.service-box .pull-left > i:after {
            background-color: {{$configuracion->color_primario}}  !important;
        }

        .pagination > li > a, .pagination > li > span {
            color: {{$configuracion->color_primario}}  !important;
        }

        .pagination > .active > a,
        .pagination > .active > span,
        .pagination > .active > a:hover,
        .pagination > .active > span:hover,
        .pagination > .active > a:focus,
        .pagination > .active > span:focus {
            color: {{$configuracion->color_segundario}}  !important;
            cursor: default;
            background-color: {{$configuracion->color_primario}};
            border-color: {{$configuracion->color_primario}};
        }

        .column-title:after {
            border-bottom: 1px solid {{$configuracion->color_terciario}}  !important;
        }

        #formulario_pqr .control-label {
            display: none;
            background: #ddd !important;
            width: 100% !important;
            margin-bottom: 5px !important;
        }


            #navegacion {
                position: fixed;
                z-index: 999;
                padding-top: 50px;
                width: 100%;
            }
            
            #navegacion > header {
                color: #ffffff !important;
                background: rgba(0, 0, 0, 0.54) !important;
            }

            .mu-navbar-nav > li > a {
                color: white !important;
            }

            .carousel-content {
                position: relative;
                z-index: 9999999;
            }/**/

            #navegacion > header.sticky {
              position: fixed;
              z-index: 99999;
              top: 0;
              width: 100%;
              background: #396b8e !important;
            }

            .sticky + .content {
              padding-top: 102px;
            }

        @foreach($estilos as $key => $value)
            {{ $value }}
        @endforeach

    </style>
</head>

<body style="padding:0;">

<main id="contenedor_principal">

    <!-- <div class="top-container">
        HELLO
    </div> -->

    @foreach($view as $item)
        {!! $item !!}
    @endforeach

</main>

<!-- End main content -->

<!-- JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
        integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1"
        crossorigin="anonymous"></script>
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
<script src="{{asset('js/jquery.jscroll.min.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.client-single').on('click', function (event) {
            event.preventDefault();

            var active = $(this).hasClass('active');

            var parent = $(this).parents('.testi-wrap');

            if (!active) {
                var activeBlock = parent.find('.client-single.active');

                var currentPos = $(this).attr('data-position');

                var newPos = activeBlock.attr('data-position');

                activeBlock.removeClass('active').removeClass(newPos).addClass('inactive').addClass(currentPos);
                activeBlock.attr('data-position', currentPos);

                $(this).addClass('active').removeClass('inactive').removeClass(currentPos).addClass(newPos);
                $(this).attr('data-position', newPos);

            }
        });

    });
</script>

<script>
    window.onscroll = function() {myFunction()};

    var header = document.getElementById("myHeader");
    var sticky = header.offsetTop;

    function myFunction() {
      if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
      } else {
        header.classList.remove("sticky");
      }
    }

</script>


        @foreach($scripts as $key => $value)
            {!! $value !!}
        @endforeach
        
@yield('script')
</body>

</html>