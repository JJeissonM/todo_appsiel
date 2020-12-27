<div id="myCarousel" class="carousel slide" data-ride="carousel">
    <!-- Indicators -->
    <ol class="carousel-indicators">
      <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#myCarousel" data-slide-to="1"></li>
      <li data-target="#myCarousel" data-slide-to="2"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
      <div class="item active">
        <img src="{{ asset('assets/img/pagina_web/banner1.png') }}" alt="appsiel" width="1200" height="700">      
      </div>

      <div class="item">
        <img src="{{ asset('assets/img/pagina_web/banner2.png') }}" alt="plataforma_educativa" width="1200" height="700">
        <div class="carousel-caption">
          <h3>&nbsp;</h3>
          <p>¡¡¡Quedaran fascinados!!!</p>
        </div>      
      </div>
    
      <div class="item">
        <img src="{{ asset('assets/img/pagina_web/banner3.png') }}" alt="descripcion_software" width="1200" height="700">   
      </div>
    </div>

    <!-- Left and right controls -->
    <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
      <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      <span class="sr-only">Anterior</span>
    </a>
    <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
      <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      <span class="sr-only">Siguiente</span>
    </a>
</div>