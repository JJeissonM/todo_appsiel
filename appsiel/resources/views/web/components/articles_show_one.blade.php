
<div class="container-fluid">
      <p>
            
            <a href="{{url('/')}}"> <i class="fa fa-home"></i> </a>
            /
            <a onclick="ver_contenedor_seccion_articulos()" href="#"> Todos los artículos </a>
            
      </p>
      <div class="col-md-12 light-txt">
            <div class="content-txt">
                  <div class="blog-post blog-media">
                        <article class="media clearfix">
                              <div class="media-body">
                                    <header class="entry-header">

                                              <?php
                                                  $url_imagen = 'assets/img/blog-default.jpg';
                                                  if( $articulo->imagen != '')
                                                  {
                                                      $url_imagen = $articulo->imagen;
                                                  }
                                              ?>

                                          <p style="text-align: center;width: 100%;">
                                                <img src="{{ asset( $url_imagen )}}" style=" max-height: 350px;object-fit: cover;">
                                          </p>

                                          <h2 class="entry-title" style="width: 100%; text-align: center;"><a href="#">{{$articulo->titulo}}</a></h2>
                                    </header>

                                    <div class="entry-content">
                                          <P>{!! $articulo->contenido !!}</P>
                                    </div>

                                    <footer class="entry-meta" style="text-align: right;">
                                          <span class="entry-author"><i class="fa fa-calendar"></i> <a href="#">{{$articulo->updated_at}}</a></span>
                                          <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{{$articulo->articlesetup->titulo}}</a></span>
                                    </footer>
                              </div>
                        </article>
                  </div>
            </div>
      </div>
</div>