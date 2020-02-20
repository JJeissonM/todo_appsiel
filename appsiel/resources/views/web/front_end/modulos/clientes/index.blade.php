<?php
  
  $clientes = [
      ['Colegio La Paz','escudo_colpaz.jpg', '//www.colegiolapaz.edu.co' ],
      ['Jardín Infantil Mi Casita','escudo_mi_casita.jpg', '' ],
      ['Administradora de Propiedad Horizontal','logo_asiph_letras.jpg', '//www.asiph675.com.co' ],
      ['Colegio Santa Teresita','santa_teresita.jpg', '//www.col-santateresita.edu.co' ],
      ['Colegio San Juan Bosco','logo_sjb.png', '' ],
      ['Colegio Nuestra Sra. de Torcoroma','escudo_torcoroma.png', '//www.colegiotorcoroma.edu.co' ],
      ['Óptica UPAR','logo_optica_upar.jpg', '//www.opticaupar.com' ],
      ['AVIPOULET','logo_avipoulet_cuadrado.jpg', '//www.avipoulet.com' ],
      ['Happy Time Pre-School','happy_time_logo.jpg', '' ],
      ['Gedeones Kids','logo_liceo_galois.png', '' ]
  ];
?>

<!-- Container (Clientes Section) -->
<div id="clientes" class="container-fluid text-center bg-grey">
  <h2> Nuestros Clientes</h2><br>
  <div id="myCarousel_clientes" class="carousel slide text-center" data-ride="carousel">

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
      <div class="item active">
        <br>
        <div class="row text-center">
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[0][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[0][2]; ?>" target="_blank"><?php echo $clientes[0][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[1][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[1][2]; ?>" target="_blank"><?php echo $clientes[1][0]; ?></a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[2][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[2][2]; ?>" target="_blank"><?php echo $clientes[2][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[3][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[3][2]; ?>" target="_blank"><?php echo $clientes[3][0]; ?> </a></strong></p>
          </div>
        </div>
      </div>
      <div class="item">
        <br>
        <div class="row text-center">
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[4][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[4][2]; ?>" target="_blank"><?php echo $clientes[4][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'web'.$clientes[5][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[5][2]; ?>" target="_blank"><?php echo $clientes[5][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[6][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p> <strong> <a href="{{ url($clientes[6][2]) }}" target="_blank"><?php echo $clientes[6][0]; ?> </a> </strong> </p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[7][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p> <strong> <a href="{{ url($clientes[7][2]) }}" target="_blank"><?php echo $clientes[7][0]; ?> </a> </strong> </p>
          </div>
        </div>
      </div>
      <div class="item">
        <br>
        <div class="row text-center">
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[8][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[8][2]; ?>" target="_blank"><?php echo $clientes[8][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[9][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[9][2]; ?>" target="_blank"><?php echo $clientes[9][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              &nbsp;
          </div>
          <div class="col-sm-3">
              &nbsp;
          </div>
        </div>
      </div>
    </div>

    <!-- Left and right controls -->
    <a class="left carousel-control" href="#myCarousel_clientes" role="button" data-slide="prev" style="
    width: 15px;">
      <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#myCarousel_clientes" role="button" data-slide="next" style="
    width: 15px;">
      <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</div>