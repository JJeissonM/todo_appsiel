<?php

?>

<div class="container-fluid">
  <h4>{{ $album->titulo }}</h4>
  <p style="width: 100%; text-align: justify;">
    {{ $album->descripcion }}
  </p>
</div>

<!-- <div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="3000"> -->
<div id="myCarousel" class="carousel slide" data-interval="false">

  <!-- Wrapper for slides -->
  <div class="carousel-inner">
    <?php 
      $es_el_primero = true;
    ?>
    @foreach( $fotos as $foto )

      <?php 
        if( $es_el_primero )
        {
          $activo = 'active';
          $es_el_primero = false;
        }else{
          $activo = '';
        }

        $array_nombre = explode( ".", $foto->nombre );
        $extension_archivo = end( $array_nombre );

      ?>

      <div class="carousel-item {{$activo}}">
        @if( $extension_archivo == 'mp4' )
          <div style="text-align: center;">
            <h5>Video</h5>
            <video width="320" height="340" controls>
              <source src="{{ asset($foto->nombre) }}" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          </div>
            
        @else
          <img src="{{ asset($foto->nombre) }}" alt="foto" class="img-fluid">
        @endif
      </div>

    @endforeach

  </div>

  <!-- Left and right controls -->
  <a class="carousel-control-prev" href="#myCarousel" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Anterior</span>
  </a>
  <a class="carousel-control-next" href="#myCarousel" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Siguiente</span>
  </a>
</div>