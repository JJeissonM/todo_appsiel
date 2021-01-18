<style>
    .slider-font {
        @if( !is_null($slider) )
            @if( !is_null($slider->configuracionfuente ) )
                font-family: <?php echo $slider->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }

    .carousel-item{
        height: 554px;        
    }

    .image{
        top: 66px;
        width: 100%;
        height: 554px;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

</style>
<section id="main-slider" class="slider-font">
    @if($slider != null && $slider->items->count() > 0)
    <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php $i = 0; ?>
            @foreach($slider->items as $item)
            @if($i==0)
            <li data-target="#carouselExampleCaptions" data-slide-to="{{$i}}" class="active"></li>
            @else
            <li data-target="#carouselExampleCaptions" data-slide-to="{{$i}}"></li>
            @endif
            <?php $i = $i + 1; ?>
            @endforeach
        </ol>
        <div class="carousel-inner slider-font">
            <?php $i = 0; ?>
            @foreach($slider->items as $item)
            @if($i==0)
            <div class="carousel-item active">
                @else
                <div class="carousel-item">
                    @endif
                    <div style="background-image: url('{{asset($item->imagen)}}')" alt="{{$item->titulo}}" class="image d-block w-100">
                    <div class="carousel-caption d-none d-md-block">
                        <h5 class="slider-font" style="color: {{$item->colorTitle}} !important;">{{$item->titulo}}</h5>
                        <p class="slider-font" style="color: {{$item->colorText}} !important;">{{$item->descripcion}}<br><br><br><a class="slider-font btn btn-primary btn-lg" href="{{$item->enlace}}">{{$item->button}} <i class="fa fa-plus"></i></a></p>
                    </div>
                    <?php $i = $i + 1; ?>
                </div>
                @endforeach
                <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Anterior</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Siguiente</span>
                </a>
            </div>
        </div>
        @endif
</section>
<!--/#main-slider-->