<?php     
  $url_imagen = asset( config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/'.$el_articulo->imagen );

  $deshabilitado = '';
?>
<div class="row">
  <div class="col-sm-8">
    <div class="post" itemprop="blogPost" itemscope="itemscope" itemtype="http://schema.org/BlogPosting">

      @if($el_articulo->mostrar_titulo)
        <h3 itemprop="name">
          {{ $el_articulo->titulo }}
        </h3>
      @endif

      @if( $el_articulo->imagen != '')
        <p class="imagen_articulo">
          <img class="img-responsive" src="{{ $url_imagen }}"/>
        </p>
      @endif

      <div class="post-body" itemprop="description articleBody">
        <div style="width: 100%; overflow: auto;"> 
          {!! $el_articulo->contenido_articulo !!}
        </div>
        <hr>
        <div style="clear: both;"></div>
      </div>

      <div class="post-footer">
        <div class="post-footer-line post-footer-line-1">
          <span class="post-author vcard">
            Creado por
            <span class="fn" itemprop="author" itemscope="itemscope" itemtype="http://schema.org/Person">
              <meta content="https://www.blogger.com/profile/14949597074204285306" itemprop="url">
              <a class="g-profile" href="https://www.blogger.com/profile/14949597074204285306" rel="author" title="author profile" data-gapiscan="true" data-onload="true" data-gapiattached="true">
                <span itemprop="name">
                  <?php 
                    $creado_por = explode("@", $el_articulo->creado_por);
                  ?>
                  {{ $creado_por[0] }}
                </span>
              </a>
            </span>
          </span>
          <span class="post-timestamp">
            el {{ $el_articulo->created_at }}
          </span>                
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-4">

    <div class="list-group">
        <a href="#" class="list-group-item list-group-item-warning"> <h4 style="width: 100%;text-align: center;">{{ $categoria->descripcion }}</h4> </a>
      @foreach($articulos as $fila)
        <a href="{{ url('blog/'.$fila->alias_sef) }}" class="list-group-item"> {{ $fila->titulo }} </a>
      @endforeach
    </div>
  </div>
</div>