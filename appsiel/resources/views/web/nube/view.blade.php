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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Slick slider -->
    <link href="{{asset('assets/css/slick.css')}}" rel="stylesheet">
    <!-- Gallery Lightbox -->
    <link href="{{asset('assets/css/magnific-popup.css')}}" rel="stylesheet">
    <!-- Skills Circle CSS  -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/circlebars@1.0.3/dist/circle.css">

    <!-- Main Style -->
    <link href="{{asset('assets/style.css')}}" rel="stylesheet">
    <link href="{{asset('css/main.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">

    <!-- Fonts -->

    <!-- Google Fonts Raleway -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,400i,500,500i,600,700" rel="stylesheet">
    <!-- Google Fonts Open sans -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,800" rel="stylesheet">

    <style type="text/css">
        .icon {
            cursor: pointer;
            text-align: center;
            font-size: 60px;
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

        .list-group-item {
            background-color: transparent;
            font-size: 16px;
        }

        .list-group-item:hover {
            background-color: #3d6983;
            color: white;
            cursor: pointer;
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

        <nav class="navbar navbar-expand-lg navbar-light navbar-inverse navbar-static-top" style="background-color: #3d6983;">
            <!-- Text based logo -->
            <a class="navbar-brand" href="{{ url('/inicio') }}" style="height: 60px; padding-top: 0px;">
                <img src="{{ asset('assets/img/logo_appsiel.png') }}" height="60px">
            </a>
            <!-- image based logo -->
            <!-- <a class="navbar-brand mu-logo" href="index.html"><img src="assets/images/logo.png" alt="logo"></a> -->
            <button class="navbar-toggler" type="button" data-toggle="collapse"
                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="fa fa-bars"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto mu-navbar-nav">
                    @foreach ($menus as $key => $item)
                    @if ($item['parent'] != 0)
                    @break
                    @endif
                    @include('web.templates.menu', ['item' => $item])
                    @endforeach
                    <!--<li class="nav-item">
                        <a href="{{url('pagina_web/icons/view?id='.$id)}}"><i
                                class="fa fa-exclamation-circle"></i> Íconos</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{url('pagina_web/nube/view?id='.$id)}}"><i class="fa fa-cloud"></i> Nube</a>
                    </li>-->
                </ul>
            </div>
        </nav>
        @endif

        {{ Form::bsMigaPan($miga_pan) }}

        @include('web.templates.messages')

        <div class="col-md-12 container-fluid">
            <div class="alert alert-success" role="alert">
                <h3 style="text-align: center;">Espacio de Almacenamiento en la Nube ({{$path}})</h3>
            </div>
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @if($prev!='NO') <a onclick="ir()" class="btn btn-primary" style="color: #FFFFFF; cursor: pointer;">REGRESAR UN NIVEL</a>@endif
                        <a class="btn btn-primary" style="color: #FFFFFF; cursor: pointer;" data-toggle="modal" data-target="#exampleModal">NUEVA CARPETA</a>
                        <a class="btn btn-primary" style="color: #FFFFFF; cursor: pointer;" data-toggle="modal" data-target="#exampleModal2">SUBIR ARCHIVOS</a>
                    </ol>
                </nav>
            </div>
            {!! Form::open(['route'=>'nube.list','method'=>'POST','id'=>'prev'])!!}
            <input type="hidden" name="prev" value="{{$path}}" />
            @if($prev=='NO')
            <input type="hidden" name="path" value="./nube/" />
            @else
            <input type="hidden" name="path" value="{{$prev}}" />
            @endif
            <input type="hidden" name="id" value="{{$id}}" />
            {!! Form::close() !!}
            <div class="col-md-12">
                <div class="form-group">
                    <center><input class="buscar" type="text" id="buscar" placeholder="Buscar en éste directorio..." onkeyup="buscar()" /></center>
                </div>
            </div>
            <div class="col-md-12 d-flex flex-wrap" id="txt" style="margin-bottom: 20px;">
                @if($files!=null)
                @foreach($files as $f)
                <div class="col-md-2" style="margin-top: 10px;" title="{{$f['file']}} ({{$f['tamanio']}})">
                    <a>
                        <center>
                            @if($f['type']=='FOLDER')
                            <i style="font-size: 60px; color: {{$f['color']}}; cursor: pointer;" class="fa fa-{{$f['icon']}}" onclick='ingresar(this.id)' id="{{$f['m']}}" data-toggle='tooltip' data-placement='top' title='Ingresar'></i>
                            @else
                            <i style="font-size: 60px; color: {{$f['color']}};" class="fa fa-{{$f['icon']}}"></i>
                            @endif
                        </center>
                    </a>
                    <center>
                        <p>{{$f['file']." (".$f['tamanio'].")"}}</p>
                        @if($f['type']!='FOLDER')
                        <a style="font-size: 12px; color: #45aed6; cursor: pointer;" id="{{url('').$f['path']}}" onclick='copiar(this.id)' data-toggle='tooltip' data-placement='top' title='Copiar Enlace'><i class='fa fa-clone'></i> Copiar Enlace</a> </br>
                        @else
                        <form method="POST" action="{{route('nube.list')}}" id="ingresar{{$f['m']}}">
                            <input type="hidden" name="prev" value="{{$prev}}" />
                            <input type="hidden" name="path" value="{{$f['path']}}/" />
                            <input type="hidden" name="id" value="{{$id}}" />
                            {{ csrf_field() }}
                        </form>
                        @endif
                        <form method="POST" action="{{route('nube.delete')}}" id="borrar_{{$f['m']}}">
                            <input type="hidden" name="prev" value="{{$prev}}" />
                            <input type="hidden" name="path" value="{{$path}}" />
                            <input type="hidden" name="id" value="{{$id}}" />
                            <input type="hidden" name="file_id" value="{{$f['path']}}" />
                            <input type="hidden" id="type" name="type" value="{{$f['type']}}" />
                            {{ csrf_field() }}
                        </form>
                        <a onclick="borrar(this.id)" id="{{$f['m']}}" style="font-size: 12px; color: #ff0000; cursor: pointer;" data-toggle="tooltip" data-placement="top" title="Eliminar"><i class="fa fa-remove"></i> Eliminar</a>
                    </center>
                </div>
                @endforeach
                @else
                <h4>Directorio vacío!</h4>
                @endif
            </div>
        </div>

    </main>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Crear Nueva Carpeta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{route('nube.nueva')}}" id="nueva">
                        <input type="hidden" name="prev" value="{{$prev}}" />
                        <input type="hidden" name="path" value="{{$path}}" />
                        <input type="hidden" name="id" value="{{$id}}" />
                        <div class="form-group">
                            <label class="control-label">Nombre de la carpeta</label>
                            <span data-toggle="tooltip" title="Establece el nombre de la carpeta para tus archivos."> <i class="fa fa-question-circle"></i></span>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="nueva()">Guardar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Subir Archivos en esta Carpeta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{route('nube.upload')}}" id="upload" enctype="multipart/form-data">
                        <input type="hidden" name="prev" value="{{$prev}}" />
                        <input type="hidden" name="path" value="{{$path}}" />
                        <input type="hidden" name="id" value="{{$id}}" />
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Archivos</label>
                            <span data-toggle="tooltip" title="Establece los archivos que deseas subir."> <i class="fa fa-question-circle"></i></span>
                            {!! Form::file('archivo[]',['class'=>'form-control has-feedback-left','required'=>'required','multiple'=>'multiple']) !!}
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="upload()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- End main content -->

    <!-- JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.slim.min.js" integrity="sha512-yrjEr6HY46i3hW7imuklZtRM7q1wyNUz8smKl2130OHTFHGbXGpHN4nYxrIeu0uJprDsLhycYY+xQ368d9OiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Slick slider -->
    <script type="text/javascript" src="{{asset('assets/web/js/slick.min.js')}}"></script>
    <!-- Progress Bar -->
    <script src="https://unpkg.com/circlebars@1.0.3/dist/circle.js"></script>

    <!-- Gallery Lightbox -->
    <script type="text/javascript" src="{{asset('assets/web/js/jquery.magnific-popup.min.js')}}"></script>

    <!-- Ajax contact form  -->
    <script type="text/javascript" src="{{asset('assets/web/js/app.js')}}"></script>

    <script src="{{asset('js/sweetAlert2.min.js')}}"></script>

    <!-- About us Skills Circle progress  -->

    @yield('script')

    <script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
        var iconos = <?php echo json_encode($files); ?>;

        function buscar() {
            $("#txt").html("");
            var texto = $("#buscar").val();
            var nuevoArray = [];
            if (iconos != null) {
                iconos.forEach(function(i) {
                    if (i.file.indexOf(texto) != -1) {
                        nuevoArray.push(i);
                    }
                });
                arrayDraw(nuevoArray);
            } else {
                Swal.fire(
                    'Información',
                    'Directorio vacío',
                    'error'
                );
            }
        }

        function arrayDraw(array) {
            var html = "";
            array.forEach(function(i) {
                html = html + "<div class='col-md-2' style='margin-top: 10px;' title='" + i.file + " (" + i.tamanio + ")'>" +
                    "<a><center>";
                if (i.type == 'FOLDER') {
                    html = html + "<i style='font-size: 60px; color: " + i.color + "; cursor: pointer;' class='fa fa-" + i.icon + "' onclick='ingresar(this.id)' id='" + i.m + "' data-toggle='tooltip' data-placement='top' title='Ingresar'></i>";
                } else {
                    html = html + "<i style='font-size: 60px; color: " + i.color + ";' class='fa fa-" + i.icon + "'></i>";
                }
                html = html + "</center></a><center><p>" + i.file + " (" + i.tamanio + ")</p>";
                if (i.type != 'FOLDER') {
                    html = html + "<a style='font-size: 12px; color: #45aed6; cursor: pointer;' id='<?php echo url(''); ?>" + i.path + "' onclick='copiar(this.id)' data-toggle='tooltip' data-placement='top' title='Copiar Enlace'><i class='fa fa-clone'></i> Copiar Enlace</a> </br>";
                } else {
                    html = html + "<form method='POST' action='<?php echo route('nube.list'); ?>' id='ingresar" + i.m + "'>" +
                        "<input type='hidden' name='prev' value='<?php echo $prev; ?>' />" +
                        "<input type='hidden' name='path' value='" + i.path + "/' />" +
                        "<input type='hidden' name='id' value='<?php echo $id; ?>' />" +
                        "<?php echo $token; ?></form>";
                }
                html = html + "<form method='POST' action='<?php echo route('nube.delete'); ?>' id='borrar_" + i.m + "'>" +
                    "<input type='hidden' name='prev' value='<?php echo $prev; ?>' />" +
                    "<input type='hidden' name='path' value='<?php echo $path; ?>' />" +
                    "<input type='hidden' name='id' value='<?php echo $id; ?>' />" +
                    "<input type='hidden' name='file_id' value='" + i.path + "' />" +
                    "<input type='hidden' id='type' name='type' value='" + i.type + "' />" +
                    "<?php echo $token; ?></form>" +
                    "<a onclick='borrar(this.id)' id='" + i.m + "' style='font-size: 12px; color: #ff0000; cursor: pointer;' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fa fa-remove'></i> Eliminar</a>" +
                    "</center></div>";
            });
            $("#txt").html(html);
        }

        function copiar(text) {
            $("body").append("<input type='text' id='temp'>");
            $("#temp").val(text).select();
            document.execCommand("copy");
            $("#temp").remove();
            Swal.fire(
                'Información',
                'Ha Copiado el enlace al portapapeles',
                'success'
            );
        }

        function ir() {
            $('#prev').submit();
        }

        function nueva() {
            var a = $("#name").val();
            if (a == "") {
                Swal.fire(
                    'Información',
                    'Debe indicar un nombre para la carpeta',
                    'error'
                );
                return;
            }
            $('#nueva').submit();
        }

        function ingresar(id) {
            $('#ingresar' + id).submit();
        }

        function upload() {
            $('#upload').submit();
        }

        function borrar(id) {
            var type = document.forms["borrar_" + id]['type'].value;
            if (type == 'FOLDER') {
                Swal.fire({
                    title: 'Confirmación',
                    text: "Está a punto de borrar un directorio y todo su contenido, ¿Desea continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, borrar!',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.value) {
                        $("#borrar_" + id).submit();
                        Swal.fire(
                            'Borrado!',
                            'Su carpeta fue borrada con todo su contenido.',
                            'success'
                        )
                    }
                })
            } else {
                Swal.fire({
                    title: 'Confirmación',
                    text: "Está a punto de borrar un archivo, ¿Desea continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, borrar!',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.value) {
                        $("#borrar_" + id).submit();
                        Swal.fire(
                            'Borrado!',
                            'Su archivo fue borrado.',
                            'success'
                        )
                    }
                })
            }
        }
    </script>

</body>

</html>