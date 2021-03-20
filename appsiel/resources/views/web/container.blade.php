<!DOCTYPE html>
<html lang="es">

<?php
    $principal = App\web\Pagina::where('pagina_inicio', true)->get()->first();
?>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>{{ $principal->descripcion }}</title>
    <!-- core CSS -->
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="{{asset( $principal->favicon )}}" />
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

    <!-- Google Fonts Raleway -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,400i,500,500i,600,700" rel="stylesheet">
    <!-- Google Fonts Open sans -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,800" rel="stylesheet">

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
    <style>
        @media (min-width: 768px) {
            .carousel-multi-item-2 .col-md-3 {
                float: left;
                width: 25%;
                max-width: 100%;
            }
        }

        .card {
            font-weight: 400;
            border: 0;
            box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);
        }

        .efecto:hover {
            transform: scale(1.1);
        }

        .carousel-multi-item-2 .col-md-3 {
            float: left;
            width: 25%;
            max-width: 100%;
        }

        .carousel-multi-item-2 .card img {
            border-radius: 2px;
        }

        .mdb-lightbox [class*="col-"] {
            padding: .5rem;
        }

        .mdb-lightbox figure {
            float: left;
            margin: 0;
        }

        .mdb-lightbox figure img {
            cursor: zoom-in;
            border-radius: 0;
            -webkit-transition: opacity .4s;
            transition: opacity .4s;
        }

        .img-fluid,
        .modal-dialog.cascading-modal.modal-avatar .modal-header,
        .video-fluid {
            max-width: 100%;
            height: auto;
        }
    </style>
    <!--[if lt IE 9]>
    <script src="{{asset('js/html5shiv.js')}}"></script>
    <script src="{{asset('js/respond.min.js')}}"></script>
    -->
    {{--    <link href="{{asset('web/css/bootstrap.min.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/font-awesome.min.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/animate.min.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/owl.carousel.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/owl.transitions.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/prettyPhoto.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/main.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('web/css/responsive.css')}}" rel="stylesheet">--}}
    <!-- Datatables -->
    {{--    <link href="{{asset('plugins/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">--}}
    {{--    <link href="{{asset('plugins/datatables.net-buttons-bs/css/buttons.bootstrap.min.css')}}"
    rel="stylesheet">--}}
    {{--    <link href="{{asset('plugins/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css')}}"
    rel="stylesheet">--}}
    {{--    <link href="{{asset('plugins/datatables.net-responsive-bs/css/responsive.bootstrap.min.css')}}"
    rel="stylesheet">--}}
    {{--    <link href="{{asset('plugins/datatables.net-scroller-bs/css/scroller.bootstrap.min.css')}}"
    rel="stylesheet">--}}
    {{--    <!--[if lt IE 9]>--}}
    {{--    <script src="js/html5shiv.js"></script>--}}
    {{--    <script src="js/respond.min.js"></script>--}}
    {{--    <![endif]-->--}}
    {{--    <link rel="shortcut icon" href="{{asset('web/images/ico/favicon.ico')}}">--}}
    {{--    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{asset('web/images/ico/apple-touch-icon-144-precomposed.png')}}">--}}
    {{--    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{asset('web/images/ico/apple-touch-icon-114-precomposed.png')}}">--}}
    {{--    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{asset('web/images/ico/apple-touch-icon-72-precomposed.png')}}">--}}
    {{--    <link rel="apple-touch-icon-precomposed" href="{{asset('web/images/ico/apple-touch-icon-57-precomposed.png')}}">--}}
</head>
<!--/head-->

<body id="home" class="homepage">

    @if($nav != null)
    {!! Form::navegacion($nav) !!}
    @endif

    <section>
        <div class="container">
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown">{{$title}}</h2>
                <p class="text-center wow fadeInDown">{!!$slogan1!!}</p>
                <p class="text-center wow fadeInDown">{!!$slogan2!!}</p>
            </div>
            <div class="row" style="height: 70vh; overflow-y:scroll;">
                <div class="col-sm-12 wow fadeInLeft" style="margin-top: 8px;">
                    {!!$data!!}
                </div>
            </div>
        </div>
    </section>


    <footer id="footer" class="fixed-bottom" style="background-color: {{$nav->background}}">
        <div class="container">
            <div class="row">

                <div class="col-sm-6" style="color: {{ $nav->color }}">
                    @if($footer != null)
                    &copy; {{$footer->texto.' '.$footer->copyright}}
                    @endif
                </div>

                <div class="col-sm-6">
                    <ul class="social-icons">
                        @foreach($redes as $red)
                        <li style="list-style: none; margin-right: 10px;">
                            <div
                                style="border-radius: 50%; border: 1px solid #ffffff; height: 45px; width: 45px; text-align: center;">
                                <a href="{{$red->enlace}}" style="color:{{$nav->color}}; font-size: 30px;"
                                    target="_blank"><i class="fa fa-{{$red->icono}}"></i></a>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </footer>
    <!--/#footer-->
    <!-- End main content -->

    <!-- JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="{{asset('js/jquery.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
        integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous">
    </script>
    <!-- Slick slider -->
    <script type="text/javascript" src="{{asset('assets/web/js/slick.min.js')}}"></script>
    <!-- Progress Bar -->
    <script src="https://unpkg.com/circlebars@1.0.3/dist/circle.js"></script>

    <!-- Gallery Lightbox -->
    <script type="text/javascript" src="{{asset('assets/web/js/jquery.magnific-popup.min.js')}}"></script>

    <!-- Ajax contact form  -->
    <script type="text/javascript" src="{{asset('assets/web/js/app.js')}}"></script>


    <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
    <script src="{{asset('js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('js/mousescroll.js')}}"></script>
    <script src="{{asset('js/smoothscroll.js')}}"></script>
    <script src="{{asset('js/jquery.prettyPhoto.js')}}"></script>
    <script src="{{asset('js/jquery.isotope.min.js')}}"></script>
    <script src="{{asset('js/jquery.inview.min.js')}}"></script>
    <script src="{{asset('js/wow.min.js')}}"></script>
    <script src="{{asset('js/main.js')}}"></script>
    <!-- DataTable -->
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>


    <script src="https://cdn.ckeditor.com/4.16.0/standard-all/ckeditor.js"></script>
    <script type="text/javascript">
        $(function () {
        // console.log('mdb-addons/mdb-lightbox-ui.html');
        console.log($("#mdb-lightbox-ui").load($("#mdb-lightbox")));
        $("#mdb-lightbox-ui").load($("#mdb-lightbox").innerHTML);

    });
    </script>
    <!-- About us Skills Circle progress  -->
    {{--<script src="{{asset('web/js/jquery.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/bootstrap.min.js')}}"></script>--}}
    {{--<script src="http://maps.google.com/maps/api/js?sensor=true"></script>--}}
    {{--<script src="{{asset('web/js/owl.carousel.min.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/mousescroll.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/smoothscroll.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/jquery.prettyPhoto.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/jquery.isotope.min.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/jquery.inview.min.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/wow.min.js')}}"></script>--}}
    {{--<script src="{{asset('web/js/main.js')}}"></script>--}}
    {{--<!-- Datatables -->--}}
    {{--<script src="{{ asset('plugins/datatables.net/js/jquery.dataTables.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-buttons-bs/js/buttons.bootstrap.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-buttons/js/buttons.flash.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-buttons/js/buttons.print.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-keytable/js/dataTables.keyTable.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-responsive-bs/js/responsive.bootstrap.js')}}"></script>--}}
    {{--<script src="{{ asset('plugins/datatables.net-scroller/js/dataTables.scroller.min.js')}}"></script>--}}
    {{--<!-- Custom Theme Scripts             -->--}}
    {{--<script src="{{ asset('plugins/build/js/custom.min.js')}}"></script>--}}
</body>

</html>