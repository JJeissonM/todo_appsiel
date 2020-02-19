<?php			
	//$cant_cols=3; // Cantidad de columnas 
	$i=0;

  $height = '350';
  if ( $altura_imagen != '')
  {
    $height = $altura_imagen;
  }

?>

<div class="ultimos_articulos">
  @foreach($articulos as $fila)
        
      @if($i % $cant_cols == 0)
        <div class="row">
      @endif

      <div class="col-sm-{{12/$cant_cols}}" >

        <a href="{{ url( '/'.$fila->slug ) }}" class="enlace">
          
          @if($mostrar_titulo)
            <div class="titulo">
              {{ $fila->titulo }}
            </div>
          @endif

          @if($mostrar_imagen)
            <div class="imagen">
              
              @php
                // Las imágenes de los artículos siempre están en la misma ubicación
                $url_imagen = asset( config('configuracion.url_instancia_cliente').'web'.$fila->imagen );
              @endphp   
              
              <img class="img-responsive" src="{{ $url_imagen }}" style="height: {{ $height }}px;" />

            </div>
          @endif

          @if($mostrar_resumen)
            <div class="resumen">
              {{ $fila->resumen }}
            </div>
          @endif

        </a>

      </div>
            
      @if($i % $cant_cols == 0)
        </div>
      @endif
      
      <?php $i++; ?>

  @endforeach
</div>