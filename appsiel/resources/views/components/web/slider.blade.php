<style>
    #main-slider {
        /*margin-top: 40px;*/
    }

    #main-slider .item {
        /*height: 550px;*/
    }

    .slider-font {
        @if( !is_null($slider) )
            @if( !is_null($slider->configuracionfuente ) )
                font-family: <?php echo $slider->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }
    /*
    .owl-carousel img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
    }

    .owl-item{
        height: 20rem !important;
    }
    */
</style>
<section id="main-slider" class="slider-font">
    @if($slider != null && $slider->items->count() > 0)
    <div class="owl-carousel slider-font">
        @foreach($slider->items as $item)
        <div class="item" style="width: 100%;">
            <div class="slider-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="carousel-content slider-font">

                                @if( $item->titulo != '' )
                                <h2 class="slider-font" style="text-shadow: 1px 1px 2px black; color: {{$item->colorTitle}} !important;">{{$item->titulo}}</h2>
                                @endif

                                @if( $item->descripcion != '' )
                                <p class="slider-font" style="text-shadow: 1px 1px 2px black; color: {{$item->colorText}} !important;">{{$item->descripcion}}</p>
                                @endif

                                @if( $item->button != '')
                                <a class="slider-font btn btn-primary btn-lg" href="{{$item->enlace}}">{{$item->button}} <i class="fa fa-plus"></i></a>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <img class="image" src="{{asset($item->imagen)}}" alt="{{$item->titulo}}">
        </div>
        @endforeach
    </div>
    <!--/.item-->
    @else
    <div class="owl-carousel">
        <div class="item" style="background-image: url('{{asset('images/slider/bg1.jpg')}}');">
            <div class="slider-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="carousel-content">
                                <h2><span>Bienvenido</span> este es nuestro sitio web.</h2>
                                <p>Siempre buscamos ofrecer una experiencia extraordinaira con nuestros productos y servicios.</p>
                                <a class="btn btn-primary btn-lg" href="#">Leer más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/.item-->
        <div class="item" style="background-image: url({{asset('images/slider/bg2.jpg')}});">
            <div class="slider-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="carousel-content">
                                <h2>La belleza es una <span>virtud</span> del ser humano.</h2>
                                <p>Queremos aporar valor y belleza a nuestro mundo. </p>
                                <a class="btn btn-primary btn-lg" href="#">Leer más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/.item-->
    </div>
    <!--/.owl-carousel-->
    @endif
</section>

<script type="text/javascript">
    
    $(document).ready(function() {
        console.log('ingresa');
        $('.owl-carousel').owlCarousel({
            autoHeight:true
        });
    });

</script>
<!--/#main-slider-->