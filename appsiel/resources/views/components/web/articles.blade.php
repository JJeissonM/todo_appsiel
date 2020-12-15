<style>

    #blog {
        padding-top: 100px !important;
        position: relative;
        z-index: 80 !important;

        <?php
        if ($setup != null) {
            if ($setup->tipo_fondo == 'COLOR') {
                echo "background-color: " . $setup->fondo . ";";
            } else {
        ?>background: url('{{$setup->fondo}}') {{$setup->repetir}} center {{$setup->direccion}};
        <?php
            }
        }
        ?>
    }

    .article-font {
        font-family: <?php echo $setup->configuracionfuente->fuente->font; ?> !important;
    }

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

<section id="blog" class="article-font">

    {{ Form::Spin(128) }}

    <div id="visor_contenido_articulos" class="article-font">

    </div>

    <div class="container" id="contenedor_seccion_articulos">


        @if( $setup != null )
            <div class="section-header">

                @if( $setup->titulo != '' )
                    <h2 class="section-title text-center wow fadeInDown article-font">{{ $setup->titulo }}</h2>
                @endif

                @if( $setup->descripcion != '' )
                    <p class="text-center wow fadeInDown article-font" style="font-weight: bold; color: #000;">{{ $setup->descripcion }}</p>
                @endif

            </div>

            @if( $setup->article_id != null)
            <!-- SE MUESTRA SOLO UN ARTÍCULO -->
                <div class="content-txt" style="padding: 20px; margin: 10px !important; font-size: 14px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px #cf9ec3; -moz-box-shadow: 1px 1px 100px #cf9ec3; box-shadow: 1px 1px 100px #cf9ec3;">
                    <div class="blog-post blog-media" style="border: none;">
                        <article class="media clearfix">
                            <div class="media-body">
                                <header class="entry-header" style="background-color: transparent !important;">
                                    <?php
                                    $url_imagen = 'assets/img/blog-default.jpg';
                                    if ($articles->imagen != '') {
                                        $url_imagen = $articles->imagen;
                                    }
                                    ?>
                                    <p style="text-align: center;width: 100%;">
                                        <img src="{{ asset( $url_imagen )}}" style=" max-height: auto; object-fit: cover;">
                                    </p>
                                    <h2 class="article-font" style="width: 100%; text-align: center;">{{$articles->titulo}}</h2>
                                </header>
                                <div class="entry-content">
                                    <P class="article-font">{!! $articles->contenido !!}</P>
                                </div>
                                <footer class="entry-meta" style="text-align: right;">
                                    <span class="entry-author"><i class="fa fa-calendar"></i> <a style="font-weight: bold; font-size: 20px;" class="article-font" href="#">{{$articles->updated_at}}</a></span>
                                    <span class="entry-category"><i class="fa fa-folder-o"></i> <a style="font-weight: bold; font-size: 20px;" class="article-font" href="#">@if($articles->articlecategory!=null) {{$articles->articlecategory->titulo}} @else Sin Categoría @endif</a></span>
                                </footer>
                            </div>
                        </article>
                    </div>
                </div>
            @else
            <!-- SE MUESTRA UNA CATEGORÍA SEGÚN FORMATO: LISTA O BLOG -->
                @if($setup->formato=='LISTA')
                    @foreach($articles as $a)
                        <div class="col-md-12 wow fadeInUp animated service-info"  data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                            <div class="media service-box" style="margin: 10px !important; font-size: 14px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px #cf9ec3; -moz-box-shadow: 1px 1px 100px #cf9ec3; box-shadow: 1px 1px 100px #cf9ec3;">
                                <div class="media-body">
                                    <div class="row">
                                        <div class="col-md-4" style="text-align: center;">
                                            <?php
                                            $url_imagen = 'assets/img/blog-default.jpg';
                                            if ($a->imagen != '') {
                                                $url_imagen = $a->imagen;
                                            }
                                            ?>
                                            <img src="{{ asset( $url_imagen )}}" style="width: 100%; max-height: 180px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-8" style="padding: 20px;">
                                            <h4 class="media-heading article-font">{{$a->titulo}}</h4>
                                            <p class="article-font">{!! str_limit($a->descripcion,100,'...') !!}</p>
                                            <p><a onclick="visor_contenido_articulos({{ $a->id }})" class="btn btn-primary waves-effect btn-sm article-font" style="color: #fff; cursor: pointer;">Leer más <i class="fa fa-arrow-right"></i></a></p>
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
                        <!-- TIPO SANTILLANA -->
                        <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                            <div style="border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px #cf9ec3; -moz-box-shadow: 1px 1px 100px #cf9ec3; box-shadow: 1px 1px 100px #cf9ec3;">
                                <?php
                                    $url_imagen = 'assets/img/blog-default.jpg';
                                    if ($a->imagen != '') {
                                        $url_imagen = $a->imagen;
                                    }
                                ?>    
                                <div style="background-color: #fff; border-top-right-radius: 20px !important; border-top-left-radius: 20px !important;"><img style="width: 100%;" src="{{asset($url_imagen)}}"></div>
                                <div style="background-color: #fff; padding: 20px; border-bottom-right-radius: 20px !important; border-bottom-left-radius: 20px !important;">
                                    <h4 class="media-heading article-font" style="margin-top: 0px;">{{$a->titulo}}</h4>
                                    <p class="article-font">{!! str_limit($a->descripcion,90,'...') !!} </p>
                                    <a class="btn btn-primary animate article-font" onclick="visor_contenido_articulos({{ $a->id }})" style="cursor: pointer; color: #fff;">Leer más...</a>
                                </div>
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

            $.get(url).done(function(data) {
                    $('#visor_contenido_articulos').html(data);
                    $('#visor_contenido_articulos').fadeIn(500);
                }).fail(function() {
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