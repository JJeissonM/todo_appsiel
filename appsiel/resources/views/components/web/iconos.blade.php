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
                <div class="col-md-3 icon" id="{{$i->icono}}" onclick="seticon(this.id)">
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

    function seticon(icono) {
        $("#iconotxt").val(icono);
        $("#exampleModal").modal('hide');
        $("#exampleModal").removeClass('modal-open');
        $('.modal-backdrop').remove();

    }
    function arrayDraw(array) {
        var html = "";
        array.forEach(function(i) {
            html = html + "<div class='col-md-3 icon'id='"+i.icono+"' onclick='seticon(this.id)'><i class='fa fa-" + i.icono + "'></i>" +
                "<p id='icono'>" + i.icono + "</p></div>";
        });
        $("#txt").html(html);
    }
</script>

</body>

</html>