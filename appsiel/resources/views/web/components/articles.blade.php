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
            <h4>.:: En ésta Sección: Artículos ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Artículos</h4>
            <div class="col-md-12">
                <div id="accordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Configuración de la Sección
                                </button>
                            </h5>
                        </div>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    @if($setup!=null)
                                    <!-- EDITAR -->
                                    {!! Form::model($setup,['route'=>['articles.update',$setup],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Título</label>
                                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                                        <input type="text" class="form-control" value="{{$setup->titulo}}" required name="titulo">
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                                        <input type="text" class="form-control" value="{{$setup->descripcion}}" name="descripcion">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Fuente Para el Componente</label>
                                        <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                                        @if($fonts!=null)
                                        {!! Form::select('configuracionfuente_id',$fonts,$setup->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>Formato (Solo aplica para categoría de artículos)</label>
                                        <span data-toggle="tooltip" title="Establece el estilo de vista de la sección. Formato Lista: <img src='{{asset('assets/img/articles-lista.png')}}' /> o Formato Blog: <img src='{{asset('assets/img/articles-blog.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                                        <select class="form-control" name="formato">
                                            @if($setup->formato=='LISTA')
                                            <option selected value="LISTA">ARTÍCULOS EN FORMATO DE LISTA</option>
                                            <option value="BLOG">ARTÍCULOS EN FORMATO DE BLOG</option>
                                            @else
                                            <option value="LISTA">ARTÍCULOS EN FORMATO DE LISTA</option>
                                            <option selected value="BLOG">ARTÍCULOS EN FORMATO DE BLOG</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Órden (Solo aplica para categoría de artículos)</label>
                                        <span data-toggle="tooltip" title="Establece el orden en que se mostraran los articulos de la sección."> <i class="fa fa-question-circle"></i></span>
                                        <select class="form-control" name="orden">
                                            @if($setup->orden=='ASC')
                                            <option selected value="ASC">MOSTRAR ANTIGUOS PRIMERO</option>
                                            <option value="DESC">MOSTRAR LOS MAS RECIENTES PRIMERO</option>
                                            @else
                                            <option value="ASC">MOSTRAR ANTIGUOS PRIMERO</option>
                                            <option selected value="DESC">MOSTRAR LOS MAS RECIENTES PRIMERO</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Esta Sección Mostrará <span data-toggle="tooltip" title="Establece el tipo de vista en que se mostraran los articulos de la sección."> <i class="fa fa-question-circle"></i></span> (Actual: @if($setup->article_id!=null) UN ARTÍCULO @elseif($setup->articlecategory_id!=null) CATEGORÍA @else --- @endif)</label>
                                        <select class="form-control" name="mostrara2" id="mostrara2" onchange="cambiar2()">
                                            <option value="0">-- Seleccione una opción --</option>
                                            <option value="ARTICULO">UN SOLO ARTÍCULO</option>
                                            <option value="CATEGORIA">UNA CATEGORÍA DE ARTÍCULOS</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="mos3">
                                        <label>Indique el Artículo</label>
                                        <label>Esta Sección Mostrará<span data-toggle="tooltip" title="Establece el articulo a mostrar de la sección."> <i class="fa fa-question-circle"></i></span></label>
                                        <select style="width: 100%;" class="select2" name="article_id">
                                            @if(count($articulos)>0)
                                            @foreach($articulos as $ar)
                                            <option value="{{$ar->id}}">{{$ar->titulo}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group" id="mos4">
                                        <label>Indique la Categoría de Artículos</label>
                                        <label>Esta Sección Mostrará <span data-toggle="tooltip" title="Establece la categoria de los articulos a mostrar de la sección."> <i class="fa fa-question-circle"></i></span> </label>
                                        <select style="width: 100%;" class="select2" name="articlecategory_id">
                                            @if(count($categorias)>0)
                                            @foreach($categorias as $ca)
                                            <option value="{{$ca->id}}">{{$ca->titulo." (".$ca->descripcion.")"}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>¿El fondo es Imagen o Color?</label>
                                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen: <img src='{{asset('assets/img/fondo-imagen.png')}}' /> o Color: <img src='{{asset('assets/img/fondo-color.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                                        <select type="select" class="form-control" id="tipo_fondo2" name="tipo_fondo" onchange="cambiarFondo2()">
                                            @if($setup->tipo_fondo=='IMAGEN')
                                            <option value="">-- Seleccione una opción --</option>
                                            <option selected value="IMAGEN">IMAGEN</option>
                                            <option value="COLOR">COLOR</option>
                                            @else
                                            <option value="">-- Seleccione una opción --</option>
                                            <option value="IMAGEN">IMAGEN</option>
                                            <option selected value="COLOR">COLOR</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group" id="fondo_container2">
                                        @if($setup->tipo_fondo=='IMAGEN')
                                        <label>Imagen de Fondo</label>
                                        <a target="_blank" href="{{asset($setup->fondo)}}">Ver Actual</a><br>
                                        <b>Repetir: {{$setup->repetir}}</b><br>
                                        <b>Orientación Imagen: {{$setup->direccion}}</b>
                                        @else
                                        <label>Color de Fondo</label>
                                        <div class="col-md-12" style="background-color: {{$setup->fondo}}; width: 100%; height: 20px;"></div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                    @else
                                    <!-- CREAR -->
                                    {!! Form::open(['route'=>'article.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                                    <input type="hidden" name="widget_id" value="{{$widget}}">
                                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                    <div class="form-group">
                                        <label>Título</label>
                                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                                        <input type="text" class="form-control" required name="titulo">
                                    </div>
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                                        <input type="text" class="form-control" name="descripcion">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Fuente Para el Componente</label>
                                        <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                                        @if($fonts!=null)
                                        {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>Formato (Solo aplica para categoría de artículos)</label>
                                        <span data-toggle="tooltip" title="Establece el estilo de vista de la sección. Formato Lista: <img src='{{asset('assets/img/articles-lista.png')}}' /> o Formato Blog: <img src='{{asset('assets/img/articles-blog.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                                        <select class="form-control" name="formato">
                                            <option value="LISTA">ARTÍCULOS EN FORMATO DE LISTA</option>
                                            <option value="BLOG">ARTÍCULOS EN FORMATO DE BLOG</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Órden (Solo aplica para categoría de artículos)</label>
                                        <span data-toggle="tooltip" title="Establece el orden en que se mostraran los articulos de la sección."> <i class="fa fa-question-circle"></i></span>
                                        <select class="form-control" name="orden">
                                            <option value="ASC">MOSTRAR ANTIGUOS PRIMERO</option>
                                            <option value="DESC">MOSTRAR LOS MAS RECIENTES PRIMERO</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Esta Sección Mostrará <span data-toggle="tooltip" title="Establece el tipo de vista en que se mostraran los articulos de la sección."> <i class="fa fa-question-circle"></i></span> </label>
                                        <select class="form-control" name="mostrara" id="mostrara" onchange="cambiar()">
                                            <option value="0">-- Seleccione una opción --</option>
                                            <option value="ARTICULO">UN SOLO ARTÍCULO</option>
                                            <option value="CATEGORIA">UNA CATEGORÍA DE ARTÍCULOS</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="mos">
                                        <label>Indique el Artículo<span data-toggle="tooltip" title="Establece el articulo a mostrar de la sección."> <i class="fa fa-question-circle"></i></span></label>
                                        <select style="width: 100%;" class="select2" name="article_id">
                                            @if(count($articulos)>0)
                                            @foreach($articulos as $ar)
                                            <option value="{{$ar->id}}">{{$ar->titulo}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group" id="mos2">
                                        <label>Indique la Categoría de Artículos <span data-toggle="tooltip" title="Establece la categoria de los articulos a mostrar de la sección."> <i class="fa fa-question-circle"></i></span></label>
                                        <select style="width: 100%;" class="select2" name="articlecategory_id">
                                            @if(count($categorias)>0)
                                            @foreach($categorias as $ca)
                                            <option value="{{$ca->id}}">{{$ca->titulo." (".$ca->descripcion.")"}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>¿El fondo es Imagen o Color?</label>
                                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen: <img src='{{asset('assets/img/fondo-imagen.png')}}' /> o Color: <img src='{{asset('assets/img/fondo-color.png')}}' />"> <i class="fa fa-question-circle"></i></span>
                                        <select type="select" class="form-control" id="tipo_fondo" required name="tipo_fondo" onchange="cambiarFondo()">
                                            <option value="">-- Seleccione una opción --</option>
                                            <option value="IMAGEN">IMAGEN</option>
                                            <option value="COLOR">COLOR</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="fondo_container">
                                    </div>
                                    <div class="form-group">
                                        {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                    </div>
                                    {!! Form::close() !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($setup != null)
            {!! Form::articles($articles,$setup)!!}
            @else
            <p style="color: red;"> <i class="fa fa-warning"></i> La sección no ha sido configurada!</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('assets/js/axios.min.js')}}"></script>
<script src="{{asset('js/sweetAlert2.min.js')}}"></script>

<script type="text/javascript">
    $(function() {
        $('.select2').select2();
        $("#mos").fadeOut();
        $("#mos2").fadeOut();
        $("#mos3").fadeOut();
        $("#mos4").fadeOut();
    });

    var asetup = <?php echo json_encode($setup); ?>;
    var articulosArray = <?php echo json_encode($articles); ?>;


    function cambiar() {
        var m = $("#mostrara").val();
        if (m == 'ARTICULO') {
            $("#mos").fadeIn();
            $("#mos2").fadeOut();
        } else if (m == 'CATEGORIA') {
            $("#mos").fadeOut();
            $("#mos2").fadeIn();
        } else {
            $("#mos").fadeOut();
            $("#mos2").fadeOut();
        }
    }

    function cambiar2() {
        var m = $("#mostrara2").val();
        if (m == 'ARTICULO') {
            $("#mos3").fadeIn();
            $("#mos4").fadeOut();
        } else if (m == 'CATEGORIA') {
            $("#mos3").fadeOut();
            $("#mos4").fadeIn();
        } else {
            $("#mos3").fadeOut();
            $("#mos4").fadeOut();
        }
    }

    function cambiarFondo() {
        $("#fondo_container").html("");
        var f = $("#tipo_fondo").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + `Imagen de Fondo <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='file' class='form-control' name='fondo' required>` +
                `<label>Repetir <span data-toggle="tooltip" title="Establece si la imagen se repite en el fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>` +
                `<label>Orientación Imagen <span data-toggle="tooltip" title="Establece la orientacion de la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>
                <select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option><option value='top'>ARRIBA</option></select>`;
        } else if (f == 'COLOR') {
            html = html + `Color de Fondo <span data-toggle="tooltip" title="Establece el color de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='color' class='form-control' name='fondo' required>`;
        } else {
            html = "";
        }
        $("#fondo_container").html(html);

        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    }

    function cambiarFondo2() {
        $("#fondo_container2").html("");
        var f = $("#tipo_fondo2").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + `Imagen de Fondo <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='file' class='form-control' name='fondo' required>` +
                `<label>Repetir <span data-toggle="tooltip" title="Establece si la imagen se repite en el fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>` +
                `<label>Orientación Imagen <span data-toggle="tooltip" title="Establece la orientacion de la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>
                <select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option><option value='top'>ARRIBA</option></select>`;
        } else if (f == 'COLOR') {
            html = html + `Color de Fondo <span data-toggle="tooltip" title="Establece el color de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='color' class='form-control' name='fondo' required>`;
        } else {
            html = "";
        }
        $("#fondo_container2").html(html);

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