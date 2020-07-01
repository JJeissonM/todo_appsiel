<?php
  
  $clientes = [
      ['Colegio La Paz','escudo_colpaz.jpg', 'http://colegiolapaz.edu.co' ],
      ['IE Oswaldo Quintana Quintana (MEGACOLEGIO)','ESCUDO_MEGACOLEGIO.png', 'http://ieoswaldoquintana.edu.co' ],
      ['SERVIPAN S.A.','logo_servipan_400x400.png', '' ],
      ['Colegio Santa Teresita','santa_teresita.jpg', 'http://col-santateresita.edu.co' ],
      ['Colegio San Juan Bosco','logo_sjb.png', 'http://colegiosanjuanbosco.edu.co' ],
      ['Colegio Nuestra Sra. de Torcoroma','escudo_torcoroma.png', 'http://colegiotorcoroma.edu.co' ],
      ['Óptica UPAR','logo_optica_upar.jpg', 'http://opticaupar.com' ],
      ['AVIPOULET DE LA COSTA','logo_avipoulet.png', 'http://avipoulet.com' ],
      ['Happy Time Pre-School','happy_time_logo.jpg', '' ],
      ['Fundaci&oacute;n Humanizada Conf&iacute;a','logo_fundacion_humanizada_confia.png', 'http://fundacionhumanizadaconfia.com.co' ],
      [ 'TRANSPORCOL', 'transporcol_logo_small.png', 'http://transporcol.com'],
      [ 'Fundaci&oacute;n Somos Un Solo Coraz&oacute;n', 'logo_somo_un_solo_corazon_con_color.jpg', 'http://fundacionsomosunsolocorazon.org'],
      [ 'Cueros Y M&aacute;s','logo_cueros_y_mas_small.jpeg','']
  ];
?>

<!-- Container (Clientes Section) -->
<div id="clientes" class="container-fluid text-center bg-grey">
  <h2> Nuestros Clientes</h2>
  <h4> <i> Empresas e instituciones que han confiado en nosotros para continuar creciendo. </i> </h4>
  <div id="myCarousel_clientes" class="carousel slide text-center" data-ride="carousel">

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
      <div class="item active">
        <br>
        <div class="row text-center">
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[0][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[0][2]; ?>" target="_blank"><?php echo $clientes[0][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[1][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[1][2]; ?>" target="_blank"><?php echo $clientes[1][0]; ?></a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[2][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[2][2]; ?>" target="_blank"><?php echo $clientes[2][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[3][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[3][2]; ?>" target="_blank"><?php echo $clientes[3][0]; ?> </a></strong></p>
          </div>
        </div>
      </div>
      <div class="item">
        <br>
        <div class="row text-center">
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[4][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[4][2]; ?>" target="_blank"><?php echo $clientes[4][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[5][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[5][2]; ?>" target="_blank"><?php echo $clientes[5][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[6][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p> <strong> <a href="{{ url($clientes[6][2]) }}" target="_blank"><?php echo $clientes[6][0]; ?> </a> </strong> </p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[7][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p> <strong> <a href="{{ url($clientes[7][2]) }}" target="_blank"><?php echo $clientes[7][0]; ?> </a> </strong> </p>
          </div>
        </div>
      </div>
      <div class="item">
        <br>
        <div class="row text-center">
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[8][1] }}" style="height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[8][2]; ?>" target="_blank"><?php echo $clientes[8][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[9][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[9][2]; ?>" target="_blank"> <?php echo $clientes[9][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[10][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[10][2]; ?>" target="_blank"><?php echo $clientes[10][0]; ?> </a></strong></p>
          </div>
          <div class="col-sm-3">
              <img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[11][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
              <p><strong><a href="<?php echo $clientes[11][2]; ?>" target="_blank"><?php echo $clientes[11][0]; ?> </a></strong></p>
          </div>
        </div>
      </div>
    
	<div class="item">
            <br>
            <div class="row text-center">
		<div class="col-sm-3">
			<img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/resources/views/pagina_web/front_end/modulos/clientes/img/'.$clientes[12][1] }}" style="max-height: 150px; width: auto; border-radius: 4px;">
	              <p><strong><a href="<?php echo $clientes[12][2]; ?>" target="_blank"><?php echo $clientes[12][0]; ?> </a></strong></p>
		</div>
		<div class="col-sm-3">
			&nbsp;
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
