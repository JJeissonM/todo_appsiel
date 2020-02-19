<?php			
	$cant_cols=3; // Cantidad de columnas 
	$i=$cant_cols;

  //$url_imagen = asset( config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/'.$el_articulo->imagen );
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-sm-12 noticias">
      @foreach($articulos as $fila)
            
            @if($i % $cant_cols == 0)
              <div class="row" style="margin: 0px;">
            @endif
            
            <?php

            		$tam_icono = '100px';

            		$url_imagen = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/pagina_web/'.$fila->imagen;

            		$enlace_web = $fila->enlace_web;
            ?>
              <div class="col-sm-{{12/$cant_cols}}" >

                <div style="padding: 10px;">
                  <div class="panel panel-default">
                    
                    <div class="panel-heading">
                      <a href="{{ url('blog/'.$fila->alias_sef) }}" style="color: white;">{{ $fila->titulo }}</a>
                    </div>
                    
                    <div class="panel-body">
                        <img class="img-responsive" src="{{ $url_imagen }}" style="height: 200px; width: 100%;" />
                    </div>
                    
                    <div class="panel-footer">
                      {{ $fila->descripcion_corta }}
                      <span style="float: right;">
                        <a href="{{ url('blog/'.$fila->alias_sef) }}">Leer más...</a>
                      </span>
                    </div>
                  
                  </div>
              	</div>
              </div>
      	<?php
            $i++;
        	?>
            @if($i % $cant_cols == 0)
            	<!-- Por cada cantidad de columnas definidas ($cant_cols ) se cierra la fila -->
              </div>

              <?php			
      			$i=$cant_cols;
      	      ?>

            @endif
      @endforeach

    </div>
  </div>
</div> 