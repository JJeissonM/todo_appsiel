@extends('web.templates.main')

@section('style')
<link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
<style>
    .card-body {
        padding: 0 !important;
        overflow: hidden;
    }

    #wrapper {
        overflow-y: scroll;
        overflow-x: hidden;
        width: 40%;
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
        width: 60%;
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
            <h4>.:: Gestión de Artículos ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Categorías</h4>
            <div class="col-md-12">
                <table id="myTable" class="table table-hover table-dark">
                    <thead>
                        <tr>
                            <th scope="col">Categoría</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($categorias)>0)
                        @foreach($categorias as $c)
                        <tr>
                            <td>{{$c->titulo." (".$c->descripcion}}</td>
                            <td>
                                <a data-toggle="tooltip" data-placement="top" title="Cargar Artículos" onclick="buscar(this.id)" id="{{$c->id}}" style="cursor: pointer; color: #fff;" class="btn btn-sm btn-primary"><i class="fa fa-check"></i></a>
                                <a onclick="editor(this.id)" data-toggle="modal" data-target="#exampleModal" id="{{$c->id.';'.$c->titulo}}" style="cursor: pointer;  color: #fff;" class="btn btn-sm btn-success"><i class="fa fa-plus"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Artículos en la Categoría Seleccionada</h4>
            <div class="col-md-12">
                <table class="table table-hover table-dark">
                    <thead>
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbArticulos">

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
                <h5 class="modal-title" id="exampleModalLabel">Crear Artículo en la Categoría: <i id="txt"></i></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    {!! Form::open(['route'=>'article.articlestore','method'=>'POST','id'=>'form-article','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="articlecategory_id" id="articlecategory_id" />
                    <div class="row">
                        <div class="col-md-4">
                            <label class="col-form-label">Estado</label>
                            <span data-toggle="tooltip" title="Establece el estado de visibilidad del articulo."> <i class="fa fa-question-circle"></i></span>
                            <select class="form-control" name="estado">
                                <option value="VISIBLE">ACTIVO (Visible en la web)</option>
                                <option value="OCULTO">INACTIVO (Oculto en la web)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="recipient-name" class="col-form-label">Título</label>
                            <span data-toggle="tooltip" title="Establece el título del articulo."> <i class="fa fa-question-circle"></i></span>
                            <input name="titulo" type="text" class="form-control" id="recipient-name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="col-form-label">Imágen</label>
                            <span data-toggle="tooltip" title="Establece una imagen para el articulo."> <i class="fa fa-question-circle"></i></span>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="col-form-label">Descripción</label>
                            <span data-toggle="tooltip" title="Establece la descripcion del articulo."> <i class="fa fa-question-circle"></i></span>
                            <textarea class="form-control" name="descripcion" maxlength="250"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Contenido</label>
                        <span data-toggle="tooltip" title="Establece el contenido del articulo."> <i class="fa fa-question-circle"></i></span>
                        <textarea name="contenido" class="form-control editor" id="contenido"></textarea>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submit()">Guardar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar Artículo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    {!! Form::open(['route'=>'article.articleupdate','method'=>'POST','id'=>'form-article-edit','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="article_id" id="article_id" />
                    <div class="row">
                        <div class="col-md-4" id="textestado">

                        </div>
                        <div class="col-md-8">
                            <label for="recipient-name" class="col-form-label">Título</label>
                            <span data-toggle="tooltip" title="Establece el título del articulo."> <i class="fa fa-question-circle"></i></span>
                            <input name="titulo" type="text" class="form-control" id="tituloe">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="col-form-label">Descripción</label>
                            <span data-toggle="tooltip" title="Establece la descripcion del articulo."> <i class="fa fa-question-circle"></i></span>
                            <textarea class="form-control" name="descripcion" id="descripcione" maxlength="250"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="col-form-label">Imágen</label>
                            <span data-toggle="tooltip" title="Establece una imagen para el articulo."> <i class="fa fa-question-circle"></i></span>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                        </div>
                    </div>

                    <div class="form-group" id="textarea">

                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submit2()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{asset('js/sweetAlert2.min.js')}}"></script>
<script src="{{asset('assets/js/axios.min.js')}}"></script>

<script type="text/javascript">
    $(function() {

    });

    function editor(id) {
        var v = id.split(";");
        $("#articlecategory_id").val(v[0]);
        $("#txt").html(v[1]);
        CKEDITOR.replace('contenido', {
            height: 200,
            removeButtons: ''
        });
    }

    function buscar(id) {
        $.ajax({
            type: 'GET',
            url: "{{url('article/category/articles/list')}}" + "/" + id,
            data: {},
        }).done(function(msg) {
            $('#tbArticulos').html("");
            if (msg !== "null") {
                var m = JSON.parse(msg);
                var html = "";
                $.each(m, function(index, item) {
                    html = html + "<tr><td>" + item.titulo + "</td>" +
                        "<td>" + item.descripcion + "</td>" +
                        "<td>" + item.estado + "</td>" +
                        "<td>" +
                        "<a href='{{url('articles/article/viewfinder/only')}}/" + item.id + "{{$variables_url}}' data-toggle='tooltip' data-placement='top' title='Ver Artículo' style='cursor: pointer; color: #fff;' class='btn btn-sm btn-primary'><i class='fa fa-eye'></i></a>" +
                        " <a id='" + JSON.stringify(item) + "' onclick='editar(this.id)' data-toggle='modal' data-target='#exampleModal2' data-toggle='tooltip' data-placement='top' title='Editar Artículo' style='cursor: pointer; color: #fff;' class='btn btn-sm btn-success'><i class='fa fa-edit'></i></a>" +
                        " <a id='" + item.id + ";" + item.articlecategory_id + "' onclick='eliminarArticulo(this.id)' data-toggle='tooltip' data-placement='top' title='Eliminar Artículo' style='cursor: pointer; color: #fff;' class='btn btn-sm btn-danger'><i class='fa fa-trash-o'></i></a></td></tr>";
                });
                $("#tbArticulos").html(html);
            } else {
                Swal.fire(
                    'Información!',
                    'Categoría vacía!',
                    'error'
                );
            }
        });
    }

    function submit() {
        $("#form-article").submit();
    }

    function submit2() {
        $("#form-article-edit").submit();
    }

    function eliminarArticulo(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, bórralo!'
        }).then((result) => {
            if (result.value) {
                axios.get("{{url('article/delete/destroy')}}" + "/" + id.split(";")[0])
                    .then(function(response) {
                        const data = response.data;
                        if (data.status == 'ok') {
                            Swal.fire(
                                'Eliminado!',
                                'Su archivo ha sido eliminado.',
                                'success'
                            );
                            var c = id.split(";")[1];
                            if (c != '') {
                                buscar(c);
                            }
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message,
                                'danger'
                            )
                        }
                    });
            }
        });
    }

    function editar(id) {
        var i = JSON.parse(id);
        $("#tituloe").val("");
        $("#textarea").html("");
        $("#textestado").html("");
        $("#article_id").val(i.id);
        //poner datos
        $("#tituloe").val(i.titulo);
        $("#descripcione").html(i.descripcion);
        var htmlestado = "<label class='col-form-label'>Estado</label><span data-toggle='tooltip' title='Establece el estado de visibilidad del articulo.''> <i class='fa fa-question-circle'></i></span>" +
            "<select class='form-control' name='estado' id='estadoe'>" +
            "<option value='VISIBLE'>VISIBLE EN LA SECCIÓN</option>" +
            "<option value='OCULTO'>OCULTO EN LA SECCIÓN</option></select>";
        $("#textestado").html(htmlestado);
        $("#estadoe option[value=" + i.estado + "]").attr("selected", true);
        var html = "<label for='message-text' class='col-form-label'>Contenido</label><span data-toggle='tooltip' title='Establece el contenido del articulo.'> <i class='fa fa-question-circle'></i></span><textarea" +
            " name='contenido' class='form-control editor' id='contenidoe'>" + i.contenido + "</textarea>";
        $("#textarea").html(html);
        CKEDITOR.replace('contenidoe', {
            height: 200,
            removeButtons: ''
        });
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    }
    $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
</script>
@endsection