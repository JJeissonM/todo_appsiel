<?php

?>

<div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
  <!-- Indicators 
  <ol class="carousel-indicators">
    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
    <li data-target="#myCarousel" data-slide-to="1"></li>
    <li data-target="#myCarousel" data-slide-to="2"></li>
  </ol>-->

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
      ?>

      <div class="carousel-item {{$activo}}">
        <img src="{{ asset($foto->nombre) }}" alt="foto" class="img-fluid">
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