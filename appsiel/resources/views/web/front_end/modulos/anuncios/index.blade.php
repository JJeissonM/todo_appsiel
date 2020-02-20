<?php
  $cant = count( $anuncios );
?>
<div style="background-color: white; border-radius: 10px;">
  <h4 style="width: 100%; text-align: center; color: gray; vertical-align: middle;">Anuncios</h4>
  <hr>
  <div id="myCarousel_anuncios" class="carousel slide" data-ride="carousel">
      <!-- Indicators -->
      <ol class="carousel-indicators">
        @for($i=0;$i<$cant;$i++)
          <li data-target="#myCarousel_anuncios" data-slide-to="{{$i}}" @if($i==0) class="active" @endif></li>
        @endfor
      </ol>

      <!-- Wrapper for slides -->
      <div class="carousel-inner" role="listbox">
        <?php $i=0; ?>
        @foreach($anuncios as $anuncio)
          <div @if($i==0) class="item active" @else class="item" @endif>
            
            @if( $anuncio->imagen != '')
            <img src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/anuncios/'.$anuncio->imagen) }}" class="img-responsive" style="max-height: 230px; display: block; margin: auto; padding: 10px;">
            @endif

            @if( $anuncio->descripcion != '')
              <p> <b> {{ $anuncio->descripcion }} </b> </p>
            @endif
            
            @if( $anuncio->detalle != '')
              <p style="width: 100%; background-color: #c7cecd; overflow: hidden;"> <b> {{ $anuncio->detalle }} </b> </p>
            @endif

          </div>
          <?php $i++;  ?>
        @endforeach
      </div>
  </div>
</div>