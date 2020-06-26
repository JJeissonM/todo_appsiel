@extends('web.templates.main')


@section('style')

<style>
    .card-body {
        padding: 0 !important;
        overflow: hidden;
    }

    #wrapper {
        overflow-y: scroll;
        overflow-x: hidden;
        width: 30%;
        height: 100vh;
        margin-right: 0;
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

    .widgets {
        width: 70%;
        height: 100vh;
        overflow-y: scroll;
    }

    .widgets img {
        width: 100%;
        object-fit: cover;
        height: 72.5vh;
        max-width: 100%;
    }

    .widgets .card-body {
        position: relative;
    }

    .activo {}

    .contenido {
        display: flex;
        padding: 5px;
        border: 1px solid #3d6983;
        border-radius: 5px;
    }

    .contenido img {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }

    .descripcion {
        padding: 5px;
    }

    .descripcion h5 {
        color: black;
        font-size: 16px;
    }

    .add {
        margin-top: 20px;
    }

    .add a {
        color: #1c85c4;
    }

    .btn-link {
        cursor: pointer;
    }

    .panel {
        background-color: #fff;
        border: 1px solid transparent;
        border-radius: 4px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
        padding: 10px;
        margin-top: 5px;
        cursor: pointer;
        width: 100%;
    }

    .panel-title>a {
        padding: 10px;
        color: #000;
    }

    .panel-group .panel {
        margin-bottom: 0;
        border-radius: 4px;
    }

    .panel-default {
        border-color: #eee;
    }

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
</style>

@endsection

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: Bienvenido {{Auth::user()->name}} | Configuraciones Globales ::.</h4>
        </div>
        <div class="col-md-12">
            <div style="margin-top:20px; margin-bottom: 40px; width:100%; border:1px solid #e9ecef; -webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75); -moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75); box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);">
                <h1 style="width: 180px; font-size:18px; margin-top:-12px; margin-left:20px; text-align: center; background:white;">Accesos Rápidos</h1>
                <div class="col-md-12" style="padding: 20px;">
                    <a href="{{url('').'/web'.$variables_url.'&id_modelo=77'}}" class="btn btn-primary">Categorías de Artículos</a>
                    <a href="{{route('articles.index').$variables_url}}" class="btn btn-primary">Artículos</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Configuraciones Globales</h4>
            <div class="col-md-12">
                <div id="accordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Configuraciones generales
                                </button>
                            </h5>
                        </div>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    @if($configuracion == null)
                                    <form action="{{route('cofiguraciones.store')}}" method="POST">
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <input type="hidden" name="_method" value="POST">
                                        <div class="form-group">
                                            <label for="">Color Primario</label>
                                            <input type="color" id="color_terciario" onchange="selectColor(event)" class="form-control" name="color_primario" value="" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="">Color Segundario</label>
                                            <input type="color" id="color_segundario" onchange="selectColor(event)" class="form-control" name="color_segundario" value="" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="">Color Terciario</label>
                                            <input type="color" id="color_terciario" onchange="selectColor(event)" class="form-control" name="color_terciario" value="" required>
                                        </div>

                                        <div class="form-group d-flex justify-content-end">
                                            <button type="submit" class="btn btn-info">Guardar</button>
                                        </div>

                                    </form>
                                    @else
                                    <form action="{{route('cofiguraciones.update',$configuracion->id)}}" method="POST">
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <input type="hidden" name="_method" value="PUT">
                                        <div class="form-group">
                                            <label for="">Color Primario</label>
                                            <input type="color" id="color_primario" onchange="selectColor(event)" class="form-control" name="color_primario" value="{{$configuracion->color_primario}}" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="">Color Segundario</label>
                                            <input type="color" id="color_segundario" onchange="selectColor(event)" class="form-control" name="color_segundario" value="{{$configuracion->color_segundario}}" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="">Color Terciario</label>
                                            <input type="color" id="color_terciario" onchange="selectColor(event)" class="form-control" name="color_terciario" value="{{$configuracion->color_terciario}}" required>
                                        </div>

                                        <div class="form-group d-flex justify-content-end">
                                            <button type="submit" class="btn btn-info">Guardar</button>
                                        </div>

                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Configurar Barra de Navegación y Logo
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    <p>Color del nav-bar, logo, fuente, tamaño fuente...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Configurar Footer
                                </button>
                            </h5>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    <p>Color del fondo...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                -->
                </div>
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title pull-left" style="padding: 10px;">Formularios de Contáctenos</h4>
            <div class="col-md-12" style="margin-top: 70px;">
                <table id="myTable" class="table table-responsive table-striped">
                    <thead>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Leer</th>
                    </thead>
                    <tbody id="paginas">
                        @foreach($contacts as $c)
                        <tr>
                            <td>{{$c->names}}</td>
                            <td>{{$c->email}}</td>
                            <td>{{$c->subject}}</td>
                            <td>@if($c->state=='READ') <label style="background-color: green; color:#FFF; padding: 5px;" class="label label-success">LEÍDO</label> @else <label style="background-color: red; color:#FFF; padding: 5px;" class="label label-success">SIN LEER</label> @endif</td>
                            <td>
                                <a onclick="leer(this.id)" id="{{$c}}" class="btn bg-warning" title="Leer Mensaje" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-check"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Leer Formulario de Contácto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="rta">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrar()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')

<script type="text/javascript">
    $(function() {
        const color_primario = document.getElementById('color_primario');
        color_primario.style.backgroundColor = color_primario.getAttribute('value');
        const color_segundario = document.getElementById('color_segundario');
        color_segundario.style.backgroundColor = color_segundario.getAttribute('value');
        const color_terciario = document.getElementById('color_terciario');
        color_terciario.style.backgroundColor = color_terciario.getAttribute('value');
    });

    function selectColor(event) {
        event.target.style.backgroundColor = event.target.value;
    }

    function leer(m) {
        $("#rta").html("");
        var v = JSON.parse(m);
        var html = "<h5>" + v.names + "</h5><p>" + v.email + "</p><p><b>" + v.subject + "</b></p><p>" + v.message + "</p>";
        $("#rta").html(html);
        $.ajax({
            url: "configuracion/contactenos/" + v.id + "/leer",
            context: document.body
        }).done(function(response) {
            //ok
            console.log(response);
        });
    }

    function cerrar() {
        location.reload();
    }
</script>

@endsection