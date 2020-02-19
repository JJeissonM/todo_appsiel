<div itemscope itemtype="http://schema.org/SiteNavigationElement">
    <div class="container-fluid">

        <?php
          $estilo = '';
          if( $datos->modo_horizontal )
          {
            $estilo = ' style="float: left;"';
          }
        ?>

        <ul class="menu_enlaces">
          @foreach($datos->items as $item)
            <li>
              <a href="{{ $item->enlace }}" target="{{ $item->target }}" {{ $estilo }}> {{ $item->descripcion }} </a>
            </li>
          @endforeach
        </ul>

    </div>
</div>