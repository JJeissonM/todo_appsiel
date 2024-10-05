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
                    <div id="{{str_slug($categoria)}}" class="tab-pane fade in active">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(auto,150px)); gap:10px;">
                            @if(count($productos)>0)
                                @foreach($productos as $item)
                                    <div id="btn_{{ $item->id }}">
                                        @include('ventas_pos.componentes.tactil.dibujar_item')
                                        <br>
                                    </div>
                                @endforeach
                            @else
                                <h5>No hay productos en esta categoría</h5>
                            @endif
                        </div>                        
                    </div>
                    <?php 
                        $es_el_primero = false;
                    ?>	
                @else
                    <div id="{{str_slug($categoria)}}" class="tab-pane fade in">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(auto,150px)); gap:10px;">
                            @if(count($productos)>0)
                                @foreach($productos as $item)
                                    <div id="btn_{{ $item->id }}">
                                        @include('ventas_pos.componentes.tactil.dibujar_item')
                                        <br>
                                    </div>
                                @endforeach
                            @else
                                <h5>No hay productos en esta categoría</h5>
                            @endif
                        </div>                        
                    </div>
                @endif
            @endforeach
        </div>
	</div>
</div>