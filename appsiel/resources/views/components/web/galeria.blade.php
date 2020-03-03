<section id="portfolio">
    <div class="container">
        @if($galeria!=null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown;">Galería de Imagenes</h2>
                @if(count($galeria->albums)>0)
                    <center><a class="btn btn-primary btn-md text-center"
                               href="{{route('galeria.albums',$galeria->id)}}">Todas
                            los Albunes...</a></center>
                @endif
            </div>

            <div class="text-center">
                <ul class="portfolio-filter">
                    <?php $cont = 1; ?>
                    @if(count($galeria->albums)>0)
                        <li><a href="#" data-filter="*" class="active">TODOS</a></li>
                        @foreach($galeria->albums as $album)
                            @if($cont <= 4)
                                <li><a href="#" data-filter=".{{str_slug($album->titulo)}}">{{$album->titulo}}</a></li>
                            @else
                                @break
                            @endif
                            <?php $cont = $cont + 1; ?>
                        @endforeach
                    @endif
                </ul>
                <!--/#portfolio-filter-->
            </div>

            <div class="portfolio-items isotope" style="position: relative; overflow: hidden; height: 260px;">
                @foreach($galeria->albums as $album)
                    @if(count($album->fotos) > 0)
                        <?php $aux = 1; ?>
                        @foreach($album->fotos as $foto)
                            @if($aux <= 2)
                                <div class="portfolio-item {{str_slug($album->titulo)}} isotope-item"
                                     style="position: absolute; left: 0px; top: 0px; transform: translate3d(0px, 0px, 0px);">
                                    <div class="portfolio-item-inner">
                                        <img class="img-responsive" src="{{url($foto->nombre)}}" alt="">
                                        <div class="portfolio-info">
                                            <h3>{{$album->titulo}}</h3>
                                            {{$foto->nombre}}
                                            <a class="preview" href="{{url($foto->nombre)}}" rel="prettyPhoto"><i
                                                        class="fa fa-eye"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @break
                            @endif
                            <?php $aux = $aux + 1; ?>
                        @endforeach
                    @endif
                @endforeach
            </div>
        @else
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown">Sección</h2>
                <p class="text-center wow fadeInDown">Sin configuración</p>
            </div>
        @endif
    </div>
    <!--/.container-->
</section>