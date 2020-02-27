<section id="blog">
    <div class="container">
        @if($articles!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">{{$articles->titulo}}</h2>
            <p class="text-center wow fadeInDown">{{$articles->descripcion}}</p>
        </div>

        <div class="row col-md-12 wow fadeInDown">
            @if($articles->formato=='LISTA')
            @foreach($articles->articles as $a)
            <div class="col-md-12 article-ls" style="line-height: 5px; margin-bottom: 20px;">
                <div class="media service-box" style="margin: 10px !important; font-size: 14px;">
                    <div class="pull-left">
                        <i style="cursor: pointer;" class="fa fa-edit"></i>
                    </div>
                    <div class="media-body">
                        <h6 style="font-size: 14px;" class="media-heading">{{$a->titulo}}</h6>
                        <p>{!! str_limit($a->contenido, $limit = 100, $end = '...') !!}</p>
                        <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{{$a->updated_at}}</a></span>
                        <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{{$articles->titulo}}</a></span>
                        <i class="fa fa-plus"></i> <a href="#" class="entry-category">Leer más...</a>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
            @if($articles->formato=='BLOG')
            @foreach($articles->articles as $a)
            <div class="col-md-6">
                <div class="blog-post blog-media">
                    <article class="media clearfix">
                        <div class="entry-thumbnail pull-left">
                            <span class="post-format post-format-gallery"><i class="fa fa-bullhorn"></i></span>
                        </div>
                        <div class="media-body">
                            <header class="entry-header">
                                <div class="entry-date">{{$a->created_at}}</div>
                                <h2 class="entry-title"><a href="#">{{$a->titulo}}</a></h2>
                            </header>

                            <div class="entry-content">
                                <P>{!! str_limit($a->contenido, $limit = 100, $end = '...') !!}</P>
                                <a class="btn btn-primary" href="#">Leer más...</a>
                            </div>

                            <footer class="entry-meta">
                                <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{{$a->updated_at}}</a></span>
                                <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{{$articles->titulo}}</a></span>
                            </footer>
                        </div>
                    </article>
                </div>
            </div>
            @endforeach
            @endif
        </div>
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div>

</section>