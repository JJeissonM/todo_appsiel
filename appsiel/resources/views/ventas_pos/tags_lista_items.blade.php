<div class="row">
	<!-- <h4 style="text-align: center;">Selecciona la categoría luego indica el producto dando clic en COMPRAR</h4> -->
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
                        @if(count($productos)>0)
                            @foreach($productos as $item)
                                <div class="col-md-2 col-xs-6" style="padding: 10px;" id="btn_{{ $item->id }}">
                                    @include('ventas_pos.tags_lista_items_dibujar_item')
                                    <br>
                                </div>
                            @endforeach
						@else
						    <h5>No hay productos en esta categoría</h5>
						@endif
                    </div>
                    <?php 
                        $es_el_primero = false;
                    ?>	
                @else
                    <div id="{{str_slug($categoria)}}" class="tab-pane fade in">
                        @if(count($productos)>0)
                            @foreach($productos as $item)
                                <div class="col-md-2 col-xs-6" style="padding: 10px;" id="btn_{{ $item->id }}">
                                    @include('ventas_pos.tags_lista_items_dibujar_item')
                                    <br>
                                </div>
                            @endforeach
						@else
						    <h5>No hay productos en esta categoría</h5>
						@endif
                    </div>
                @endif
            @endforeach
        </div>
	</div>
</div>