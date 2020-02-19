<?php 
  $elementos = (object)[
                  (object)['enlace' => '#about', 'descripcion' => 'Nosotros'],
                  (object)['enlace' => '#services', 'descripcion' => 'Servicios'],
                  (object)['enlace' => '#portfolio', 'descripcion' => 'DEMO'],
                  (object)['enlace' => '#pricing', 'descripcion' => 'Precios'],
                  (object)['enlace' => '#clientes', 'descripcion' => 'Clientes'],
                  (object)['enlace' => '#contact', 'descripcion' => 'Contactenos']
              ];
?>
<div class="container" itemscope itemtype="http://schema.org/SiteNavigationElement">
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
          <span class="sr-only">Toggle Navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#myPage">
          <img src="{{ asset('assets/img/pagina_web/logo.png') }}" alt="logo_appsiel" width="32" style="display: inline;" class="logo" itemprop="logo">
          <strong>APPSIEL</strong>
        </a>
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav navbar-right">
          @foreach($elementos as $linea)
            <li>
              <a href="{{$linea->enlace}}">
                {{ $linea->descripcion }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
  </nav>
</div>