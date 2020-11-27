<style>
    .article {
        background-color: white;
        border: 0px;
    }

    .article:hover {
        transform: scale(1.02);
        box-shadow: 0px 0px 5px 1px #3d6983;
        cursor: pointer;
    }
</style>

<section id="blog">

    {{ Form::Spin(128) }}

    <div id="visor_contenido_articulos">

    </div>

    <div class="container" id="contenedor_seccion_articulos">


        @if( $setup != null )
            <div class="section-header">

                @if( $setup->titulo != '' )
                    <h2 class="section-title text-center wow fadeInDown">{{ $setup->titulo }}</h2>
                @endif

                @if( $setup->descripcion != '' )
                    <p class="text-center wow fadeInDown">{{ $setup->descripcion }}</p>
                @endif

            </div>

            @if( $setup->article_id != null)
                <div class="content-txt">
                    <div class="blog-post blog-media">
                        <article class="media clearfix">
                            <div class="media-body">
                                <header class="entry-header">

                                    <?php
                                    $url_imagen = 'assets/img/blog-default.jpg';
                                    if ($articles->imagen != '') {
                                        $url_imagen = $articles->imagen;
                                    }
                                    ?>

                                    <p style="text-align: center;width: 100%;">
                                        <img src="{{ asset( $url_imagen )}}" style=" max-height: 350px;object-fit: cover;">
                                    </p>

                                    <h2 class="entry-title" style="width: 100%; text-align: center;"><a href="#">{{$articles->titulo}}</a></h2>
                                </header>

                                <div class="entry-content">
                                    <P>{!! $articles->contenido !!}</P>
                                </div>

                                <footer class="entry-meta" style="text-align: right;">
                                    <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{{$articles->updated_at}}</a></span>
                                    <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">@if($articles->articlecategory!=null) {{$articles->articlecategory->titulo}} @else Sin Categoría @endif</a></span>
                                </footer>
                            </div>
                        </article>
                    </div>
                </div>
            @else

                @if($setup->formato=='LISTA')
                    @foreach($articles as $a)
                        <div class="col-md-12 article-ls" style="line-height: 5px; margin-bottom: 20px;">
                            <div class="media service-box" style="margin: 10px !important; font-size: 14px;">
                                <div class="media-body">
                                    <div class="row">
                                        <div class="col-md-4" style="text-align: center;">

                                            <?php
                                            $url_imagen = 'assets/img/blog-default.jpg';
                                            if ($a->imagen != '') {
                                                $url_imagen = $a->imagen;
                                            }
                                            ?>

                                            <img src="{{ asset( $url_imagen )}}" style="width: 100%; max-height: 180px;object-fit: cover;">

                                        </div>
                                        <div class="col-md-8">
                                            <h3 style="font-size: 14px;" class="media-heading">{{$a->titulo}}</h3>
                                            <p>{!! $a->descripcion !!}</p>
                                            <!-- <p><span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{ {$a->updated_at}}</a></span></p> -->
                                            <!-- <p><span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{ {$setup->titulo}}</a></span></p> -->
                                            <p><a onclick="visor_contenido_articulos({{ $a->id }})" class="btn btn-primary waves-effect btn-sm"><i class="fa fa-plus"></i> Leer más...</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($setup->formato=='BLOG')
                    <div class="row">
                        @foreach($articles as $a)

                        <div class="col-md-4">
                            <div class="article blog-post blog-media">
                                <article class="media clearfix">
                                    <!-- <div class="entry-thumbnail pull-left">
                                                    <span class="post-format post-format-gallery"><i class="fa fa-bullhorn"></i></span>
                                                </div> 
                                                <a target="_blank" href="{ {route('article.show',$a->id)}}" style="text-decoration: none;">-->

                                    <a onclick="visor_contenido_articulos({{ $a->id }})">

                                        <div class=" media-body">
                                            <div style="text-align: center;">

                                                <?php
                                                $url_imagen = 'assets/img/blog-default.jpg';
                                                if ($a->imagen != '') {
                                                    $url_imagen = $a->imagen;
                                                }
                                                ?>

                                                <img src="{{ asset( $url_imagen )}}" style="width: 100%; max-height: 180px;object-fit: cover;">
                                            </div>

                                            <header class="entry-header">
                                                <!-- <div class="entry-date">{ {$a->created_at}}</div> -->
                                                <h2 class="entry-title"> {{$a->titulo}} </h2>
                                            </header>

                                            <div class="entry-content">
                                                <p>{!! str_limit($a->descripcion, $limit = 100, $end = '...') !!}</p>
                                                <hr>
                                            </div>

                                            <!-- 
                                                    <footer class="entry-meta">
                                                        <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{ {$a->updated_at}}</a></span>
                                                        <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{ {$setup->titulo}}</a></span>
                                                    </footer>
                                                -->
                                        </div>
                                    </a>

                                </article>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

            @endif

        @else <!-- Cuando $setup == null -->
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown">Sección</h2>
                <p class="text-center wow fadeInDown">Sin configuración</p>
            </div>
        @endif

    </div>

    <script type="text/javascript">
        function visor_contenido_articulos(item_id) {
            $('#visor_contenido_articulos').html('');

            $('#contenedor_seccion_articulos').fadeOut(1000);

            var url = "{{url('articles')}}" + '/' + item_id;

            $.get(url)
                .done(function(data) {

                    $('#visor_contenido_articulos').html(data);
                    $('#visor_contenido_articulos').fadeIn(500);
                })
                .error(function() {

                    $('#contenedor_seccion_articulos').fadeIn(500);
                    $('#visor_contenido_articulos').show();
                    $('#visor_contenido_articulos').html('<p style="color:red;">Elemento no puede ser mostrado. Por favor, intente nuevamente.</p>');
                });
        }


        function ver_contenedor_seccion_articulos() {
            $('#contenedor_seccion_articulos').fadeIn(500);
            $('#visor_contenido_articulos').html('');
            $('#visor_contenido_articulos').hide();
        }
    </script>

</section>