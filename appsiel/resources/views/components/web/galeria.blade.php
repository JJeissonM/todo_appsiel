<section id="portfolio">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated"
                style="visibility: visible; animation-name: fadeInDown;">Galeria de Imagenes</h2>

        </div>

        <div class="text-center">
            <ul class="portfolio-filter">
                @if(count($galeria->albums)>0)
                    <li><a href="#" data-filter="*">TODOS</a></li>
                    @foreach($galeria->albums as $album)
                        <li><a href="#" data-filter=".{{str_slug($album->titulo)}}">{{$album->titulo}}</a></li>
                    @endforeach
                @endif
            </ul><!--/#portfolio-filter-->
        </div>

        <div class="portfolio-items isotope" style="position: relative; overflow: hidden; height: 260px;">
            @foreach($galeria->albums as $album)
                @foreach($album->fotos as $foto)
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
                @endforeach
            @endforeach
        </div>
    </div><!--/.container-->
</section>