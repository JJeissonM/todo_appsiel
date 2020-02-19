<?php 
  $url_logo = config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/'.$pagina->logo.'?'.rand(1,1000); 
  $numero_whatsapp = '3184021442';
?>


<div class="banner-top">
  <div class="col-sm-6">
    <div class="escudo">
      <a class="hidden-xs hidden-sm" href="{{url('/')}}">
        <img src="{{ asset( $url_logo ) }}" width="140" itemprop="logo">
      </a>
      <p>
        <span class="titulo">Colegio Nuestra Señora de Torcoroma</span>
        <span class="sub-titulo">Luz, Ciencia y Amor</span>
      </p>
    </div>       
  </div>

  <div class="col-sm-6">
    <div class="escudo">
      &nbsp;
      <p>
        <!-- <span class="titulo"><a href="#" class="btn btn-lg btn-danger">Admisiones</a></span> -->
      </p>
    </div>    
  </div>

</div>