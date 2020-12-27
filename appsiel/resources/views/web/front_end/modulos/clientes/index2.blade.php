<?php
  
  $clientes = (object)[
      (object)['descripcion' => 'Berenjenas rellenas','imagen' => 'berenjenas-rellenas-e.jpg', 'enlace' => '' ],
      (object)['descripcion' => 'Bizcocho de naranja y canela','imagen' => 'bizocho-naranja-canela-e.jpg', 'enlace' => '' ],
      (object)['descripcion' => 'Croquetas de pollo, curry y nueces','imagen' => 'croquetas-pollo-e.jpg', 'enlace' => '' ],
      (object)['descripcion' => 'flores-coliflor-gambas-e','imagen' => 'flores-coliflor-gambas-e.jpg', 'enlace' => '' ],
      (object)['descripcion' => 'Lubina en papillote con patatas','imagen' => 'lubina-horno-papillote-patatas-e.jpg', 'enlace' => '' ],
      (object)['descripcion' => 'Mejillones a la sidra con mostaza a la antigua','imagen' => 'mejillones-sidra-mostaza-e.jpg', 'enlace' => '' ]
  ];
?>

<!-- Container (Clientes Section) -->
<div id="clientes" class="container-fluid text-center" style="background-color: #f7f7f9;">
  <h2>{{ $titulo }}</h2><br>
  <div id="myCarousel_clientes" class="carousel slide text-center" data-ride="carousel">

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
      
      <?php       
        $cant_cols=3;
        $i=0;
        $es_el_primero = true;
      ?>
      @foreach ($clientes as $fila)

        @if($i % $cant_cols == 0)
           <!-- se ABRE una linea -->
           @if($es_el_primero)
            <div class="item active">
            @php $es_el_primero = false; @endphp
          @else
            <div class="item">
          @endif
              <div class="row text-center">
        @endif

        <div class="col-sm-{{(12/$cant_cols)}}">
          <img src="{{ asset( "/assets/pagina_web/".$pagina->plantilla."/recetas/".$fila->imagen ) }}" class="img-responsive">
          <p><strong><a href="{{ $fila->enlace }}" target="_blank">{{ $fila->descripcion }} </a></strong></p>
        </div>

        <?php $i++; ?>

        @if($i % $cant_cols == 0)
          <!-- se CIERRA una linea -->
            </div>
          </div>
        @endif        

      @endforeach

      @for($j=0; $j < ( $i % $cant_cols );$j++)
        <div class="col-sm-{{(12/$cant_cols)}}">
          &nbsp;  
        </div>
      @endfor

    </div>

    <!-- Left and right controls -->
    <a class="left carousel-control" href="#myCarousel_clientes" role="button" data-slide="prev">
      <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#myCarousel_clientes" role="button" data-slide="next">
      <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>

  </div>
</div>