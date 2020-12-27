<?php
  $imagenes = $datos['imagenes'];
  $cant = count($imagenes);
?>

  <div id="{{ 'myCarousel_'.$datos['id'] }}" class="carousel slide" data-ride="carousel">
      <!-- Indicators -->
      <ol class="carousel-indicators">
        @for($i=0;$i<$cant;$i++)
          <li data-target="{{ '#myCarousel_'.$datos['id'] }}" data-slide-to="{{$i}}" @if($i==0) class="active" @endif></li>
        @endfor
      </ol>

      <!-- Wrapper for slides -->
      <div class="carousel-inner" role="listbox">
        @for($i=0;$i<$cant;$i++)
          <div @if($i==0) class="item active" @else class="item"  @endif>
            <img src="{{ $imagenes[$i]['imagen'] }}" class="img-responsive" style="max-height: {{ $datos['altura_maxima'] }}px;" width="100%">
            @if( $imagenes[$i]['texto'] != '')
              @if( $imagenes[$i]['enlace'] != '')
                <p> <a href="http://{{ $imagenes[$i]['enlace'] }}" target="_blank"> <b> {{ $imagenes[$i]['texto'] }} </b> </a> </p>
              @else
                <p> <b> {{ $imagenes[$i]['texto'] }} </b> </p>
              @endif
            @endif
          </div>
        @endfor
      </div>

      @if( $datos['activar_controles_laterales'] )
        <a class="left carousel-control" href="#{{ 'myCarousel_'.$datos['id'] }}" role="button" data-slide="prev">
          <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
          <span class="sr-only">Anterior</span>
        </a>
        <a class="right carousel-control" href="#{{ 'myCarousel_'.$datos['id'] }}" role="button" data-slide="next">
          <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
          <span class="sr-only">Siguiente</span>
        </a>
      @endif
  </div> 