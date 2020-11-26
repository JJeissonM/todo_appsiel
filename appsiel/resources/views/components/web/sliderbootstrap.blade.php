<style>

</style>
<section id="main-slider">
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
        <div class="carousel-inner">
            <?php $i = 0; ?>
            @foreach($slider->items as $item)
            @if($i==0)
            <div class="carousel-item active">
                @else
                <div class="carousel-item">
                    @endif
                    <img src="{{asset($item->imagen)}}" alt="{{$item->titulo}}" class="d-block w-100">
                    <div class="carousel-caption d-none d-md-block">
                        <h5 style="color: {{$item->colorTitle}} !important;">{{$item->titulo}}</h5>
                        <p style="color: {{$item->colorText}} !important;">{{$item->descripcion}}<br><br><br><a class="btn btn-primary btn-lg" href="{{$item->enlace}}">{{$item->button}}</a></p>
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