<?php
  
  function getChildren($data, $line)
  {
      $children = [];
      foreach ($data as $linea) {
          $line1 = (array)$linea;

          if ($line['id'] == $line1['item_padre_id']) {
              $children = array_merge($children, [ array_merge($line1, ['submenu' => getChildren($data, $line1) ]) ]);
          }
      }
      return $children;
  }

  $menu_id = 1; // Debe ser automático

  $data = DB::table('pw_menu_items')->where('menu_id', $menu_id)->orderBy('orden')->get();

  
  $menuAll = [];
  foreach ($data as $linea1) {
    $line = (array)$linea1;
    $item = [ array_merge($line, ['submenu' => getChildren($data, $line) ]) ];
    $menuAll = array_merge($menuAll, $item);
  }

  /*$clase_fixed = '';
  $mostar_logo = false;
  $url_logo = '';
  $slogan = '';
  $alineacion_items = '';*/
?>

<div itemscope itemtype="http://schema.org/SiteNavigationElement">
  <nav class="navbar navbar-default {{$clase_fixed}}">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>                       
        </button>

        @if($mostar_logo)
          <a class="navbar-brand" href="{{url('/')}}">
            
            @if( $url_logo != '')
              <img src="{{ $url_logo }}" alt="logo" width="32" style="display: inline;" class="logo" itemprop="logo">
            @endif

            @if($slogan != '')
              <strong>{{ $slogan }}</strong>
            @endif

          </a>
        @endif
           
      </div>
      <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav {{$alineacion_items}}">
          @foreach ($menuAll as $key => $item)
              @if ($item['item_padre_id'] != 0)
                  @break
              @endif
              @include('web.front_end.modulos.menu.menu-item', ['item' => $item])
          @endforeach
        </ul>
      </div>
    </div>
  </nav>
</div>