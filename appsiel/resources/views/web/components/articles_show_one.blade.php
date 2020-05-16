
<div class="row">
      <div class="col-md-12 light-txt">
            <div class="content-txt">
                  <div class="blog-post blog-media">
                        <article class="media clearfix">
                              <div class="media-body">
                                    <header class="entry-header">
                                          <p style="text-align: center;width: 100%;">
                                          <img src="{{ asset( $a->imagen )}}" style=" max-height: 350px;object-fit: cover;">
                                          </p>
                                          <h2 class="entry-title"><a href="#">{{$a->titulo}}</a></h2>
                                          <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">Publicado: {{$a->created_at}}</a></span>
                                          <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">Sección: {{$a->articlesetup->titulo}}</a></span>
                                    </header>

                                    <div class="entry-content">
                                          <P>{!! $a->contenido !!}</P>
                                    </div>

                                    <footer class="entry-meta">
                                          <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{{$a->updated_at}}</a></span>
                                          <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{{$a->articlesetup->titulo}}</a></span>
                                    </footer>
                              </div>
                        </article>
                  </div>
            </div>
      </div>
</div>