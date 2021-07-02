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
            <h4>.:: Ver Artículo ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div class="col-md-12">
            <div class="content-txt">
                <div class="blog-post blog-media">
                    <article class="media clearfix">
                        <div class="media-body">
                            <header class="entry-header">

                                <?php
                                $url_imagen = 'assets/img/blog-default.jpg';
                                if ($articulo->imagen != '') {
                                    $url_imagen = $articulo->imagen;
                                }
                                ?>
                                @if($articulo->imagen != '')
                                <p style="text-align: center;width: 100%;">
                                    <img src="{{ asset( $url_imagen )}}" style=" max-height: 350px;object-fit: cover;">
                                </p>
                                @endif
                                <h2 class="entry-title" style="width: 100%; text-align: center;"><a href="#">{{$articulo->titulo}}</a></h2>
                            </header>

                            <div class="entry-content">
                                <P>{!! $articulo->contenido !!}</P>
                            </div>

                            <footer class="entry-meta" style="text-align: right;">
                                <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{{$articulo->updated_at}}</a></span>
                                <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">@if($articulo->articlecategory!=null) {{$articulo->articlecategory->titulo}} @else Sin Categoría @endif</a></span>
                            </footer>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
    $(function() {

    });
</script>
@endsection