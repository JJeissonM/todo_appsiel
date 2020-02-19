<div class="container" itemscope itemtype="http://schema.org/SiteNavigationElement">
  
  <nav class="navbar {{$estilo}} {{$clase_fixed}}">

      <div class="navbar-header">
        
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar_{{$menu_id}}">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>                       
        </button>

        @if($mostrar_logo)
            <a class="navbar-brand" href="{{url('/')}}" style="height: 60px; padding-top: 0px;">
            
              @if( $url_logo != '')
                <img src="{{ $url_logo }}" alt="logo" height="55px" style="display: inline;" itemprop="logo">
              @endif

              @if($slogan != '')
                <strong>{{ $slogan }}</strong>
              @endif

            </a>          
        @endif

           
      </div>

      <div class="collapse navbar-collapse" id="myNavbar_{{$menu_id}}">
        <ul class="nav navbar-nav {{$alineacion_items}}">
          {!! $lista_items !!}
        </ul>
      </div>

  </nav>

</div>