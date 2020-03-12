<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        Web - APPSIEL
    </title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="{{asset('assets/images/favicon.ico')}}" />
    <!-- Font Awesome -->
    <link href="{{asset('assets/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <!-- Slick slider -->
    <link href="{{asset('assets/css/slick.css')}}" rel="stylesheet">
    <!-- Gallery Lightbox -->
    <link href="{{asset('assets/css/magnific-popup.css')}}" rel="stylesheet">
    <!-- Skills Circle CSS  -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/circlebars@1.0.3/dist/circle.css">

    <!-- Main Style -->
    <link href="{{asset('assets/style.css')}}" rel="stylesheet">
    <link href="{{asset('css/main.css')}}" rel="stylesheet">

    <!-- Fonts -->

    <!-- Google Fonts Raleway -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,400i,500,500i,600,700" rel="stylesheet">
    <!-- Google Fonts Open sans -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,800" rel="stylesheet">

    <style type="text/css">
        .icon {
            cursor: pointer;
            float: left;
            text-align: center;
            font-size: 45px;
            color: #3d6983;
        }

        .icon>p {
            font-size: 14px;
        }

        .icon:hover {
            font-size: 40px;
            color: #9400d3 !important;
        }

        .buscar {
            margin-top: 40px;
            margin-bottom: 40px;
            height: 40px;
            padding: 15px;
            border: 2px solid;
            border-color: #3d6983;
            width: 70%;
            border-radius: 10px;
            -webkit-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
            -moz-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
            box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
        }

        .buscar:focus {
            border-color: #9400d3;
        }
    </style>

    @yield('style')

</head>

<body>

    <!-- END SCROLL TOP BUTTON -->

    <!-- Start main content -->
    <main>

        <?php

        use App\Core\Menu;
        use Illuminate\Support\Facades\Input;

        $id = Input::get('id');
        $menus = Menu::menus($id);
        ?>

        @if (!Auth::guest())

        <nav class="navbar navbar-inverse navbar-static-top" style="background-color: #3d6983;">
            <div class="container-fluid">

                <nav class="navbar navbar-expand-lg navbar-light mu-navbar ">
                    <!-- Text based logo -->
                    <a class="navbar-brand" href="{{ url('/inicio') }}" style="height: 60px; padding-top: 0px;">
                        <img src="{{ asset('assets/img/logo_appsiel.png') }}" height="60px" width="100px">
                    </a>
                    <!-- image based logo -->
                    <!-- <a class="navbar-brand mu-logo" href="index.html"><img src="assets/images/logo.png" alt="logo"></a> -->
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="fa fa-bars"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="margin-left: 150px;">
                        <ul class="navbar-nav mr-auto mu-navbar-nav">
                            @foreach ($menus as $key => $item)
                            @if ($item['parent'] != 0)
                            @break
                            @endif
                            @include('web.templates.menu', ['item' => $item])
                            @endforeach
                            <li class="nav-item">
                                <a href="{{url('pagina_web/icons/view?id='.$id)}}"><i class="fa fa-exclamation-circle"></i> Íconos</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{url('pagina_web/nube/view?id='.$id)}}"><i class="fa fa-cloud"></i> Nube</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </nav>
        @endif

        {{ Form::bsMigaPan($miga_pan) }}

        @include('web.templates.messages')

        <div class="col-md-12">
            <div class="alert alert-success" role="alert">
                <h3 style="text-align: center;">Listado de íconos disponibles para configurar su sitio web!</h3>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <center><input class="buscar" type="text" id="buscar" placeholder="Buscar íconos..." onkeyup="buscar()" /></center>
                </div>
            </div>
            <div class="col-md-12" id="txt">
                @foreach($iconos as $i)
                <div class="col-md-3 icon">
                    <i class="fa fa-{{$i->icono}}"></i>
                    <p id="icono">{{$i->icono}}</p>
                </div>
                @endforeach
            </div>
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


    <!-- About us Skills Circle progress  -->

    @yield('script')

    <script type="text/javascript">
        var iconos = <?php echo json_encode($iconos); ?>;


        function buscar() {
            $("#txt").html("");
            var texto = $("#buscar").val();
            var nuevoArray = [];
            iconos.forEach(function(i) {
                if (i.icono.indexOf(texto) != -1) {
                    nuevoArray.push(i);
                }
            });
            arrayDraw(nuevoArray);
        }

        function arrayDraw(array) {
            var html = "";
            array.forEach(function(i) {
                html = html + "<div class='col-md-3 icon'><i class='fa fa-" + i.icono + "'></i>" +
                    "<p id='icono'>" + i.icono + "</p></div>";
            });
            $("#txt").html(html);
        }
    </script>

</body>

</html>