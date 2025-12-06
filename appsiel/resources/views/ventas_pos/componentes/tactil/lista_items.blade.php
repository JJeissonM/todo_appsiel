@if($productosTemp == null)
    No hay items para mostrar.
@else
    
    <div class="row">
        <div class="col-md-12">

            <ul class="nav nav-tabs">
                <?php 
                    $es_el_primero = true;
                ?>	
                @foreach($productosTemp as $categoria => $productos)
                    @if($es_el_primero)
                        <li class="active">
                            <a data-toggle="tab" href="#{{str_slug($categoria)}}">{{$categoria}}</a>
                        </li>
                        <?php 
                            $es_el_primero = false;
                        ?>	
                    @else
                        <li>
                            <a data-toggle="tab" href="#{{str_slug($categoria)}}">{{$categoria}}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
            
            <div class="tab-content">
                <?php 
                    $es_el_primero = true;
                ?>	
                @foreach($productosTemp as $categoria => $productos)
                    @if($es_el_primero)
                        <?php 
                            $es_el_primero = false;
                            $active = 'active';
                        ?>	
                        @include('ventas_pos.componentes.tactil.lista_items_content_tab', compact('active') )
                    @else
                        <?php 
                            $active = '';
                        ?>	
                        @include('ventas_pos.componentes.tactil.lista_items_content_tab', compact('active') )
                    @endif
                @endforeach
            </div>
        </div>
    </div>

@endif