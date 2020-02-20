<?php 
  
  $colegio = App\Core\Colegio::where('empresa_id', 1)->get()[0];

  $url_logo = config('configuracion.url_instancia_cliente').'/storage/app/web/'.$pagina->logo.'?'.rand(1,1000);
  $numero_whatsapp = '3016369529';

  $url_facebook = "#";
  $url_youtube = "#";
  $url_instagram = "https://www.instagram.com/colpaz2007/";
?>

<div class="row"> 
  <div class="col-sm-6">

    <!-- Elemento: div con objetos alineados verticalmente -->
    <div class="escudo">

      <!-- Elemento: Enlace con imagen -->
      <a class="hidden-xs hidden-sm" href="{{url('/')}}">
        <img src="{{ asset( $url_logo ) }}" width="140" itemprop="logo">
      </a>

      <!-- Elemento: parrafo -->
      <p>
        <span class="titulo">{{ $pagina->descripcion }}</span>
        <span class="sub-titulo">{{ $colegio->slogan }}</span>
      </p>

    </div>

  </div>

  <div class="col-sm-6">
    <div class="iconos_redes_sociales">
      <a href="https://api.whatsapp.com/send?phone=57{{$numero_whatsapp}}" target="_blank" class="fa fa-whatsapp" title="+57 {{$numero_whatsapp}}"></a>
      <a href="{{ $url_facebook }}" class="fa fa-facebook" target="_blank"></a>
      <a href="{{ $url_youtube }}" class="fa fa-youtube" target="_blank"></a>
      <a href="{{ $url_instagram }}" class="fa fa-instagram" target="_blank"></a>
    </div>
  </div>
</div>